<?php

namespace Package\StorageBundle\Storage;

use Package\StorageBundle\Client\DriverInterface;
use Package\StorageBundle\Client\SimpleS3Client;

/**
 * @method SimpleS3Client getClient()
 * @method bool           upload(string $sourcePath, string $storagePath, array $metadata = [])
 * @method bool           write(string $content, string $storagePath, string $contentType = 'text/plain', array $metadata = [])
 * @method bool           exists(string $storagePath)
 * @method string         download(string $storagePath)
 * @method resource       downloadResource(string $storagePath)
 * @method iterable       downloadChunk(string $storagePath)
 * @method string         getUrl(string $storagePath)
 * @method bool           delete(string $storagePath)
 * @method int            getSize(string $storagePath)
 * @method string         getMimeType(string $storagePath)
 */
class Storage
{
    /**
     * @param DriverInterface[] $devices
     */
    public function __construct(private readonly string $default, private readonly array $devices)
    {
    }

    public function device(string $deviceKey): DriverInterface
    {
        return $this->devices[$deviceKey];
    }

    public function getStorageKey(): string
    {
        return $this->default;
    }

    public function __call(string $name, array $parameters = null): mixed
    {
        return $this->devices[$this->default]->{$name}(...$parameters);
    }
}
