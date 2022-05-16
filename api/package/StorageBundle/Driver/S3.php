<?php

namespace Package\StorageBundle\Driver;

/**
 * @see https://github.com/utopia-php/storage
 */
class S3 implements DriverInterface
{
    /**
     * AWS Method Flags.
     */
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_CONNECT = 'CONNECT';
    public const METHOD_TRACE = 'TRACE';

    /**
     * AWS Regions constants.
     */
    public const US_EAST_1 = 'us-east-1';
    public const US_EAST_2 = 'us-east-2';
    public const US_WEST_1 = 'us-west-1';
    public const US_WEST_2 = 'us-west-2';
    public const AF_SOUTH_1 = 'af-south-1';
    public const AP_EAST_1 = 'ap-east-1';
    public const AP_SOUTH_1 = 'ap-south-1';
    public const AP_NORTHEAST_3 = 'ap-northeast-3';
    public const AP_NORTHEAST_2 = 'ap-northeast-2';
    public const AP_NORTHEAST_1 = 'ap-northeast-1';
    public const AP_SOUTHEAST_1 = 'ap-southeast-1';
    public const AP_SOUTHEAST_2 = 'ap-southeast-2';
    public const CA_CENTRAL_1 = 'ca-central-1';
    public const EU_CENTRAL_1 = 'eu-central-1';
    public const EU_WEST_1 = 'eu-west-1';
    public const EU_SOUTH_1 = 'eu-south-1';
    public const EU_WEST_2 = 'eu-west-2';
    public const EU_WEST_3 = 'eu-west-3';
    public const EU_NORTH_1 = 'eu-north-1';
    public const SA_EAST_1 = 'eu-north-1';
    public const CN_NORTH_1 = 'cn-north-1';
    public const ME_SOUTH_1 = 'me-south-1';
    public const CN_NORTHWEST_1 = 'cn-northwest-1';
    public const US_GOV_EAST_1 = 'us-gov-east-1';
    public const US_GOV_WEST_1 = 'us-gov-west-1';

    /**
     * AWS ACL Flag constants.
     */
    public const ACL_PRIVATE = 'private';
    public const ACL_PUBLIC_READ = 'public-read';
    public const ACL_PUBLIC_READ_WRITE = 'public-read-write';
    public const ACL_AUTHENTICATED_READ = 'authenticated-read';

    protected array $headers = [
        'host' => '', 'date' => '', 'content-md5' => '', 'content-type' => '',
    ];

    protected array $amzHeaders;

    public function __construct(private string $root, private string $accessKey, private string $secretKey, private string $bucket, private string $region = self::US_EAST_1, private string $acl = self::ACL_PRIVATE)
    {
        $this->root = rtrim($root, '\\/');
        $this->headers['host'] = $this->bucket.'.s3.'.$this->region.'.amazonaws.com';
        $this->amzHeaders = [];
    }

    public function getName(): string
    {
        return 'S3 Storage';
    }

    public function getDescription(): string
    {
        return 'S3 Bucket Storage drive for AWS or on premise solution';
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getPath(string $filename): string
    {
        return ltrim($this->getRoot().DIRECTORY_SEPARATOR.$filename, '\\/');
    }

    /**
     * Upload.
     *
     * Upload a file to desired destination in the selected disk.
     * return number of chunks uploaded or 0 if it fails.
     *
     * @throws \Exception
     */
    public function upload(string $source, string $path, int $chunk = 1, int $chunks = 1, array &$metadata = []): int
    {
        $path = $this->getPath($path);

        if (1 === $chunk && 1 === $chunks) {
            return (int) $this->writeData($path, file_get_contents($source), mime_content_type($source));
        }
        $uploadId = $metadata['uploadId'] ?? null;
        if (empty($uploadId)) {
            $uploadId = $this->createMultipartUpload($path, $metadata['content_type']);
            $metadata['uploadId'] = $uploadId;
        }

        $etag = $this->uploadPart($source, $path, $chunk, $uploadId);
        $metadata['parts'] ??= [];
        $metadata['parts'][] = ['partNumber' => $chunk, 'etag' => $etag];
        $metadata['chunks'] ??= 0;
        ++$metadata['chunks'];
        if ($metadata['chunks'] === $chunks) {
            $this->completeMultipartUpload($path, $uploadId, $metadata['parts']);
        }

        return $metadata['chunks'];
    }

    /**
     * Start Multipart Upload.
     *
     * Initiate a multipart upload and return an upload ID.
     *
     * @throws \Exception
     */
    protected function createMultipartUpload(string $path, string $contentType): string
    {
        $uri = '' !== $path ? '/'.\str_replace(['%2F', '%3F'], ['/', '?'], \rawurlencode($path)) : '/';

        $this->headers['content-md5'] = \base64_encode(md5('', true));
        unset($this->amzHeaders['x-amz-content-sha256']);
        $this->headers['content-type'] = $contentType;
        $this->amzHeaders['x-amz-acl'] = $this->acl;
        $response = $this->call(self::METHOD_POST, $uri, '', ['uploads' => '']);

        return $response->body['UploadId'];
    }

    /**
     * Upload Part.
     *
     * @throws \Exception
     */
    protected function uploadPart(string $source, string $path, int $chunk, string $uploadId): string
    {
        $uri = '' !== $path ? '/'.\str_replace(['%2F', '%3F'], ['/', '?'], \rawurlencode($path)) : '/';

        $data = \file_get_contents($source);
        $this->headers['content-type'] = \mime_content_type($source);
        $this->headers['content-md5'] = \base64_encode(md5($data, true));
        $this->amzHeaders['x-amz-content-sha256'] = \hash('sha256', $data);
        unset($this->amzHeaders['x-amz-acl']); // ACL header is not allowed in parts, only createMultipartUpload accepts this header.

        $response = $this->call(self::METHOD_PUT, $uri, $data, [
            'partNumber' => $chunk,
            'uploadId' => $uploadId,
        ]);

        return $response->headers['etag'];
    }

    /**
     * Complete Multipart Upload.
     *
     * @throws \Exception
     */
    protected function completeMultipartUpload(string $path, string $uploadId, array $parts): bool
    {
        $uri = '' !== $path ? '/'.\str_replace(['%2F', '%3F'], ['/', '?'], \rawurlencode($path)) : '/';

        $body = '<CompleteMultipartUpload>';
        foreach ($parts as $part) {
            $body .= "<Part><ETag>{$part['etag']}</ETag><PartNumber>{$part['partNumber']}</PartNumber></Part>";
        }
        $body .= '</CompleteMultipartUpload>';

        $this->amzHeaders['x-amz-content-sha256'] = \hash('sha256', $body);
        $this->headers['content-md5'] = \base64_encode(md5($body, true));
        $this->call(self::METHOD_POST, $uri, $body, ['uploadId' => $uploadId]);

        return true;
    }

    /**
     * Abort Chunked Upload.
     *
     * @throws \Exception
     */
    public function abort(string $path, string $extra = ''): bool
    {
        $path = $this->getPath($path);

        $uri = '' !== $path ? '/'.\str_replace(['%2F', '%3F'], ['/', '?'], \rawurlencode($path)) : '/';
        unset($this->headers['content-type']);
        $this->headers['content-md5'] = \base64_encode(md5('', true));
        $this->call(self::METHOD_DELETE, $uri, '', ['uploadId' => $extra]);

        return true;
    }

    /**
     * Read file or part of file by given path, offset and length.
     *
     * @throws \Exception
     */
    public function read(string $path, int $offset = 0, int $length = null): string
    {
        $path = $this->getPath($path);

        unset($this->amzHeaders['x-amz-acl'], $this->amzHeaders['x-amz-content-sha256'], $this->headers['content-type']);
        $this->headers['content-md5'] = base64_encode(md5('', true));
        $uri = ('' !== $path) ? '/'.str_replace('%2F', '/', \rawurlencode($path)) : '/';
        if (null !== $length) {
            $end = $offset + $length - 1;
            $this->headers['range'] = "bytes=$offset-$end";
        }

        return $this->call(self::METHOD_GET, $uri)->body;
    }

    /**
     * Write file by given path.
     *
     * @throws \Exception
     */
    public function write(string $path, string $data, string $contentType = ''): bool
    {
        return $this->writeData($this->getPath($path), $data, $contentType);
    }

    /**
     * Write file by given path.
     *
     * @throws \Exception
     */
    protected function writeData(string $path, string $data, string $contentType = ''): bool
    {
        $uri = '' !== $path ? '/'.\str_replace(['%2F', '%3F'], ['/', '?'], \rawurlencode($path)) : '/';

        $this->headers['content-type'] = $contentType;
        $this->headers['content-md5'] = \base64_encode(md5($data, true)); // TODO whould this work well with big file? can we skip it?
        $this->amzHeaders['x-amz-content-sha256'] = \hash('sha256', $data);
        $this->amzHeaders['x-amz-acl'] = $this->acl;

        $this->call(self::METHOD_PUT, $uri, $data);

        return true;
    }

    /**
     * Move file from given source to given path, Return true on success and false on failure.
     *
     * @see http://php.net/manual/en/function.filesize.php
     *
     * @throw \Exception
     */
    public function move(string $source, string $target): bool
    {
        $type = $this->getFileMimeType($source);

        if ($this->write($target, $this->read($source), $type)) {
            $this->delete($source);
        }

        return true;
    }

    /**
     * Delete file in given path, Return true on success and false on failure.
     *
     * @see http://php.net/manual/en/function.filesize.php
     *
     * @throws \Exception
     */
    public function delete(string $path, bool $recursive = false): bool
    {
        $path = $this->getPath($path);

        $uri = ('' !== $path) ? '/'.\str_replace('%2F', '/', \rawurlencode($path)) : '/';

        unset($this->headers['content-type'], $this->amzHeaders['x-amz-acl'], $this->amzHeaders['x-amz-content-sha256']);
        $this->headers['content-md5'] = \base64_encode(md5('', true));
        $this->call(self::METHOD_DELETE, $uri);

        return true;
    }

    /**
     * Get list of objects in the given path.
     *
     * @throws \Exception
     */
    private function listObjects(string $prefix = '', int $maxKeys = 1000, string $continuationToken = ''): array
    {
        $uri = '/';
        $this->headers['content-type'] = 'text/plain';
        $this->headers['content-md5'] = \base64_encode(md5('', true));

        $parameters = [
            'list-type' => 2,
            'prefix' => $prefix,
            'max-keys' => $maxKeys,
        ];
        if (!empty($continuationToken)) {
            $parameters['continuation-token'] = $continuationToken;
        }

        return $this->call(self::METHOD_GET, $uri, '', $parameters)->body;
    }

    /**
     * Delete files in given path, path must be a directory. Return true on success and false on failure.
     *
     * @throws \Exception
     */
    public function deletePath(string $path): bool
    {
        $path = $this->getPath($path);

        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;
        $uri = '/';
        $continuationToken = '';
        do {
            $objects = $this->listObjects($path, continuationToken: $continuationToken);
            $count = (int) ($objects['KeyCount'] ?? 1);
            if ($count < 1) {
                break;
            }
            $continuationToken = $objects['NextContinuationToken'] ?? '';
            $body = '<Delete xmlns="http://s3.amazonaws.com/doc/2006-03-01/">';
            if ($count > 1) {
                foreach ($objects['Contents'] as $object) {
                    $body .= "<Object><Key>{$object['Key']}</Key></Object>";
                }
            } else {
                $body .= "<Object><Key>{$objects['Contents']['Key']}</Key></Object>";
            }
            $body .= '<Quiet>true</Quiet>';
            $body .= '</Delete>';
            $this->amzHeaders['x-amz-content-sha256'] = \hash('sha256', $body);
            $this->headers['content-md5'] = \base64_encode(md5($body, true));
            $this->call(self::METHOD_POST, $uri, $body, ['delete' => '']);
        } while (!empty($continuationToken));

        return true;
    }

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool
    {
        $path = $this->getPath($path);

        try {
            $this->getInfo($path);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

    /**
     * Returns given file path its size.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function getFileSize(string $path): int
    {
        $path = $this->getPath($path);

        $response = $this->getInfo($path);

        return (int) ($response['content-length'] ?? 0);
    }

    /**
     * Returns given file path its mime type.
     *
     * @see http://php.net/manual/en/function.mime-content-type.php
     */
    public function getFileMimeType(string $path): string
    {
        $path = $this->getPath($path);

        $response = $this->getInfo($path);

        return $response['content-type'] ?? '';
    }

    /**
     * Returns given file path its MD5 hash value.
     *
     * @see http://php.net/manual/en/function.md5-file.php
     */
    public function getFileHash(string $path): string
    {
        $path = $this->getPath($path);

        $etag = $this->getInfo($path)['etag'] ?? '';

        return (!empty($etag)) ? substr($etag, 1, -1) : $etag;
    }

    /**
     * Get file info.
     */
    private function getInfo(string $path): array
    {
        unset($this->headers['content-type'], $this->amzHeaders['x-amz-acl'], $this->amzHeaders['x-amz-content-sha256']);
        $this->headers['content-md5'] = \base64_encode(md5('', true));
        $uri = '' !== $path ? '/'.\str_replace('%2F', '/', \rawurlencode($path)) : '/';

        return $this->call(self::METHOD_HEAD, $uri)->headers;
    }

    /**
     * Generate the headers for AWS Signature V4.
     */
    private function getSignatureV4(string $method, string $uri, array $parameters = []): string
    {
        $service = 's3';
        $region = $this->region;

        $algorithm = 'AWS4-HMAC-SHA256';
        $combinedHeaders = [];

        $amzDateStamp = \substr($this->amzHeaders['x-amz-date'], 0, 8);

        // CanonicalHeaders
        foreach ($this->headers as $k => $v) {
            $combinedHeaders[\strtolower($k)] = \trim($v);
        }

        foreach ($this->amzHeaders as $k => $v) {
            $combinedHeaders[\strtolower($k)] = \trim($v);
        }

        uksort($combinedHeaders, [&$this, 'sortMetaHeadersCmp']);

        // Convert null query string parameters to strings and sort
        uksort($parameters, [&$this, 'sortMetaHeadersCmp']);
        $queryString = \http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);

        // Payload
        $amzPayload = [$method];

        $qsPos = \strpos($uri, '?');
        $amzPayload[] = (false === $qsPos ? $uri : \substr($uri, 0, $qsPos));

        $amzPayload[] = $queryString;

        foreach ($combinedHeaders as $k => $v) { // add header as string to requests
            $amzPayload[] = $k.':'.$v;
        }

        $amzPayload[] = ''; // add a blank entry so we end up with an extra line break
        $amzPayload[] = \implode(';', \array_keys($combinedHeaders)); // SignedHeaders
        $amzPayload[] = $this->amzHeaders['x-amz-content-sha256']; // payload hash

        $amzPayloadStr = \implode("\n", $amzPayload); // request as string

        // CredentialScope
        $credentialScope = [$amzDateStamp, $region, $service, 'aws4_request'];

        // stringToSign
        $stringToSignStr = \implode("\n", [$algorithm, $this->amzHeaders['x-amz-date'],
            \implode('/', $credentialScope), \hash('sha256', $amzPayloadStr), ]);

        // Make Signature
        $kSecret = 'AWS4'.$this->secretKey;
        $kDate = \hash_hmac('sha256', $amzDateStamp, $kSecret, true);
        $kRegion = \hash_hmac('sha256', $region, $kDate, true);
        $kService = \hash_hmac('sha256', $service, $kRegion, true);
        $kSigning = \hash_hmac('sha256', 'aws4_request', $kService, true);

        $signature = \hash_hmac('sha256', \utf8_encode($stringToSignStr), $kSigning);

        return $algorithm.' '.\implode(',', [
            'Credential='.$this->accessKey.'/'.\implode('/', $credentialScope),
            'SignedHeaders='.\implode(';', \array_keys($combinedHeaders)),
            'Signature='.$signature,
        ]);
    }

    /**
     * Get the S3 response.
     *
     * @throws \Exception
     */
    private function call(string $method, string $uri, string $data = '', array $parameters = []): object
    {
        $url = 'https://'.$this->headers['host'].$uri.'?'.\http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
        $response = new \stdClass();
        $response->body = '';
        $response->headers = [];

        // Basic setup
        $curl = \curl_init();
        \curl_setopt($curl, CURLOPT_USERAGENT, 'storage-bundle/storage');
        \curl_setopt($curl, CURLOPT_URL, $url);

        // Headers
        $httpHeaders = [];
        $this->amzHeaders['x-amz-date'] = \gmdate('Ymd\THis\Z');

        if (!isset($this->amzHeaders['x-amz-content-sha256'])) {
            $this->amzHeaders['x-amz-content-sha256'] = \hash('sha256', $data);
        }

        foreach ($this->amzHeaders as $header => $value) {
            if ('' !== $value) {
                $httpHeaders[] = $header.': '.$value;
            }
        }

        $this->headers['date'] = \gmdate('D, d M Y H:i:s T');
        foreach ($this->headers as $header => $value) {
            if ('' !== $value) {
                $httpHeaders[] = $header.': '.$value;
            }
        }

        $httpHeaders[] = 'Authorization: '.$this->getSignatureV4($method, $uri, $parameters);

        \curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeaders);
        \curl_setopt($curl, CURLOPT_HEADER, false);
        \curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
        \curl_setopt($curl, CURLOPT_WRITEFUNCTION, static function ($curl, string $data) use ($response) {
            $response->body .= $data;

            return \strlen($data);
        });
        curl_setopt($curl, CURLOPT_HEADERFUNCTION, static function ($curl, string $header) use (&$response) {
            $len = strlen($header);
            $headers = explode(':', $header, 2);

            if (count($headers) < 2) { // ignore invalid headers
                return $len;
            }

            $response->headers[strtolower(trim($headers[0]))] = trim($headers[1]);

            return $len;
        });
        \curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        \curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        // Request types
        switch ($method) {
            case self::METHOD_PUT:
            case self::METHOD_POST: // POST only used for CloudFront
                \curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case self::METHOD_HEAD:
            case self::METHOD_DELETE:
                \curl_setopt($curl, CURLOPT_NOBODY, true);
                break;
        }

        $result = \curl_exec($curl);

        if (!$result) {
            throw new \RuntimeException(\curl_error($curl));
        }

        $response->code = \curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($response->code >= 400) {
            throw new \RuntimeException($response->body, $response->code);
        }

        \curl_close($curl);

        // Parse body into XML
        /* @phpstan-ignore-next-line */
        if ((isset($response->headers['content-type']) && 'application/xml' === $response->headers['content-type']) || (str_starts_with($response->body, '<?xml') && ($response->headers['content-type'] ?? '') !== 'image/svg+xml')) {
            $response->body = \simplexml_load_string($response->body);
            $response->body = json_decode(json_encode($response->body, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR);
        }

        return $response;
    }

    /**
     * Sort compare for meta headers.
     *
     * @internal Used to sort x-amz meta headers
     *
     * @param string $a String A
     * @param string $b String B
     */
    private function sortMetaHeadersCmp(string $a, string $b): int
    {
        $lenA = \strlen($a);
        $lenB = \strlen($b);
        $minLen = \min($lenA, $lenB);
        $ncmp = \strncmp($a, $b, $minLen);
        if ($lenA === $lenB) {
            return $ncmp;
        }

        if (0 === $ncmp) {
            return $lenA < $lenB ? -1 : 1;
        }

        return $ncmp;
    }
}
