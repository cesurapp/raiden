<?php

namespace Package\ApiBundle\Thor\Generator;

use Symfony\Component\HttpFoundation\File\File;

class TypeScriptGenerator
{
    private string $path;
    private string $pathResponse;
    private string $pathRequest;
    private string $pathQuery;
    private TypeScriptHelper $helper;

    public function __construct(private array $data)
    {
        // Create Template Helper
        $this->helper = new TypeScriptHelper();

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

    /**
     * Generate TS Files.
     */
    public function generate(): self
    {
        foreach ($this->data as $groupRoutes) {
            foreach ($groupRoutes as $route) {
                $this->generateResponse($route);
                $this->generateRequest($route);
                $this->generateQuery($route);
            }
        }

        // Render Index
        file_put_contents($this->path.'/index.ts', $this->renderTemplate('index.ts.php', [
            'data' => $this->data,
        ]));

        // Render Dependency
        file_put_contents($this->path.'/flatten.ts', $this->renderTemplate('flatten.ts.php', [
            'data' => $this->data,
        ]));

        return $this;
    }

    /**
     * Compress TS Library to TAR Format.
     */
    public function compress(?string $path = null): File
    {
        $path ??= $this->path;

        exec("tar -czvf $path/Api.tar.bz2 -C $this->path .");
        while (true) {
            if (file_exists($path.'/Api.tar.bz2')) {
                break;
            }
            usleep(50000);
        }

        return new File($path.'/Api.tar.bz2');
    }

    /**
     * Copy Generated Directory to Custom Path.
     */
    public function copyFiles(string $path): void
    {
        // Remove Old Path
        if (file_exists($path)) {
            exec("rm -rf $path");
        }

        exec("cp -R $this->path $path");
        while (true) {
            if (is_dir($path)) {
                break;
            }
            usleep(50000);
        }
    }

    /**
     * Generate Response Parameters.
     */
    private function generateResponse(array $route): void
    {
        if (!$route['response']) {
            return;
        }

        $name = sprintf('%sResponse.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathResponse."/{$name}", $this->renderTemplate('response.ts.php', [
            'data' => $route,
        ]));
    }

    /**
     * Generate POST|PUT|PATCH Parameters.
     */
    private function generateRequest(array $route): void
    {
        if (!$route['request']) {
            return;
        }

        $name = sprintf('%sRequest.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathRequest."/{$name}", $this->renderTemplate('request.ts.php', [
            'data' => $route,
        ]));
    }

    /**
     * Generate GET Parameters.
     */
    private function generateQuery(array $route): void
    {
        if (!$route['query']) {
            return;
        }

        $name = sprintf('%sQuery.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathQuery."/{$name}", $this->renderTemplate('query.ts.php', [
            'data' => $route,
        ]));
    }

    /**
     * Render PHP Template.
     */
    private function renderTemplate(string $template, array $data = []): string
    {
        $data['helper'] = $this->helper;
        extract($data, EXTR_OVERWRITE);

        ob_start();
        include __DIR__.'/../Template/typescript/'.$template;

        return ob_get_clean();
    }
}