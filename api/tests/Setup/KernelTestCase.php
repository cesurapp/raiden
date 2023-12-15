<?php

namespace App\Tests\Setup;

use Cesurapp\SwooleBundle\Task\TaskWorker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class KernelTestCase extends BaseKernelTestCase
{
    use AppTrait;

    public static AbstractBrowser|KernelBrowser|null $client = null;
    public static Response|null $response = null;

    /**
     * Request Client or Kernel Handle.
     */
    public function request(string $method, string $uri, array $parameters = [], array $files = [], array $server = [], string $content = null, bool $changeHistory = true): self
    {
        if (static::$client) {
            static::$client->request($method, $uri, $parameters, $files, [...$server, ...$this->server], $content, $changeHistory);
        } else {
            static::$response = static::$kernel->handle(Request::create($uri, $method, $parameters, [], $files, [...$server, ...$this->server], $content));
        }

        // Clear Login
        $this->server = [];

        return $this;
    }

    public function jsonRequest(string $method, string $uri, array $parameters = [], array $server = [], bool $changeHistory = true): self
    {
        try {
            if (static::$client) {
                static::$client->request($method, $uri, [], [], [...$server, ...$this->server, ...[
                    'CONTENT_TYPE' => 'application/json',
                    'HTTP_ACCEPT' => 'application/json',
                ]], json_encode($parameters, JSON_THROW_ON_ERROR), $changeHistory);
            } else {
                static::$response = static::$kernel->handle(
                    Request::create($uri, $method, [], [], [], [...$server, ...$this->server, ...[
                        'CONTENT_TYPE' => 'application/json',
                        'HTTP_ACCEPT' => 'application/json',
                    ]], json_encode($parameters, JSON_THROW_ON_ERROR))
                );
            }
        } catch (\Exception) {
        }

        // Clear Login
        $this->server = [];

        return $this;
    }

    /**
     * Dumping.
     */
    public function dd(): void
    {
        if (static::$client) {
            dd(static::$client->getResponses());
        } else {
            dd(static::$response);
        }
    }

    public function ddContent(): void
    {
        dd($this->getContent());
    }

    public function ddJson(): void
    {
        dd($this->getJson());
    }

    /**
     * Contents.
     */
    public function getResponses(): Response
    {
        if (static::$client) {
            return static::$client->getResponse();
        }

        return static::$response;
    }

    public function getContent(): string|false
    {
        if (static::$client) {
            return static::$client->getResponse()->getContent();
        }

        if (static::$response instanceof StreamedResponse || static::$response instanceof BinaryFileResponse) {
            ob_start();
            static::$response->sendContent();

            return ob_get_clean();
        }

        return static::$response->getContent();
    }

    public function getJson(string $key = null, mixed $default = null): mixed
    {
        $data = json_decode($this->getContent(), true, 512, JSON_THROW_ON_ERROR);

        return $key ? $this->getArrayDot($data, $key, $default) : $data;
    }

    public function getHeader(string $key = null): mixed
    {
        return $this->getArrayDot($this->getResponses()->headers->all(), $key);
    }

    public function getArrayDot(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }
        if (!str_contains($key, '.')) {
            return $array[$key] ?? $default;
        }
        $result = $array;
        foreach (explode('.', $key) as $segment) {
            if (is_array($result) && array_key_exists($segment, $result)) {
                $result = $result[$segment];
            } else {
                return $default;
            }
        }

        return $result;
    }

    /**
     * Asserting.
     */
    public function isStatus(int $code): self
    {
        $this->assertEquals($code, $this->getResponses()->getStatusCode());

        return $this;
    }

    public function isOk(): self
    {
        $this->assertTrue($this->getResponses()->isOk());

        return $this;
    }

    public function isFail(): self
    {
        $this->assertTrue($this->getResponses()->isClientError());

        return $this;
    }

    public function isForbidden(): self
    {
        $this->assertTrue($this->getResponses()->isForbidden());

        return $this;
    }

    public function isNotFound(): self
    {
        $this->assertTrue($this->getResponses()->isNotFound());

        return $this;
    }

    public function isServerError(): self
    {
        $this->assertTrue($this->getResponses()->isServerError());

        return $this;
    }

    public function isRedirection(): self
    {
        $this->assertTrue($this->getResponses()->isRedirection());

        return $this;
    }

    public function isValidationException(): self
    {
        $this->isStatus(422);

        return $this;
    }

    public function isEquals(mixed $content, string $key = null, mixed $data = null): self
    {
        $data ??= $this->getJson($key);

        $this->assertEquals($data, $content);

        return $this;
    }

    public function isContains(string $needle, string $haystack = null): self
    {
        $haystack ??= $this->getContent();

        $this->assertStringContainsString($needle, $haystack);

        return $this;
    }

    public function isHeaderEquals(string $key = null, mixed $data = null, mixed $header = null): self
    {
        $header ??= $this->getHeader($key);

        $this->assertEquals($data, $header);

        return $this;
    }

    public function isJsonCount(int $count, string $key = null, array $data = null): self
    {
        $data ??= $this->getJson($key, []);

        $this->assertCount($count, $data, "Failed to assert that the response count matched the expected {$count}");

        return $this;
    }

    public function isJsonStructure(array $structure, array $arrData = null, string $key = null): self
    {
        $data = $arrData ?? $this->getJson($key);

        foreach ($structure as $index => $value) {
            if (is_array($value) && '*' === $index) {
                $this->assertIsArray($data);

                foreach ($data as $dataItem) {
                    $this->isJsonStructure($structure['*'], $dataItem);
                }
            } elseif (is_array($value)) {
                $this->assertArrayHasKey($index, $data);
                $this->isJsonStructure($value, $data[$index]);
            } else {
                $this->assertArrayHasKey($value, $data);
            }
        }

        return $this;
    }

    /**
     * Get Entity Manager.
     */
    public function em(): EntityManagerInterface
    {
        return static::getContainer()->get('doctrine')->getManager();
    }

    public function emPersist(object $object): self
    {
        $this->em()->persist($object);

        return $this;
    }

    public function emRemove(object $object): self
    {
        $this->em()->remove($object);

        return $this;
    }

    public function emFlush(): self
    {
        $this->em()->flush();

        return $this;
    }

    public function emSave(mixed $object): mixed
    {
        $this->em()->persist($object);
        $this->em()->flush();

        return $object;
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
     * Mock TaskWorker.
     */
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
