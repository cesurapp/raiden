<?php

namespace Package\ApiBundle\Response;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Symfony Simple Response.
 */
class ApiResponse2
{
    public static function create(mixed $data, int $status = 200, array $headers = []): array
    {
        return [
            'type' => 'ApiResult',
            'data' => $data,
            'options' => [
                'status' => $status,
                'headers' => $headers,
            ],
        ];
    }

    public static function errorMsg(array $messages, int $status = 403, array $headers = []): array
    {
        return [
            'type' => 'ApiError',
            'data' => $messages,
            'options' => [
                'status' => $status,
                'headers' => $headers,
            ],
        ];
    }

    public static function infoMsg(array $messages, int $status = 403, array $headers = []): array
    {
        return [
            'type' => 'ApiInfo',
            'data' => $messages,
            'options' => [
                'status' => $status,
                'headers' => $headers,
            ],
        ];
    }

    public static function warnMsg(array $messages, int $status = 403, array $headers = []): array
    {
        return [
            'type' => 'ApiWarning',
            'data' => $messages,
            'options' => [
                'status' => $status,
                'headers' => $headers,
            ],
        ];
    }

    public static function paginate(QueryBuilder|Query $query, ?int $max = null, array $headers = []): array
    {
        return [
            'type' => 'ApiResult',
            'data' => $query,
            'options' => [
                'status' => 200,
                'headers' => $headers,
                'pager' => 'Offset',
                'pagerMax' => $max,
                'pagerPage' => null,
                'fetchJoin' => true,
            ],
        ];
    }

    public static function paginateCursor(QueryBuilder|Query $query, ?int $max = null, array $headers = []): array
    {
        return [
            'type' => 'ApiResult',
            'data' => $query,
            'options' => [
                'status' => 200,
                'headers' => $headers,
                'pager' => 'Cursor',
                'pagerMax' => $max,
            ],
        ];
    }

    /**
     * Download Binary File.
     */
    public static function file(\SplFileInfo|string $path, string $fileName = '', string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        return (new BinaryFileResponse($path))->setContentDisposition($disposition, $fileName);
    }

    /**
     * Download Large File.
     */
    public static function fileLarge(string $filePath, string $fileName = null): StreamedResponse
    {
        $file = new File($filePath);

        return new StreamedResponse(static function () use ($filePath) {
            $output = fopen('php://output', 'wb+');
            $handle = fopen($filePath, 'rb');

            while (!feof($handle)) {
                fwrite($output, fread($handle, 2048));
            }

            fclose($output);
            fclose($handle);
        }, 200, [
            'Content-Type' => $file->getMimeType(),
            'Content-Disposition' => HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                $fileName ?? $file->getFilename()
            ),
        ]);
    }
}
