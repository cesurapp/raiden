<?php

namespace Package\MediaBundle\Test;

use Doctrine\ORM\Tools\SchemaTool;
use Package\MediaBundle\Manager\MediaManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

use function Co\run;

class ManagerTest extends KernelTestCase
{
    public function testUpload(): void
    {
        // Create Request
        $request = new Request();
        $file = new UploadedFile(__DIR__.'/resources/image.png', 'image.png');
        $file2 = new UploadedFile(__DIR__.'/resources/image.png', 'image.jpg');
        $request->files->add([
            'fff' => [$file, $file2],
        ]);
        $request->request->add(
            [
            'files' => [
                'imageBase64' => 'iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAYAAADgdz34AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAApgAAAKYB3X3/OAAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAANCSURBVEiJtZZPbBtFFMZ/M7ubXdtdb1xSFyeilBapySVU8h8OoFaooFSqiihIVIpQBKci6KEg9Q6H9kovIHoCIVQJJCKE1ENFjnAgcaSGC6rEnxBwA04Tx43t2FnvDAfjkNibxgHxnWb2e/u992bee7tCa00YFsffekFY+nUzFtjW0LrvjRXrCDIAaPLlW0nHL0SsZtVoaF98mLrx3pdhOqLtYPHChahZcYYO7KvPFxvRl5XPp1sN3adWiD1ZAqD6XYK1b/dvE5IWryTt2udLFedwc1+9kLp+vbbpoDh+6TklxBeAi9TL0taeWpdmZzQDry0AcO+jQ12RyohqqoYoo8RDwJrU+qXkjWtfi8Xxt58BdQuwQs9qC/afLwCw8tnQbqYAPsgxE1S6F3EAIXux2oQFKm0ihMsOF71dHYx+f3NND68ghCu1YIoePPQN1pGRABkJ6Bus96CutRZMydTl+TvuiRW1m3n0eDl0vRPcEysqdXn+jsQPsrHMquGeXEaY4Yk4wxWcY5V/9scqOMOVUFthatyTy8QyqwZ+kDURKoMWxNKr2EeqVKcTNOajqKoBgOE28U4tdQl5p5bwCw7BWquaZSzAPlwjlithJtp3pTImSqQRrb2Z8PHGigD4RZuNX6JYj6wj7O4TFLbCO/Mn/m8R+h6rYSUb3ekokRY6f/YukArN979jcW+V/S8g0eT/N3VN3kTqWbQ428m9/8k0P/1aIhF36PccEl6EhOcAUCrXKZXXWS3XKd2vc/TRBG9O5ELC17MmWubD2nKhUKZa26Ba2+D3P+4/MNCFwg59oWVeYhkzgN/JDR8deKBoD7Y+ljEjGZ0sosXVTvbc6RHirr2reNy1OXd6pJsQ+gqjk8VWFYmHrwBzW/n+uMPFiRwHB2I7ih8ciHFxIkd/3Omk5tCDV1t+2nNu5sxxpDFNx+huNhVT3/zMDz8usXC3ddaHBj1GHj/As08fwTS7Kt1HBTmyN29vdwAw+/wbwLVOJ3uAD1wi/dUH7Qei66PfyuRj4Ik9is+hglfbkbfR3cnZm7chlUWLdwmprtCohX4HUtlOcQjLYCu+fzGJH2QRKvP3UNz8bWk1qMxjGTOMThZ3kvgLI5AzFfo379UAAAAASUVORK5CYII=',
            ],
            ]
        );

        $request->request->add([
            'filesLink' => [
                'https://www.google.com.tr/images/branding/googlelogo/1x/googlelogo_light_color_272x92dp.png',
                'https://www.google.com.tr/images/branding/googlelogo/1x/googlelogo_light_color_272x92dp.png'
            ],
        ]);

        $container = static::bootKernel()->getContainer();
        $this->initDatabase(self::$kernel);

        /** @var MediaManager $manager */
        $manager = $container->get(MediaManager::class);
        $em = $container->get('doctrine')->getManager();

        run(function () use ($manager, $request){
            $medias = $manager->uploadLink($request, ['filesLink' => []]);
        });

        // Insert
      /*
        $user = new UserEntity();
        $user->setName('nalet');
        $user->setMedia($medias);
        $em->persist($user);
        $em->flush();

        // Delete
        $user->removeMedia($medias[1]);
        $em->persist($user);
        $em->flush();

        $medias[0]->incrCounter();
        $user2 = new UserEntity();
        $user2->setName('nalet2');
        $user2->addMedia($medias[0]);
        $em->persist($user2);
        $em->flush();

        // Remove User
        $em->remove($user2);
        //$em->remove($user);
        $em->flush();

        dump($em->getRepository(Media::class)->findAll());*/
    }

    public function testMediaEntity(): void
    {
    }

    public function testUploadFile(): void
    {
    }

    public function testUploadBase64(): void
    {
    }

    public function testUploadLink(): void
    {
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
