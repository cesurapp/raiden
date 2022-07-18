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

    public function testWrite(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text.txt'));
        $this->assertEquals(true, $this->object->exists('text.txt'));
        $this->assertEquals(true, is_readable($this->object->getUrl('text.txt')));

        $this->object->delete('text.txt');
    }

    public function testDownload(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text-for-read.txt'));
        $this->assertEquals('Hello World', $this->object->download('text-for-read.txt'));

        $this->object->delete('text-for-read.txt');
    }

    public function testDownloadResource(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text-for-read.txt'));
        $this->assertIsResource($this->object->downloadResource('text-for-read.txt'));
    }

    public function testFileExists(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text-for-test-exists.txt'));
        $this->assertEquals(true, $this->object->exists('text-for-test-exists.txt'));
        $this->assertEquals(false, $this->object->exists('text-for-test-doesnt-exist.txt'));

        $this->object->delete('text-for-test-exists.txt');
    }

    public function testMove(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text-for-move.txt'));
        $this->assertEquals('Hello World', $this->object->download('text-for-move.txt'));
        $this->assertEquals(true, $this->object->move($this->object->getUrl('text-for-move.txt'), 'text-for-move-new.txt'));
        $this->assertEquals('Hello World', $this->object->download('text-for-move-new.txt'));
        $this->assertEquals(false, file_exists($this->object->getUrl('text-for-move.txt')));
        $this->assertEquals(false, is_readable($this->object->getUrl('text-for-move.txt')));
        $this->assertEquals(true, file_exists($this->object->getUrl('text-for-move-new.txt')));
        $this->assertEquals(true, is_readable($this->object->getUrl('text-for-move-new.txt')));

        $this->object->delete('text-for-move-new.txt');
    }

    public function testDelete(): void
    {
        $this->assertEquals(true, $this->object->write('Hello World', 'text-for-delete.txt'));
        $this->assertEquals('Hello World', $this->object->download('text-for-delete.txt'));
        $this->assertEquals(true, $this->object->delete('text-for-delete.txt'));
        $this->assertEquals(false, file_exists($this->object->getUrl('text-for-delete.txt')));
        $this->assertEquals(false, is_readable($this->object->getUrl('text-for-delete.txt')));
    }
}
