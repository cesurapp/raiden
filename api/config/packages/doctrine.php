<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $mappings = [];
    $rootDir = dirname(__DIR__, 2);
    $entityDirs = array_filter(glob($rootDir.'/src/{,*/,*/*/,*/*/*/,*/*/*/*/}Entity', GLOB_BRACE), 'is_dir');
    foreach ($entityDirs as $index => $dir) {
        $prefix = str_replace(['/src/', '/'], ['', '\\'], dirname(str_replace($rootDir, '', $dir)));
        $mappings[0 === $index ? 'App' : $prefix] = [
            'is_bundle' => false,
            'dir' => $dir,
            'prefix' => 'App\\'.$prefix.'\\Entity',
            'alias' => str_replace('\\', '', $prefix),
        ];
    }

    $containerConfigurator->extension('doctrine', [
        'dbal' => [
            'url' => '%env(resolve:DATABASE_URL)%',
            'options' => [
                PDO::ATTR_PERSISTENT => true,
            ],
        ],
        'orm' => [
            'auto_generate_proxy_classes' => true,
            'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
            'auto_mapping' => true,
            'mappings' => $mappings,
        ],
    ]);

    // Prod
    if ('prod' === $containerConfigurator->env()) {
        $containerConfigurator->extension('framework', [
            'cache' => [
                'pools' => [
                    'doctrine.system_cache_pool' => [
                        'adapter' => 'cache.system',
                    ],
                    'doctrine.result_cache_pool' => [
                        'adapter' => 'cache.app',
                    ],
                ],
            ],
        ]);
        $containerConfigurator->extension('doctrine', [
            'orm' => [
                'auto_generate_proxy_classes' => false,
                'query_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.system_cache_pool',
                ],
                'result_cache_driver' => [
                    'type' => 'pool',
                    'pool' => 'doctrine.result_cache_pool',
                ],
            ],
        ]);
    }

    // Test
    if ('test' === $containerConfigurator->env()) {
        $containerConfigurator->extension('doctrine', [
            'dbal' => [
                'dbname_suffix' => '_test%env(default::TEST_TOKEN)%',
            ],
        ]);
    }
};
