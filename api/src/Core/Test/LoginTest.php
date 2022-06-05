<?php

namespace App\Core\Test;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

class LoginTest extends WebTestCase
{
    public function testLogin(): void
    {
        go(function () {
            echo 'Test Coroutine in PhpUnit';
        });
        $this->assertTrue(true, true);
    }

    public function createDB(KernelInterface $kernel)
    {
        /*$entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema($metaData);*/
    }
}
