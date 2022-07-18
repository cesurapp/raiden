<?php

namespace Package\StorageBundle\Client;

interface DriverInterface
{
    public function getClient(): SimpleS3Client|self;

    public function upload(string $sourcePath, string $storagePath, array $metadata = []): bool;

    public function write(string $content, string $storagePath, string $contentType = 'text/plain', array $metadata = []): bool;

    public function exists(string $storagePath): bool;

    public function download(string $storagePath): string;

    /**
     * @return resource
     */
    public function downloadResource(string $storagePath);

    public function downloadChunk(string $storagePath): iterable;

    public function getUrl(string $storagePath): string;

    public function delete(string $storagePath): bool;

    public function getSize(string $storagePath): int;

    public function getMimeType(string $storagePath): string;
}
