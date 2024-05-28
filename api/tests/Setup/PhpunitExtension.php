<?php

namespace App\Tests\Setup;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
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

        $em = self::getContainer()->get('doctrine')->getManager();
        $conn = $em->getConnection();

        // Create DB Not Found
        $config = array_diff_key($conn->getParams(), array_flip(['dbname', 'path', 'url']));
        if ($conn->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $config['dbname'] = 'postgres';
        }
        $newConn = DriverManager::getConnection($config, $conn->getConfiguration());
        $schemaManager = $newConn->createSchemaManager();
        if (!in_array($conn->getParams()['dbname'], $schemaManager->listDatabases(), true)) {
            $schemaManager->createDatabase($conn->getParams()['dbname']);

            // Create Extension
            // $newConn->executeStatement('CREATE EXTENSION IF NOT EXISTS postgis;');
            // $newConn->executeStatement('DROP EXTENSION IF EXISTS postgis_tiger_geocoder;');
            // $newConn->executeStatement('DROP EXTENSION IF EXISTS postgis_topology;');
        }

        // Refresh DB
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata());

        static::ensureKernelShutdown();
    }
}
