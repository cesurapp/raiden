<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
    Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
    Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
    Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
    Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true],
    Sonata\Exporter\Bridge\Symfony\SonataExporterBundle::class => ['all' => true],
    Cesurapp\StorageBundle\StorageBundle::class => ['all' => true],
    Cesurapp\MediaBundle\MediaBundle::class => ['all' => true],
    Cesurapp\ApiBundle\ApiBundle::class => ['all' => true],
    Cesurapp\SwooleBundle\SwooleBundle::class => ['all' => true],
];
