<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Driver\Local;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LocalTest extends WebTestCase
{
    protected ?Local $object;

    protected string $rootDir;

    public function setUp(): void
    {
        $this->rootDir = self::createKernel()->getCacheDir();
        $this->object = new Local($this->rootDir);
    }

    public function tearDown(): void
    {
    }

    public function testName(): void
    {
        $this->assertEquals('Local Storage', $this->object->getName());
    }

    public function testDescription(): void
    {
        $this->assertEquals(
            'Adapter for Local storage that is in the physical or virtual machine or mounted to it.',
            $this->object->getDescription()
        );
    }

    public function testRoot(): void
    {
        $this->assertEquals($this->object->getRoot(), $this->rootDir);
    }

    public function testWrite(): void
    {
        $this->assertEquals(true, $this->object->write('text.txt', 'Hello World'));
        $this->assertEquals(true, $this->object->exists('text.txt'));
        $this->assertEquals(true, is_readable($this->object->getRoot().DIRECTORY_SEPARATOR.'text.txt'));

        $this->object->delete('text.txt');
    }

    public function testRead(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-read.txt', 'Hello World'));
        $this->assertEquals('Hello World', $this->object->read('text-for-read.txt'));

        $this->object->delete('text-for-read.txt');
    }

    public function testFileExists(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-test-exists.txt', 'Hello World'));
        $this->assertEquals(true, $this->object->exists('text-for-test-exists.txt'));
        $this->assertEquals(false, $this->object->exists('text-for-test-doesnt-exist.txt'));

        $this->object->delete('text-for-test-exists.txt');
    }

    public function testMove(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-move.txt', 'Hello World'));
        $this->assertEquals('Hello World', $this->object->read('text-for-move.txt'));
        $this->assertEquals(true, $this->object->move($this->object->getPath('text-for-move.txt'), 'text-for-move-new.txt'));
        $this->assertEquals('Hello World', $this->object->read('text-for-move-new.txt'));
        $this->assertEquals(false, file_exists($this->object->getPath('text-for-move.txt')));
        $this->assertEquals(false, is_readable($this->object->getPath('text-for-move.txt')));
        $this->assertEquals(true, file_exists($this->object->getPath('text-for-move-new.txt')));
        $this->assertEquals(true, is_readable($this->object->getPath('text-for-move-new.txt')));

        $this->object->delete('text-for-move-new.txt');
    }

    public function testDelete(): void
    {
        $this->assertEquals(true, $this->object->write('text-for-delete.txt', 'Hello World'));
        $this->assertEquals('Hello World', $this->object->read('text-for-delete.txt'));
        $this->assertEquals(true, $this->object->delete('text-for-delete.txt'));
        $this->assertEquals(false, file_exists($this->object->getPath('text-for-delete.txt')));
        $this->assertEquals(false, is_readable($this->object->getPath('text-for-delete.txt')));
    }

    public function testFileSize(): void
    {
        $this->assertEquals(77931, filesize(__DIR__.'/../resources/disk-a/kitten-1.jpg'));
        $this->assertEquals(131958, filesize(__DIR__.'/../resources/disk-a/kitten-2.jpg'));
    }

    public function testFileMimeType(): void
    {
        $this->assertEquals(mime_content_type(__DIR__.'/../resources/disk-a/kitten-1.jpg'), 'image/jpeg');
        $this->assertEquals(mime_content_type(__DIR__.'/../resources/disk-a/kitten-2.jpg'), 'image/jpeg');
        $this->assertEquals(mime_content_type(__DIR__.'/../resources/disk-b/kitten-1.png'), 'image/png');
        $this->assertEquals(mime_content_type(__DIR__.'/../resources/disk-b/kitten-2.png'), 'image/png');
    }

    public function testFileHash(): void
    {
        $this->assertEquals('277be0ebe51e975e8e9cc7492b451911', md5_file(__DIR__.'/../resources/disk-a/kitten-1.jpg'));
        $this->assertEquals('81702fdeef2e55b1a22617bce4951cb5', md5_file(__DIR__.'/../resources/disk-a/kitten-2.jpg'));
        $this->assertEquals('03010f4f02980521a8fd6213b52ec313', md5_file(__DIR__.'/../resources/disk-b/kitten-1.png'));
        $this->assertEquals('8a9ed992b77e4b62b10e3a5c8ed72062', md5_file(__DIR__.'/../resources/disk-b/kitten-2.png'));
    }

    public function testDirectorySize(): void
    {
        $this->assertGreaterThan(0, $this->object->getDirectorySize(__DIR__.'/../resources/disk-a/'));
        $this->assertGreaterThan(0, $this->object->getDirectorySize(__DIR__.'/../resources/disk-b/'));
    }

    public function testPartUpload(): string
    {
        $source = __DIR__.'/../resources/disk-a/large_file.mov';
        $filename = uniqid('', false).'.mov';
        $totalSize = filesize($source);
        $chunkSize = 2097152;

        $chunks = ceil($totalSize / $chunkSize);

        $chunk = 1;
        $start = 0;

        $handle = @fopen($source, 'rb');
        while ($start < $totalSize) {
            $contents = fread($handle, $chunkSize);
            $op = __DIR__.'/chunk.part';
            $cc = fopen($op, 'wb');
            fwrite($cc, $contents);
            fclose($cc);
            $this->object->upload($op, $filename, $chunk, (int) $chunks);
            $start += strlen($contents);
            ++$chunk;
            fseek($handle, $start);
        }
        @fclose($handle);
        $this->assertEquals(filesize($source), $this->object->getFileSize($filename));
        $this->assertEquals(\md5_file($source), $this->object->getFileHash($filename));

        return $filename;
    }

    public function testAbort(): void
    {
        $source = __DIR__.'/../resources/disk-a/large_file.mp4';
        $filename = 'abcduploaded.mp4';
        $totalSize = filesize($source);
        $chunkSize = 2097152;
        $chunks = ceil($totalSize / $chunkSize);

        $chunk = 1;
        $start = 0;

        $handle = @fopen($source, 'rb');
        while ($chunk < 3) { // only upload two chunks
            $contents = fread($handle, $chunkSize);
            $op = __DIR__.'/chunk.part';
            $cc = fopen($op, 'wb');
            fwrite($cc, $contents);
            fclose($cc);
            $this->object->upload($op, $filename, $chunk, (int) $chunks);
            $start += strlen($contents);
            ++$chunk;
            fseek($handle, $start);
        }
        @fclose($handle);

        // using file name with same first four chars
        $filename1 = 'abcduploaded2.mp4';
        $totalSize = filesize($source);
        $chunks = ceil($totalSize / $chunkSize);

        $chunk = 1;
        $start = 0;

        $handle = @fopen($source, 'rb');
        while ($chunk < 3) { // only upload two chunks
            $contents = fread($handle, $chunkSize);
            $op = __DIR__.'/chunk.part';
            $cc = fopen($op, 'wb');
            fwrite($cc, $contents);
            fclose($cc);
            $this->object->upload($op, $filename1, $chunk, (int) $chunks);
            $start += strlen($contents);
            ++$chunk;
            fseek($handle, $start);
        }
        @fclose($handle);

        $this->assertTrue($this->object->abort($filename));
        $this->assertTrue($this->object->abort($filename1));
    }

    public function testPartitionFreeSpace(): void
    {
        $this->assertGreaterThan(0, $this->object->getPartitionFreeSpace());
    }

    public function testPartitionTotalSpace(): void
    {
        $this->assertGreaterThan(0, $this->object->getPartitionTotalSpace());
    }
}
