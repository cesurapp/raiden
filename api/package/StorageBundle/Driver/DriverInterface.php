<?php

namespace Package\StorageBundle\Driver;

interface DriverInterface
{
    /**
     * Get Name.
     *
     * Get storage device name
     */
    public function getName(): string;

    /**
     * Get Description.
     *
     * Get storage device description and purpose.
     */
    public function getDescription(): string;

    /**
     * Get Root.
     *
     * Get storage device root path
     */
    public function getRoot(): string;

    /**
     * Get Path.
     *
     * Each device hold a complex directory structure that is being build in this method.
     */
    public function getPath(string $filename): string;

    /**
     * Upload.
     *
     * Upload a file to desired destination in the selected disk
     * return number of chunks uploaded or 0 if it fails.
     *
     * @throws \Exception
     */
    public function upload(string $source, string $path, int $chunk = 1, int $chunks = 1, array &$metadata = []): int;

    /**
     * Abort Chunked Upload.
     */
    public function abort(string $path, string $extra = ''): bool;

    /**
     * Read file by given path.
     */
    public function read(string $path, int $offset = 0, int $length = null): string;

    /**
     * Write file by given path.
     */
    public function write(string $path, string $data, string $contentType): bool;

    /**
     * Move file from given source to given path, return true on success and false on failure.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function move(string $source, string $target): bool;

    /**
     * Delete file in given path return true on success and false on failure.
     */
    public function delete(string $path, bool $recursive = false): bool;

    /**
     * Delete files in given path, path must be a directory. return true on success and false on failure.
     */
    public function deletePath(string $path): bool;

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool;

    /**
     * Returns given file path its size.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function getFileSize(string $path): int;

    /**
     * Returns given file path its mime type.
     *
     * @see http://php.net/manual/en/function.mime-content-type.php
     */
    public function getFileMimeType(string $path): string;

    /**
     * Returns given file path its MD5 hash value.
     *
     * @see http://php.net/manual/en/function.md5-file.php
     */
    public function getFileHash(string $path): string;
}
