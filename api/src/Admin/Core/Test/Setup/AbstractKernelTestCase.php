<?php

namespace App\Admin\Core\Test\Setup;

use App\Admin\Core\Entity\Organization;
use App\Admin\Core\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Uid\Ulid;

abstract class AbstractKernelTestCase extends KernelTestCase
{
    private ?EntityManagerInterface $manager = null;

    /**
     * Validate & Decoded Array.
     */
    public function json(?string $content = null, ?string $key = null): string|array
    {
        try {
            $decodedResponse = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
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
        if (!$content) {
            $content = $this->json();
        }

        $this->assertEquals(
            json_encode($data, JSON_THROW_ON_ERROR),
            json_encode($content, JSON_THROW_ON_ERROR)
        );

        return $this;
    }

    /**
     * Assert that the response JSON has the expected count of items at the given key.
     */
    public function assertJsonCount(int $count, string $key = null, ?array $content = null): self
    {
        if (!$content) {
            $content = $this->json();
        }

        $this->assertCount($count, $key ? $content[$key] : $content, "Failed to assert that the response count matched the expected {$count}");

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
     * Check Event is Fired.
     */
    public function assertEventFired(string $eventName): void
    {
        $dispatcher = static::getContainer()->get('event_dispatcher');

        $called = array_merge(
            array_column($dispatcher->getCalledListeners(), 'event'),
            $dispatcher->getOrphanedEvents()
        );

        $this->assertContains($eventName, $called);
    }

    /**
     * Test Console Command.
     */
    public function cmdTester(string $command, array $inputs = []): CommandTester
    {
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
        $user = (new User())
            ->setOrganization($createOrganization ? (new Organization())->setName('Test Org') : null)
            ->setLanguage('TR')
            ->setEmailApproved(true)
            ->setFirstName('John')
            ->setLastName('Doe')
            ->setPhone(random_int(905400000000, 905499999900))
            ->setPhoneCountry('TR')
            ->setPhoneApproved(true)
            ->setEmail(Ulid::generate().'@test.com')
            ->setEmailApproved(true);

        // Hash Password
        $passwordHasherFactory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);
        $user->setPassword((new UserPasswordHasher($passwordHasherFactory))->hashPassword($user, $password));

        $this->manager()->persist($user);
        $this->manager()->flush();

        return $user;
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
     * Save Object.
     */
    public function save(object $object): object
    {
        $this->manager()->persist($object);
        $this->manager()->flush();

        return $object;
    }
}
