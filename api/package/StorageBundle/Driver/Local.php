<?php

namespace Package\StorageBundle\Driver;

/**
 * @see https://github.com/utopia-php/storage
 */
class Local implements DriverInterface
{
    public function __construct(private string $root)
    {
        $this->root = rtrim($root, '\\/');
    }

    public function getName(): string
    {
        return 'Local Storage';
    }

    public function getDescription(): string
    {
        return 'Adapter for Local storage that is in the physical or virtual machine or mounted to it.';
    }

    public function getRoot(): string
    {
        return $this->root;
    }

    public function getPath(string $filename): string
    {
        return $this->getRoot().DIRECTORY_SEPARATOR.ltrim($filename, '\\/');
    }

    /**
     * Upload.
     *
     * Upload a file to desired destination in the selected disk.
     * return number of chunks uploaded or 0 if it fails.
     *
     * @throws \RuntimeException
     */
    public function upload(string $source, string $path, int $chunk = 1, int $chunks = 1, array &$metadata = []): int
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.ltrim($path, '\\/');

        if (!file_exists(dirname($path)) && !@mkdir(dirname($path), 0755, true)) {
            throw new \RuntimeException('Can\'t create directory: '.dirname($path));
        }

        if (1 === $chunks) {
            if (!move_uploaded_file($source, $path)) {
                throw new \RuntimeException('Can\'t upload file '.$path);
            }

            return $chunks;
        }
        $tmp = dirname($path).DIRECTORY_SEPARATOR.'tmp_'.basename($path).DIRECTORY_SEPARATOR.basename($path).'_chunks.log';

        if (!file_exists(\dirname($tmp)) && !@mkdir(dirname($tmp), 0755, true)) {
            throw new \RuntimeException('Can\'t create directory: '.\dirname($tmp));
        }
        if (!file_put_contents($tmp, "$chunk\n", FILE_APPEND)) {
            throw new \RuntimeException('Can\'t write chunk log '.$tmp);
        }

        $chunkLogs = file($tmp);
        if (!$chunkLogs) {
            throw new \RuntimeException('Unable to read chunk log '.$tmp);
        }

        $chunksReceived = count(file($tmp));

        if (!\rename($source, dirname($tmp).DIRECTORY_SEPARATOR.pathinfo($path, PATHINFO_FILENAME).'.part.'.$chunk)) {
            throw new \RuntimeException('Failed to write chunk '.$chunk);
        }

        if ($chunks === $chunksReceived) {
            for ($i = 1; $i <= $chunks; ++$i) {
                $part = dirname($tmp).DIRECTORY_SEPARATOR.pathinfo($path, PATHINFO_FILENAME).'.part.'.$i;
                $data = file_get_contents($part);
                if (!$data) {
                    throw new \RuntimeException('Failed to read chunk '.$part);
                }

                if (!file_put_contents($path, $data, FILE_APPEND)) {
                    throw new \RuntimeException('Failed to append chunk '.$part);
                }
                unlink($part);
            }
            unlink($tmp);

            return $chunksReceived;
        }

        return $chunksReceived;
    }

    /**
     * Abort Chunked Upload.
     */
    public function abort(string $path, string $extra = ''): bool
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.ltrim($path, '\\/');

        if (file_exists($path)) {
            unlink($path);
        }

        $tmp = dirname($path).DIRECTORY_SEPARATOR.'tmp_'.basename($path).DIRECTORY_SEPARATOR;

        if (!\file_exists(\dirname($tmp))) {
            throw new \RuntimeException('File doesn\'t exist: '.\dirname($path));
        }
        $files = glob($tmp.'*', GLOB_MARK); // GLOB_MARK adds a slash to directories returned

        foreach ($files as $file) {
            $this->deleteRecursive($file, true);
        }

        return rmdir($tmp);
    }

    /**
     * Read file by given path.
     */
    public function read(string $path, int $offset = 0, int $length = null): string
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.ltrim($path, '\\/');

        if (!file_exists($path)) {
            throw new \RuntimeException('File Not Found');
        }

        return file_get_contents($path, use_include_path: false, context: null, offset: $offset, length: $length);
    }

    /**
     * Write file by given path.
     */
    public function write(string $path, string $data, string $contentType = ''): bool
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.ltrim($path, '\\/');

        if (!\file_exists(\dirname($path)) && !@\mkdir(\dirname($path), 0755, true)) {
            throw new \RuntimeException('Can\'t create directory '.\dirname($path));
        }

        return (bool) \file_put_contents($path, $data);
    }

    /**
     * Move file from given source to given path, Return true on success and false on failure.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function move(string $source, string $target): bool
    {
        $target = $this->getRoot().DIRECTORY_SEPARATOR.ltrim($target, '\\/');

        if (!file_exists(\dirname($target)) && !@\mkdir(\dirname($target), 0755, true)) {
            throw new \RuntimeException('Can\'t create directory '.\dirname($target));
        }

        if (\rename($source, $target)) {
            return true;
        }

        return false;
    }

    /**
     * Delete file in given path, Return true on success and false on failure.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function delete(string $path, bool $recursive = false): bool
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        return $this->deleteRecursive($path, $recursive);
    }

    private function deleteRecursive(string $path, bool $recursive = false): bool
    {
        if ($recursive && is_dir($path)) {
            $files = glob($path.'*', GLOB_MARK);

            foreach ($files as $file) {
                $this->deleteRecursive($file, true);
            }

            rmdir($path);
        } elseif (is_file($path)) {
            return unlink($path);
        }

        return false;
    }

    /**
     * Delete files in given path, path must be a directory. Return true on success and false on failure.
     */
    public function deletePath(string $path): bool
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        if (is_dir($path)) {
            $files = \glob($path.'*', GLOB_MARK);

            foreach ($files as $file) {
                $this->delete($file, true);
            }

            rmdir($path);

            return true;
        }

        return false;
    }

    /**
     * Check if file exists.
     */
    public function exists(string $path): bool
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        return file_exists($path);
    }

    /**
     * Returns given file path its size.
     *
     * @see http://php.net/manual/en/function.filesize.php
     */
    public function getFileSize(string $path): int
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        return filesize($path);
    }

    /**
     * Returns given file path its mime type.
     *
     * @see http://php.net/manual/en/function.mime-content-type.php
     */
    public function getFileMimeType(string $path): string
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        return mime_content_type($path);
    }

    /**
     * Returns given file path its MD5 hash value.
     *
     * @see http://php.net/manual/en/function.md5-file.php
     */
    public function getFileHash(string $path): string
    {
        $path = $this->getRoot().DIRECTORY_SEPARATOR.$path;

        return md5_file($path);
    }

    /**
     * Get directory size in bytes.
     *
     * Return -1 on error
     *
     * Based on http://www.jonasjohn.de/snippets/php/dir-size.htm
     */
    public function getDirectorySize(string $path): int
    {
        $size = 0;

        $directory = opendir($path);

        if (!$directory) {
            return -1;
        }

        while (($file = \readdir($directory)) !== false) {
            // Skip file pointers
            if ('.' === $file[0]) {
                continue;
            }

            // Go recursive down, or add the file size
            if (is_dir($path.$file)) {
                $size += $this->getDirectorySize($path.$file.DIRECTORY_SEPARATOR);
            } else {
                $size += filesize($path.$file);
            }
        }

        closedir($directory);

        return $size;
    }

    /**
     * Get Partition Free Space.
     *
     * disk_free_space — Returns available space on filesystem or disk partition
     */
    public function getPartitionFreeSpace(): float
    {
        return disk_free_space($this->getRoot());
    }

    /**
     * Get Partition Total Space.
     *
     * disk_total_space — Returns the total size of a filesystem or disk partition
     */
    public function getPartitionTotalSpace(): float
    {
        return disk_total_space($this->getRoot());
    }
}
