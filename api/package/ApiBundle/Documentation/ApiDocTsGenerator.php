<?php

namespace Package\ApiBundle\Documentation;

use Swoole\Process;
use Twig\Environment;

class ApiDocTsGenerator
{
    private string $path;
    private string $pathResponse;
    private string $pathRequest;
    private string $pathQuery;

    public function __construct(private array $data, private Environment $twig)
    {
        $this->twig->addExtension(new ApiDocTwigHelper());

        // Generate Path
        $this->path = sys_get_temp_dir().uniqid('', false);
        $this->pathResponse = $this->path.'/Response';
        $this->pathRequest = $this->path.'/Request';
        $this->pathQuery = $this->path.'/Query';

        foreach ([$this->path, $this->pathResponse, $this->pathRequest, $this->pathQuery] as $dir) {
            if (!mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
            }
        }
    }

    public function generate(): self
    {
        foreach ($this->data as $groupName => $groupRoutes) {
            foreach ($groupRoutes as $route) {
                $this->createResponse($route);
                $this->createRequest($route);
                $this->createQuery($route);
            }
        }

        // Render Index
        file_put_contents($this->path.'/index.ts', $this->twig->render('@Api/typescript/index.ts.twig', [
            'data' => $this->data,
        ]));

        return $this;
    }

    public function compress(string $path): self
    {
        $cmd = new Process(function (Process $p) use ($path) {
            $p->exec(exec('which tar'), ['-czvf', "$path/Api.tar.bz2", '-C', $this->path, '.']);
        });
        $cmd->start();

        return $this;
    }

    public function copyFiles(string $path): self
    {
        $cmd = new Process(function (Process $p) use ($path) {
            $p->exec(exec('which tar'), ['-czvf', "$path/Api.tar.bz2", '-C', $this->path, '.']);
        });
        $cmd->start();

        return $this;
    }

    public function createResponse(array $route): void
    {
        if (!$route['controllerResponse']) {
            return;
        }

        $name = sprintf('%sResponse.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathResponse."/{$name}", $this->twig->render('@Api/typescript/response.ts.twig', [
            'data' => $route,
        ]));
    }

    public function createRequest(array $route): void
    {
        if (!$route['post']) {
            return;
        }

        $name = sprintf('%sRequest.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathRequest."/{$name}", $this->twig->render('@Api/typescript/request.ts.twig', [
            'data' => $route,
        ]));
    }

    public function createQuery(array $route): void
    {
        if (!$route['get']) {
            return;
        }

        $name = sprintf('%sQuery.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathQuery."/{$name}", $this->twig->render('@Api/typescript/query.ts.twig', [
            'data' => $route,
        ]));
    }
}
