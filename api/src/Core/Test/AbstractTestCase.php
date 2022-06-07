<?php

namespace App\Core\Test;

use App\Core\Entity\Organization;
use App\Core\Entity\User;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Ulid;

abstract class AbstractTestCase extends WebTestCase
{
    private KernelBrowser $client;
    private ?EntityManagerInterface $manager = null;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->initDB();
    }

    public function createUser(bool $login = true, bool $createOrganization = true, string $password = '123123'): User
    {
        $user = (new User())
            ->setOrganization(!$createOrganization ?: (new Organization())->setName('Test Org'))
            ->setLanguage('TR')
            ->setApproved(true)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone((string) random_int(5400000000, 6400000000))
            ->setPassword('123123')
            ->setEmail(Ulid::generate().'@test.com');

        $passwordHasherFactory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);
        $user->setPassword((new UserPasswordHasher($passwordHasherFactory))->hashPassword($user, $password));

        $this->manager->persist($user);
        $this->manager->flush();

        if ($login) {
            $this->client->loginUser($user);
        }

        return $user;
    }

    public function getClient(): KernelBrowser
    {
        return $this->client;
    }

    public function getManager(): EntityManagerInterface
    {
        if (!$this->manager) {
            $this->manager = self::getContainer()->get('doctrine')->getManager();
        }

        return $this->manager;
    }

    public function initDB(): void
    {
        if ('test' !== self::$kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $con = $this->getManager()->getConnection();
        $name = $con->getParams()['dbname'];

        // Create DB
        $tmpCon = DriverManager::getConnection($con->getParams());
        if (!in_array($name, $tmpCon->createSchemaManager()->listDatabases(), true)) {
            $tmpCon->createSchemaManager()->createDatabase($name);
        }

        // Update Schema
        $metaData = $this->getManager()->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->getManager());
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}
