<?php

namespace Package\StorageBundle\Test\Driver;

use Package\StorageBundle\Client\DriverInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class S3Base extends WebTestCase
{
    abstract protected function init(): void;

    protected ?DriverInterface $client;

    public function setUp(): void
    {
        $this->init();
    }

    public function testUpload(): void
    {
        $this->assertTrue($this->client->upload(__DIR__.'/../resources/file-1.jpg', 'testing/file-1.jpg'));
        $this->assertEquals(204, $this->client->delete('testing/file-1.jpg'));
    }

    public function testWrite(): void
    {
        $this->assertTrue($this->client->write('Hello World', 'text.txt'));
        $this->assertEquals(204, $this->client->delete('text.txt'));
    }

    public function testDownload(): void
    {
        $this->assertTrue($this->client->write('Hello World', 'text-read.txt'));
        $this->assertEquals('Hello World', $this->client->download('text-read.txt'));
        $this->assertEquals(204, $this->client->delete('text-read.txt'));
    }

    public function testFileExists(): void
    {
        $this->assertTrue($this->client->write('Hello World', 'text-exist.txt'));
        $this->assertTrue($this->client->exists('text-exist.txt'));
        $this->assertFalse($this->client->exists('testing/file-7.jpg'));
        $this->assertEquals(204, $this->client->delete('text-exist.txt'));
    }

    public function testDelete(): void
    {
        $this->assertTrue($this->client->write('Hello World', 'text-delete.txt'));
        $this->assertTrue($this->client->exists('text-delete.txt'));
        $this->assertEquals(204, $this->client->delete('text-delete.txt'));
    }

    public function testFileSize(): void
    {
        $this->assertTrue($this->client->upload(__DIR__.'/../resources/file-1.jpg', 'testing/file-16.jpg'));
        $this->assertEquals(77931, $this->client->getSize('testing/file-16.jpg'));
        $this->assertEquals(204, $this->client->delete('testing/file-16.jpg'));
    }

    public function testFileMimeType(): void
    {
        $this->assertTrue($this->client->upload(__DIR__.'/../resources/file-1.jpg', 'testing/file-19.jpg'));
        $this->assertEquals('image/jpeg', $this->client->getMimeType('testing/file-19.jpg'));
        $this->assertEquals(204, $this->client->delete('testing/file-19.jpg'));
    }
}
