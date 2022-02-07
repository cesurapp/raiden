<?php

namespace Package\ApiBundle\Utils;

use Package\ApiBundle\Contract\ApiResourceInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Symfony Simple Response
 */
class ApiResponse
{
    public static function json(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        return new JsonResponse($data, $status, $headers);
    }

    public static function jsonError(array $data, int $status = 403, array $headers = []): JsonResponse
    {
        return self::json($data, $status, $headers);
    }

    public static function jsonResource(ApiResourceInterface|array $resource, string $type = 'default', int $status = 200, array $headers = []): JsonResponse
    {
        if (is_array($resource)) {
            return self::json(array_map(static fn($res) => $res->{"{$type}Resource"}(), $resource));
        }
        return self::json($resource->{"{$type}Resource"}(), $status, $headers);
    }

    /**
     * Download Binary File
     */
    public static function file(\SplFileInfo|string $filePath, string $fileName = '', string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT): BinaryFileResponse
    {
        return (new BinaryFileResponse($filePath))->setContentDisposition($disposition, $fileName);
    }

    /**
     * Download Large File
     */
    public static function fileLarge(string $filePath, string $fileName = null): StreamedResponse
    {
        $file = new File($filePath);

        return new StreamedResponse(static function () use ($filePath) {
            $output = fopen('php://output', 'wb+');
            $handle = fopen($filePath, "rb");

            while (!feof($handle)) {
                fwrite($output, fread($handle, 2048));
            }

            fclose($output);
            fclose($handle);
        }, 200, [
            'Content-Type' => $file->getMimeType(),
            'Content-Disposition' => HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, $fileName ?? $file->getFilename())
        ]);
    }
}