<?php

namespace Package\ApiBundle\Thor\Generator;

use Package\ApiBundle\Response\ApiResourceInterface;
use Symfony\Component\HttpFoundation\File\File;

class TypeScriptGenerator
{
    private string $path;
    private string $pathResponse;
    private string $pathRequest;
    private string $pathQuery;
    private string $pathTable;
    private string $pathEnum;
    private string $pathResource;
    private TypeScriptHelper $helper;

    public function __construct(private readonly array $data)
    {
        // Create Template Helper
        $this->helper = new TypeScriptHelper();

        // Generate Path
        $this->path = sys_get_temp_dir().uniqid('', false);
        $this->pathResponse = $this->path.'/Response';
        $this->pathRequest = $this->path.'/Request';
        $this->pathQuery = $this->path.'/Query';
        $this->pathTable = $this->path.'/Table';
        $this->pathEnum = $this->path.'/Enum';
        $this->pathResource = $this->path.'/Resource';

        foreach (
            [
                $this->path,
                $this->pathResponse,
                $this->pathRequest,
                $this->pathQuery,
                $this->pathTable,
                $this->pathEnum,
                $this->pathResource,
            ] as $dir
        ) {
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
        foreach ($this->data as $key => $groupRoutes) {
            if (str_starts_with($key, '_')) {
                switch ($key) {
                    case '_enums': $this->generateEnum($groupRoutes);
                    break;
                    case '_resource': $this->generateResources($groupRoutes);
                }

                continue;
            }

            foreach ($groupRoutes as $route) {
                $this->generateResponse($route);
                $this->generateRequest($route);
                $this->generateQuery($route);
                $this->generateTable($route);
            }
        }

        // Render Index
        file_put_contents($this->path.'/index.ts', $this->renderTemplate('index.ts.php', [
            'data' => array_filter($this->data, static fn ($k) => !str_starts_with($k, '_'), ARRAY_FILTER_USE_KEY),
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
        $tmpPath = $path ?? sys_get_temp_dir();

        shell_exec("tar -czvf $tmpPath/Api.tar.bz2 -C $this->path . 2>&1");
        while (true) {
            if (file_exists($tmpPath.'/Api.tar.bz2')) {
                break;
            }
            usleep(50000);
        }

        return new File($tmpPath.'/Api.tar.bz2');
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

    /**
     * Generate Response Parameters.
     */
    private function generateResponse(array $route): void
    {
        if (!$route['response']) {
            return;
        }

        $resources = [];
        array_walk_recursive($route, static function ($val) use (&$resources) {
            if (is_string($val) && class_exists($val) && in_array(ApiResourceInterface::class, class_implements($val), true)) {
                $resources[] = TypeScriptHelper::baseClass($val);
            }
        });

        $name = sprintf('%sResponse.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathResponse."/{$name}", $this->renderTemplate('response.ts.php', [
            'data' => $route,
            'resources' => $resources,
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
     * Generate DataTable Columns.
     */
    private function generateTable(array $route): void
    {
        if (!$route['table']) {
            return;
        }

        $name = sprintf('%sTable.ts', ucfirst($route['shortName']));
        file_put_contents($this->pathTable."/{$name}", $this->renderTemplate('table.ts.php', [
            'data' => $route,
        ]));
    }

    /**
     * Generate DataTable Columns.
     */
    private function generateEnum(array $enumsGroup): void
    {
        foreach ($enumsGroup as $namespace => $enumData) {
            $file = 'Permission' === $namespace ? 'permission.ts.php' : 'enum.ts.php';
            $name = sprintf('%s.ts', ucfirst($namespace));
            $enumData = is_array($enumData) ? $enumData : $enumData::cases();
            file_put_contents($this->pathEnum."/{$name}", $this->renderTemplate($file, [
                'namespace' => $namespace,
                'data' => $enumData,
            ]));
        }
    }

    /**
     * Generate Resources.
     */
    private function generateResources(array $resourceGroup): void
    {
        foreach ($resourceGroup as $namespace => $data) {
            $name = sprintf('%s.ts', ucfirst($namespace));
            file_put_contents($this->pathResource."/{$name}", $this->renderTemplate('resource.ts.php', [
                'namespace' => $namespace,
                'data' => $data,
            ]));
        }
    }
}
