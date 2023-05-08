<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Finder\Finder;

return static function (ContainerConfigurator $containerConfigurator): void {
    $mappings = [];
    $finder = new Finder();
    foreach ($finder->directories()->name('Entity')->in(dirname(__DIR__, 2).'/src') as $item) {
        $alias = str_replace('/', '', $item->getRelativePath());

        $mappings[0 === count($mappings) ? 'App' : $alias] = [
            'is_bundle' => false,
            'dir' => $item->getRealPath(),
            'prefix' => 'App\\'.str_replace('/', '\\', $item->getRelativePathname()),
            'alias' => $alias,
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
