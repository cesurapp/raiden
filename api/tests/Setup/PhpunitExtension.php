<?php

namespace App\Tests\Setup;

use PHPUnit\Runner\Extension\Extension as ExtensionInterface;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Event\TestSuite\Started;
use PHPUnit\Event\TestSuite\StartedSubscriber;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Tools\SchemaTool;
use App\Kernel;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class PhpunitExtension implements ExtensionInterface
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new DatabaseSetupSubscriber());
    }
}

final class DatabaseSetupSubscriber implements StartedSubscriber
{
    public function notify(Started $event): void
    {
        require_once dirname(__DIR__, 2).'/tests/bootstrap.php';

        $kernel = new Kernel('test', true);
        $kernel->boot();

        $container = $kernel->getContainer();

        if ('test' !== $kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $em = $container->get('doctrine')->getManager();
        $conn = $em->getConnection();

        // Create DB if not exists
        $config = array_diff_key($conn->getParams(), array_flip(['dbname', 'path', 'url']));
        if ($conn->getDatabasePlatform() instanceof PostgreSQLPlatform) {
            $config['dbname'] = 'postgres';
        }
        $newConn = DriverManager::getConnection($config, $conn->getConfiguration());
        $schemaManager = $newConn->createSchemaManager();
        if (!in_array($conn->getParams()['dbname'], $schemaManager->listDatabases(), true)) {
            $schemaManager->createDatabase($conn->getParams()['dbname']);
        }

        // Refresh DB
        $schemaTool = new SchemaTool($em);
        $schemaTool->dropDatabase();
        $schemaTool->updateSchema($em->getMetadataFactory()->getAllMetadata());

        $kernel->shutdown();
    }
}
