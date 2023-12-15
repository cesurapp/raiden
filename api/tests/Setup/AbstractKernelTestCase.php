<?php

namespace App\Tests\Setup;

use App\Admin\Core\Entity\Organization;
use App\Admin\Core\Entity\User;
use App\Admin\Core\Enum\UserType;
use App\Admin\Core\Permission\PermissionInterface;
use Cesurapp\SwooleBundle\Task\TaskWorker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Uid\Ulid;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    protected static null|KernelBrowser $client = null;

    /**
     * Get Entity Manager.
     */
    public function manager(): EntityManagerInterface
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * Save Object.
     */
    public function save(object $object): object
    {
        $this->manager()->persist($object);
        $this->manager()->flush();

        return $object;
    }

    /**
     * Validate & Decoded Array.
     */
    public function json(string $content = null, string $key = null): string|array
    {
        try {
            $decodedResponse = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            $this->fail('Invalid JSON was returned from the route.');
        }

        return $key ? $decodedResponse[$key] : $decodedResponse;
    }

    /**
     * Assert that the response has the exact given JSON.
     */
    public function assertExactJson(array $data, array $content = null): self
    {
        if (!$content) {
            $content = $this->json();
        }

        $this->assertEquals(json_encode($data, JSON_THROW_ON_ERROR), json_encode($content, JSON_THROW_ON_ERROR));

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     */
    public function assertJsonCount(int $count, string $key = null, array $content = null): self
    {
        if (!$content) {
            $content = $this->json();
        }

        $this->assertCount(
            $count,
            $key ? $content[$key] : $content,
            "Failed to assert that the response count matched the expected {$count}"
        );

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
     * Test Console Command.
     */
    public function commandTester(string $command, array $inputs = []): CommandTester
    {
        if (!static::$booted) {
            self::bootKernel();
        }

        $cmd = (new Application(static::$kernel))->find($command);
        $tester = new CommandTester($cmd);
        $tester->setInputs($inputs);
        $tester->execute(['command' => $cmd->getName()]);
        $tester->assertCommandIsSuccessful();

        return $tester;
    }

    /**
     * Create App User.
     */
    public function createUser(bool $createOrganization = false, string $password = '123123123'): User
    {
        $user = $this->getUser($createOrganization, $password);
        $this->manager()->persist($user);
        $this->manager()->flush();

        return $user;
    }

    public function createAdmin(
        PermissionInterface $permission = null,
        bool $createOrganization = false,
        string $password = '123123123'
    ): User {
        $user = $this->getUser($createOrganization, $password);
        $user->setType(UserType::ADMIN);
        if ($permission) {
            $user->addRoles($permission);
        }

        $this->manager()->persist($user);
        $this->manager()->flush();

        return $user;
    }

    public function getUser(bool $createOrganization = false, string $password = '123123123'): User
    {
        return (new User())
            ->setOrganization($createOrganization ? (new Organization())->setName('Test Org') : null)
            ->setLanguage('tr')
            ->setEmailApproved(true)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone(random_int(905400000000, 905499999900))
            ->setPhoneCountry('TR')
            ->setPhoneApproved(true)
            ->setEmail(Ulid::generate().'@test.com')
            ->setEmailApproved(true)
            ->setPassword($password, self::getContainer()->get('security.user_password_hasher'));
    }

    public function mockTaskWorker(callable $returnCallback): TaskWorker|MockObject
    {
        $worker = $this->getMockBuilder(TaskWorker::class)
            ->disableOriginalConstructor()
            ->getMock();

        $worker->method('handle')->willReturnCallback($returnCallback);

        if (isset(self::$client)) {
            self::$client->disableReboot();
        }

        self::getContainer()->set(TaskWorker::class, $worker);

        return $worker;
    }
}