<?php

namespace Package\StorageBundle\Client;

abstract class AbstractDriver implements DriverInterface
{
    protected SimpleS3Client $client;

    public function __construct(
        protected string $accessKey,
        protected string $secretKey,
        protected string $bucket,
        protected string $root,
        protected string $endPoint = '',
        protected string $region = '',
    ) {
    }

    protected function getRoot(): string
    {
        return rtrim($this->root, '\\/');
    }

    protected function getPath(string $filename): string
    {
        return $this->getRoot().DIRECTORY_SEPARATOR.ltrim($filename, '\\/');
    }

    public function getClient(): SimpleS3Client
    {
        return $this->client;
    }

    public function upload(string $sourcePath, string $storagePath, array $metadata = []): bool
    {
        $fp = \fopen($sourcePath, 'rb');

        if (empty($metadata['ContentType'])) {
            $metadata['ContentType'] = mime_content_type($fp);
        }

        $result = $this->getClient()->upload($this->bucket, $this->getPath($storagePath), $fp, $metadata);

        return 200 === $result->info()['response']->getStatusCode();
    }

    public function write(
        string $content,
        string $storagePath,
        string $contentType = 'text/plain',
        array $metadata = []
    ): bool {
        if (!empty($metadata['ContentType'])) {
            $metadata['ContentType'] = $contentType;
        }

        $result = $this->getClient()->putObject(
            array_merge($metadata, [
                'Bucket' => $this->bucket,
                'Key' => $this->getPath($storagePath),
                'Body' => $content,
            ])
        );

        return 200 === $result->info()['response']->getStatusCode();
    }

    public function exists(string $storagePath): bool
    {
        return $this->getClient()->has($this->bucket, $this->getPath($storagePath));
    }

    public function download(string $storagePath): string
    {
        return $this->getClient()->download($this->bucket, $this->getPath($storagePath))->getContentAsString();
    }

    /**
     * @return resource
     */
    public function downloadResource(string $storagePath)
    {
        return $this->getClient()->download($this->bucket, $this->getPath($storagePath))->getContentAsResource();
    }

    public function downloadChunk(string $storagePath): iterable
    {
        return $this->getClient()->download($this->bucket, $this->getPath($storagePath))->getChunks();
    }

    public function getUrl(string $storagePath): string
    {
        return $this->getClient()->getUrl($this->bucket, $this->getPath($storagePath));
    }

    public function delete(string $storagePath): bool
    {
        $result = $this->getClient()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getPath($storagePath),
        ]);

        return 204 === $result->info()['response']->getStatusCode();
    }

    public function getSize(string $storagePath): int
    {
        $head = $this->getClient()->headObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getPath($storagePath),
        ]);

        return (int) $head->getContentLength();
    }

    public function getMimeType(string $storagePath): string
    {
        $head = $this->getClient()->headObject([
            'Bucket' => $this->bucket,
            'Key' => $this->getPath($storagePath),
        ]);

        return $head->getContentType();
    }
}
