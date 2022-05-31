<?php

namespace Package\ApiBundle;

use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Response\ApiResourceLocator;
use Package\ApiBundle\Thor\Controller\ThorController;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ApiBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode() // @phpstan-ignore-line
            ->children()
                ->scalarNode('storage_path')->defaultValue('')->end()
                ->scalarNode('globals')->defaultValue('')->end()
                ->scalarNode('base_url')->defaultValue('')->end()
                ->scalarNode('ts_extra_path')->defaultValue('')->end()
            ->end();
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Init Resource Locator
        $resources = $builder->findTaggedServiceIds('resources');
        array_walk($resources, static fn (&$val, $id) => $val = new Reference($id));
        $builder
            ->register(ApiResourceLocator::class, ApiResourceLocator::class)
            ->addArgument(ServiceLocatorTagPass::register($builder, $resources));
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // Register Configuration
        foreach ($config as $key => $value) {
            $builder->getParameterBag()->set('thor.'.$key, $value);
        }

        // Register Api Resources
        $builder->registerForAutoconfiguration(ApiResourceInterface::class)
            ->addTag('resources')
            ->setLazy(true);

        // Load Services
        $services = $container->services()->defaults()->autowire()->autoconfigure();

        // Event Listener
        $services->load('Package\\ApiBundle\\EventListener\\', 'EventListener');

        // Argument Resolver
        $services->load('Package\\ApiBundle\\ArgumentResolver\\', 'ArgumentResolver');

        // Thor
        $services->load('Package\\ApiBundle\\Thor\\Command\\', 'Thor/Command');
        $services->set(ThorController::class, ThorController::class);
    }
}
