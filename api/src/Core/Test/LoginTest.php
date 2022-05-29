<?php

namespace App\Core\Test;

use App\Core\Entity\UserEntity;
use Doctrine\ORM\Tools\SchemaTool;
use Package\MediaBundle\Entity\Media;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class LoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        $this->assertIsInt(2);
        $client = static::createClient();
        $this->createDB($client->getKernel());

        $em = $client->getContainer()->get('doctrine')->getManager();
        $userRepo = $em->getRepository(UserEntity::class);
        $mediaRepo = $em->getRepository(Media::class);

        $media = (new Media())
            ->setPath('/a/b/c/asd')
            ->setData(['asd', 'dddd'])
            ->setApproved(true)
            ->setMime('jpg')
            ->setSize(200);

        $media2 = (new Media())
            ->setPath('/a/b/c/asd2')
            ->setData(['asd2', 'dddd2'])
            ->setApproved(true)
            ->setMime('png')
            ->setSize(300);

        $em->persist($media);
        $em->persist($media2);
        $em->flush();

        $user = (new UserEntity())
            ->setName('Tamam')
            ->addMedia($media);
            //->addImage($media);
        $em->persist($user);
        $em->flush();


        $em->clear();
        $sd = $userRepo->find(1);

        dump(
            $sd->getMedia()
        );

        $data = $client->getContainer()->get('doctrine')->getConnection()
            ->executeQuery('select * from user_entity')->fetchAllAssociative();
        //dump($data);
    }

    public function createDB(KernelInterface $kernel)
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
