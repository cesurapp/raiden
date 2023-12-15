<?php

namespace App\Tests\Setup;

use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhpunitExtension extends KernelTestCase implements BeforeFirstTestHook
{
    /**
     * Initialize DB.
     */
    public function executeBeforeFirstTest(): void
    {
        static::bootKernel();

        if ('test' !== self::$kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $entityManager = self::getContainer()->get('doctrine')->getManager();
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->updateSchema($metaData);

        static::ensureKernelShutdown();
    }
}
