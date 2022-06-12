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

    /**
     * Validate & Decoded Array.
     */
    public function json(?string $content = null, ?string $key = null): string|array
    {
        try {
            $decodedResponse = json_decode(
                $content ?? $this->client()->getResponse()->getContent(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Exception $e) {
            $this->fail('Invalid JSON was returned from the route.');
        }

        return $key ? $decodedResponse[$key] : $decodedResponse;
    }

    /**
     * Assert that the response has the exact given JSON.
     */
    public function assertExactJson(array $data, ?array $content = null): self
    {
        $this->assertEquals(
            json_encode($data, JSON_THROW_ON_ERROR),
            json_encode($content ?? $this->json(), JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     */
    public function assertJsonCount(int $count, string $key = null, ?array $content = null): self
    {
        $this->assertCount($count, $key ? $this->json(key: $key) : $this->json(), "Failed to assert that the response count matched the expected {$count}");

        return $this;
    }

    /**
     * Assert that the response has a given JSON structure.
     */
    public function assertJsonStructure(array $structure, array $responseData = null): self
    {
        if (is_null($responseData)) {
            $responseData = $this->json();
        }

        foreach ($structure as $key => $value) {
            if (is_array($value) && '*' === $key) {
                $this->assertIsArray($responseData);

                foreach ($responseData as $responseDataItem) {
                    $this->assertJsonStructure($structure['*'], $responseDataItem);
                }
            } elseif (is_array($value)) {
                $this->assertArrayHasKey($key, $responseData);
                $this->assertJsonStructure($value, $responseData[$key]);
            } else {
                $this->assertArrayHasKey($value, $responseData);
            }
        }

        return $this;
    }

    /**
     * Create Application User.
     */
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

        // Hash Password
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

    /**
     * Get HTTP Client.
     */
    public function client(): KernelBrowser
    {
        return $this->client;
    }

    /**
     * Get Entity Manager.
     */
    public function manager(): EntityManagerInterface
    {
        if (!$this->manager) {
            $this->manager = self::getContainer()->get('doctrine')->getManager();
        }

        return $this->manager;
    }

    /**
     * Initialize DB.
     */
    public function initDB(): void
    {
        if ('test' !== self::$kernel->getEnvironment()) {
            throw new \LogicException('Execution only in Test environment possible!');
        }

        $con = $this->manager()->getConnection();
        $name = $con->getParams()['dbname'];

        // Create DB
        $tmpCon = DriverManager::getConnection($con->getParams());
        if (!in_array($name, $tmpCon->createSchemaManager()->listDatabases(), true)) {
            $tmpCon->createSchemaManager()->createDatabase($name);
        }

        // Update Schema
        $metaData = $this->manager()->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->manager());
        $schemaTool->dropSchema($metaData);
        $schemaTool->updateSchema($metaData);
    }
}
