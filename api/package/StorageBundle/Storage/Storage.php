<?php

namespace Package\StorageBundle\Storage;

use Package\StorageBundle\Driver\DriverInterface;

/**
 * @method string getName()
 * @method string getDescription()
 * @method string getRoot()
 * @method int    upload(string $source, string $path, int $chunk = 1, int $chunks = 1, array &$metadata = [])
 * @method bool   abort(string $path, string $extra = '')
 * @method string read(string $path, int $offset = 0, int $length = null)
 * @method bool   write(string $path, string $data, string $contentType)
 * @method bool   move(string $source, string $target)
 * @method bool   delete(string $path, bool $recursive = false)
 * @method bool   deletePath(string $path)
 * @method bool   exists(string $path)
 * @method int    getFileSize(string $path)
 * @method string getFileMimeType(string $path)
 * @method string getFileHash(string $path)
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

    public function __call(string $name, array $parameters = null): mixed
    {
        return $this->devices[$this->default]->{$name}(...$parameters);
    }
}
