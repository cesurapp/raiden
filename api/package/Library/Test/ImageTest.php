<?php

namespace Package\Library\Test;

use Package\Library\Image;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImageTest extends WebTestCase
{
    public function testResize(): void
    {
        $imageRoot = __DIR__.'/../../StorageBundle/Test/resources/disk-a/kitten-1.jpg';

        $image = new Image(file_get_contents($imageRoot));
        $image->resize(100, 100);
        $image->save(sys_get_temp_dir().'/testfile.jpg', 'jpg');

        $this->assertEquals(2236, filesize(sys_get_temp_dir().'/testfile.jpg'));
    }
}
