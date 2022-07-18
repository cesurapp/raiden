<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\S3;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class S3Base extends WebTestCase
{
    abstract protected function init(): void;

    abstract protected function getAdapterName(): string;

    abstract protected function getAdapterDescription(): string;

    protected ?S3 $object;

    protected string $root = '/root';

    public function setUp(): void
    {
        $this->init();
        $this->uploadTestFiles();
    }

    private function uploadTestFiles(): void
    {
        $this->object->upload(__DIR__.'/../resources/disk-a/kitten-1.jpg', 'testing/kitten-1.jpg');
    }

    private function removeTestFiles(): void
    {
        $this->object->delete('testing/kitten-1.jpg');
    }

    public function tearDown(): void
    {
        $this->removeTestFiles();
    }

    public function testName(): void
    {
        $this->assertEquals($this->getAdapterName(), $this->object->getName());
    }

    public function testDescription(): void
    {
        $this->assertEquals($this->getAdapterDescription(), $this->object->getDescription());
    }

    public function testRoot(): void
    {
        $this->assertEquals($this->root, $this->object->getRoot());
    }

    public function testWrite(): void
    {
        $this->assertEquals(true, $this->object->write('text.txt', 'Hello World', 'text/plain'));

        $this->object->delete('text.txt');
    }

    public function testRead(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-read.txt', 'Hello World', 'text/plain'));
        $this->assertEquals('Hello World', $this->object->read('text-for-read.txt'));

        $this->object->delete('text-for-read.txt');
    }

    public function testFileExists(): void
    {
        $this->assertEquals(true, $this->object->exists('testing/kitten-1.jpg'));
        $this->assertEquals(false, $this->object->exists('testing/kitten-5.jpg'));
    }

    public function testMove(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-move.txt', 'Hello World', 'text/plain'));
        $this->assertEquals(true, $this->object->exists('text-for-move.txt'));
        $this->assertEquals(true, $this->object->move('text-for-move.txt', 'text-for-move-new.txt'));
        $this->assertEquals('Hello World', $this->object->read('text-for-move-new.txt'));
        $this->assertEquals(false, $this->object->exists('text-for-move.txt'));

        $this->object->delete('text-for-move-new.txt');
    }

    public function testDelete(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-delete.txt', 'Hello World', 'text/plain'));
        $this->assertEquals(true, $this->object->exists('text-for-delete.txt'));
        $this->assertEquals(true, $this->object->delete('text-for-delete.txt'));
    }

    public function testDeletePath(): void
    {
        // Test Single Object
        $path = $this->object->getPath('text-for-delete-path.txt');
        $path = str_ireplace($this->object->getRoot(), $this->object->getRoot().DIRECTORY_SEPARATOR.'bucket', $path);
        $this->assertEquals(true, $this->object->write($path, 'Hello World', 'text/plain'));
        $this->assertEquals(true, $this->object->exists($path));
        $this->assertEquals(true, $this->object->deletePath('bucket'));
        $this->assertEquals(false, $this->object->exists($path));

        // Test Multiple Objects
        $path = $this->object->getPath('text-for-delete-path1.txt');
        $path = str_ireplace($this->object->getRoot(), $this->object->getRoot().DIRECTORY_SEPARATOR.'bucket', $path);
        $this->assertEquals(true, $this->object->write($path, 'Hello World', 'text/plain'));
        $this->assertEquals(true, $this->object->exists($path));

        $path2 = $this->object->getPath('text-for-delete-path2.txt');
        $path2 = str_ireplace($this->object->getRoot(), $this->object->getRoot().DIRECTORY_SEPARATOR.'bucket', $path2);
        $this->assertEquals(true, $this->object->write($path2, 'Hello World', 'text/plain'));
        $this->assertEquals(true, $this->object->exists($path2));

        $this->assertEquals(true, $this->object->deletePath('bucket'));
        $this->assertEquals(false, $this->object->exists($path));
        $this->assertEquals(false, $this->object->exists($path2));
    }

    public function testFileSize(): void
    {
        $this->assertEquals(77931, $this->object->getFileSize('testing/kitten-1.jpg'));
    }

    public function testFileMimeType(): void
    {
        $this->assertEquals('image/jpeg', $this->object->getFileMimeType('testing/kitten-1.jpg'));
    }

    public function testFileHash(): void
    {
        $this->assertEquals('7551f343143d2e24ab4aaf4624996b6a', $this->object->getFileHash('testing/kitten-1.jpg'));
    }

    public function testPartUpload(): string
    {
        $source = __DIR__.'/../resources/disk-a/large_file.mov';
        $dest = uniqid('', false).'.mov';
        $totalSize = \filesize($source);
        // AWS S3 requires each part to be at least 5MB except for last part
        $chunkSize = 5 * 1024 * 1024;

        $chunks = ceil($totalSize / $chunkSize);

        $chunk = 1;
        $start = 0;

        $metadata = [
            'parts' => [],
            'chunks' => 0,
            'uploadId' => null,
            'content_type' => \mime_content_type($source),
        ];
        $handle = @fopen($source, 'rb');
        $op = __DIR__.'/chunk.part';
        while ($start < $totalSize) {
            $contents = fread($handle, $chunkSize);
            $cc = fopen($op, 'wb');
            fwrite($cc, $contents);
            fclose($cc);
            $etag = $this->object->upload($op, $dest, $chunk, (int) $chunks, $metadata);
            $parts[] = ['partNumber' => $chunk, 'etag' => $etag];
            $start += strlen($contents);
            ++$chunk;
            fseek($handle, $start);
        }
        @fclose($handle);
        unlink($op);

        $this->assertEquals(\filesize($source), $this->object->getFileSize($dest));

        // S3 doesnt provide a method to get a proper MD5-hash of a file created using multipart upload
        // https://stackoverflow.com/questions/8618218/amazon-s3-checksum
        // More info on how AWS calculates ETag for multipart upload here
        // https://savjee.be/2015/10/Verifying-Amazon-S3-multi-part-uploads-with-ETag-hash/
        // TODO
        // $this->assertEquals(\md5_file($source), $this->object->getFileHash($dest));
        // $this->object->delete($dest);
        return $dest;
    }
}
