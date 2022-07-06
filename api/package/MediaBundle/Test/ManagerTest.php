<?php

namespace Package\MediaBundle\Test;

use Doctrine\ORM\Tools\SchemaTool;
use Package\MediaBundle\Entity\Media;
use Package\MediaBundle\Manager\MediaManager;
use Package\StorageBundle\Storage\Storage;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ManagerTest extends KernelTestCase
{
    protected function setUp(): void
    {
        static::bootKernel();
        $this->initDatabase(self::$kernel);
    }

    public function testUploadFile(): void
    {
        $request = new Request();
        $request->files->add([
            new UploadedFile(__DIR__.'/resources/image.png', 'image.png'),
            new UploadedFile(__DIR__.'/resources/image.png', 'image.jpg'),
            'subKey' => [new UploadedFile(__DIR__.'/resources/image.png', 'image.png')],
        ]);

        $manager = self::getContainer()->get(MediaManager::class);
        $storage = self::getContainer()->get(Storage::class);
        $em = self::getContainer()->get('doctrine')->getManager();

        // Upload All
        $medias = $manager->uploadFile($request);

        $this->assertInstanceOf(Media::class, $medias[0]);
        $this->assertInstanceOf(Media::class, $medias[1]);
        $this->assertInstanceOf(Media::class, $medias['subKey'][0]);
        $this->assertTrue($storage->device($medias[0]->getStorage())->exists($medias[0]->getPath()));

        array_walk_recursive($medias, static function ($media) use ($em) {
            $em->remove($media);
        });
        $em->flush();

        $this->assertFalse($storage->device($medias[0]->getStorage())->exists($medias[0]->getPath()));
        $this->assertFalse($storage->device($medias[0]->getStorage())->exists($medias[1]->getPath()));
    }

    public function testUploadBase64(): void
    {
        $request = new Request();
        $request->request->add([
            'imageBase64' => 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAANCSURBVEiJtZZPbBtFFMZ/M7ubXdtdb1xSFyeilBapySVU8h8OoFaooFSqiihIVIpQBKci6KEg9Q6H9kovIHoCIVQJJCKE1ENFjnAgcaSGC6rEnxBwA04Tx43t2FnvDAfjkNibxgHxnWb2e/u992bee7tCa00YFsffekFY+nUzFtjW0LrvjRXrCDIAaPLlW0nHL0SsZtVoaF98mLrx3pdhOqLtYPHChahZcYYO7KvPFxvRl5XPp1sN3adWiD1ZAqD6XYK1b/dvE5IWryTt2udLFedwc1+9kLp+vbbpoDh+6TklxBeAi9TL0taeWpdmZzQDry0AcO+jQ12RyohqqoYoo8RDwJrU+qXkjWtfi8Xxt58BdQuwQs9qC/afLwCw8tnQbqYAPsgxE1S6F3EAIXux2oQFKm0ihMsOF71dHYx+f3NND68ghCu1YIoePPQN1pGRABkJ6Bus96CutRZMydTl+TvuiRW1m3n0eDl0vRPcEysqdXn+jsQPsrHMquGeXEaY4Yk4wxWcY5V/9scqOMOVUFthatyTy8QyqwZ+kDURKoMWxNKr2EeqVKcTNOajqKoBgOE28U4tdQl5p5bwCw7BWquaZSzAPlwjlithJtp3pTImSqQRrb2Z8PHGigD4RZuNX6JYj6wj7O4TFLbCO/Mn/m8R+h6rYSUb3ekokRY6f/YukArN979jcW+V/S8g0eT/N3VN3kTqWbQ428m9/8k0P/1aIhF36PccEl6EhOcAUCrXKZXXWS3XKd2vc/TRBG9O5ELC17MmWubD2nKhUKZa26Ba2+D3P+4/MNCFwg59oWVeYhkzgN/JDR8deKBoD7Y+ljEjGZ0sosXVTvbc6RHirr2reNy1OXd6pJsQ+gqjk8VWFYmHrwBzW/n+uMPFiRwHB2I7ih8ciHFxIkd/3Omk5tCDV1t+2nNu5sxxpDFNx+huNhVT3/zMDz8usXC3ddaHBj1GHj/As08fwTS7Kt1HBTmyN29vdwAw+/wbwLVOJ3uAD1wi/dUH7Qei66PfyuRj4Ik9is+hglfbkbfR3cnZm7chlUWLdwmprtCohX4HUtlOcQjLYCu+fzGJH2QRKvP3UNz8bWk1qMxjGTOMThZ3kvgLI5AzFfo379UAAAAASUVORK5CYII=',
        ]);

        $manager = self::getContainer()->get(MediaManager::class);
        $storage = self::getContainer()->get(Storage::class);
        $em = self::getContainer()->get('doctrine')->getManager();

        // Upload All
        $medias = $manager->uploadBase64($request, ['imageBase64' => '']);

        $this->assertInstanceOf(Media::class, $medias['imageBase64']);
        $this->assertTrue(
            $storage->device($medias['imageBase64']->getStorage())->exists($medias['imageBase64']->getPath())
        );

        array_walk_recursive($medias, static function ($media) use ($em) {
            $em->remove($media);
        });
        $em->flush();

        $this->assertFalse(
            $storage->device($medias['imageBase64']->getStorage())->exists($medias['imageBase64']->getPath())
        );
    }

    public function testUploadLink(): void
    {
        $request = new Request();
        $request->request->add([
            'filesLink' => 'https://www.google.com.tr/images/branding/googlelogo/1x/googlelogo_light_color_272x92dp.png',
        ]);

        $manager = self::getContainer()->get(MediaManager::class);
        $storage = self::getContainer()->get(Storage::class);
        $em = self::getContainer()->get('doctrine')->getManager();

        // Upload All
        $medias = $manager->uploadLink($request, ['filesLink' => '']);

        $this->assertInstanceOf(Media::class, $medias['filesLink']);
        $this->assertTrue($storage->device($medias['filesLink']->getStorage())->exists($medias['filesLink']->getPath()));

        array_walk_recursive($medias, static function ($media) use ($em) {
            $em->remove($media);
        });
        $em->flush();

        $this->assertFalse($storage->device($medias['filesLink']->getStorage())->exists($medias['filesLink']->getPath()));
    }

    private function initDatabase(KernelInterface $kernel): void
    {
        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);
    }
}
