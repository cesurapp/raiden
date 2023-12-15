<?php

namespace App\Tests\Setup;

use Ahc\Jwt\JWT;
use App\Admin\Core\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestAssertionsTrait;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

abstract class AbstractWebTestCase extends AbstractKernelTestCase
{
    use WebTestAssertionsTrait;

    protected function tearDown(): void
    {
        parent::tearDown();
        self::getClient(null);
    }

    /**
     * Creates a KernelBrowser.
     *
     * @param array $options An array of options to pass to the createKernel method
     * @param array $server  An array of server parameters
     */
    protected static function createClient(array $options = [], array $server = []): ?AbstractBrowser
    {
        if (static::$booted) {
            throw new \LogicException(sprintf('Booting the kernel before calling "%s()" is not supported, the kernel should only be booted once.', __METHOD__));
        }

        $kernel = static::bootKernel($options);

        try {
            $client = $kernel->getContainer()->get('test.client');
        } catch (ServiceNotFoundException) {
            if (class_exists(KernelBrowser::class)) {
                throw new \LogicException('You cannot create the client used in functional tests if the "framework.test" config is not set to true.');
            }
            throw new \LogicException('You cannot create the client used in functional tests if the BrowserKit component is not available. Try running "composer require symfony/browser-kit".');
        }

        $client->setServerParameters($server);

        return self::$client = self::getClient($client); // @phpstan-ignore-line
    }

    /**
     * HTTP Client JSON Decode Content.
     */
    public function json(string $content = null, string $key = null): string|array
    {
        if ($content) {
            parent::json($content, $key);
        }

        $data = json_decode($this->client()->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $key ? $data[$key] : $data;
    }

    /**
     * Get HTTP Client.
     */
    public function client(User $user = null): KernelBrowser
    {
        if (!self::$client) {
            static::createClient();
        }

        if ($user) {
            $token = static::getContainer()->get(JWT::class)->encode(['id' => $user->getId()->toBase32()]);
            self::$client->setServerParameter('HTTP_AUTHORIZATION', 'Bearer '.$token);
        }

        return self::$client;
    }

    public function assertStatus(int $code): void
    {
        $this->assertEquals($code, $this->client()->getResponse()->getStatusCode());
    }

    public function isFail(): void
    {
        $this->assertGreaterThanOrEqual(400, $this->client()->getResponse()->getStatusCode());
    }

    public function isOk(): void
    {
        $this->assertStatus(200);
    }

    public function isForbidden(): void
    {
        $this->assertStatus(403);
    }

    public function isNotFound(): void
    {
        $this->assertStatus(404);
    }

    public function isValidationException(): void
    {
        $this->assertStatus(422);
    }
}
