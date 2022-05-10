<?php

namespace Package\Library\Test;

use Package\Library\Image;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageTest extends WebTestCase
{
    public function testCrop(): void
    {
        // JPG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.jpg'));
        $image->crop(100, 100);
        $geometry = $image->image->getImageGeometry();
        $this->assertEquals(100, $geometry['width']);
        $this->assertEquals(100, $geometry['height']);

        // PNG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.png'));
        $image->crop(100, 100);
        $geometry = $image->image->getImageGeometry();
        $this->assertEquals(100, $geometry['width']);
        $this->assertEquals(100, $geometry['height']);
    }

    public function testCompress(): void
    {
        // JPG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.jpg'));
        $image->save(sys_get_temp_dir().'/image.jpg', 'jpg');
        $this->assertEquals(322695, filesize(sys_get_temp_dir().'/image.jpg'));
        unlink(sys_get_temp_dir().'/image.jpg');

        // PNG to JPG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.png'));
        $image->save(sys_get_temp_dir().'/image.jpg', 'jpg');
        $this->assertEquals(58058, filesize(sys_get_temp_dir().'/image.jpg'));
        unlink(sys_get_temp_dir().'/image.jpg');
    }

    public function testResize(): void
    {
        // JPG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.jpg'));
        $image->resize(100, 100);
        $geometry = $image->image->getImageGeometry();
        $this->assertEquals(96, $geometry['width']);
        $this->assertEquals(100, $geometry['height']);
        $this->assertEquals(3829, strlen($image->image->getImageBlob()));

        // PNG
        $image = new Image(file_get_contents(__DIR__.'/resources/image.png'));
        $image->resize(100, 100);
        $geometry = $image->image->getImageGeometry();
        $this->assertEquals(100, $geometry['width']);
        $this->assertEquals(67, $geometry['height']);
        $this->assertEquals(9910, strlen($image->image->getImageBlob()));
    }
}
