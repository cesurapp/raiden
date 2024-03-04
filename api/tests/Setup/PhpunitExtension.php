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
        $this->createDbIfNotExist($entityManager);
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->updateSchema($metaData);

        static::ensureKernelShutdown();
    }

    private function createDbIfNotExist($entityManager): void
    {
        $conn = $entityManager->getConnection();
        $config = $conn->getParams();
        $dbName = $config['dbname'];
        unset($config['dbname'], $config['path'], $config['url']);
        $tmpCon = DriverManager::getConnection($config, $conn->getConfiguration());
        $schemaManager = $tmpCon->createSchemaManager();
        if (!in_array($dbName, $schemaManager->listDatabases())) {
            $schemaManager->createDatabase($dbName);
        }
    }
}
