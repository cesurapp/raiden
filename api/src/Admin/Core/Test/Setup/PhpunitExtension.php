<?php

namespace App\Admin\Core\Test\Setup;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Runner\AfterLastTestHook;
use PHPUnit\Runner\BeforeFirstTestHook;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PhpunitExtension extends KernelTestCase implements AfterLastTestHook, BeforeFirstTestHook
{
    public function executeBeforeFirstTest(): void
    {
        $this->initDB();
    }

    public function executeAfterLastTest(): void
    {
        $this->initDB(true);
    }

    /**
     * Initialize DB.
     */
    public function initDB(bool $drop = false): void
    {
        static::bootKernel();

        if ('test' !== self::$kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $manager = static::getContainer()->get('doctrine')->getManager();
        $connection = $manager->getConnection();
        $config = $connection->getParams();
        $dbName = $config['dbname'];

        // Create DB
        unset($config['dbname'], $config['url']);
        $tmpCon = DriverManager::getConnection($config);

        // Update Schema
        $metaData = $manager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($manager);
        if ($drop) {
            $schemaTool->dropDatabase();
            return;
        }

        $tmpCon->createSchemaManager()->dropDatabase($dbName);
        $tmpCon->createSchemaManager()->createDatabase($dbName);
        $schemaTool->updateSchema($metaData);

        static::ensureKernelShutdown();
    }
}
