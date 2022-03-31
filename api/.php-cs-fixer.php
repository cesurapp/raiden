<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude(['var', 'vendor', 'package/ApiBundle/Thor/Template']);

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
    ])
    ->setCacheFile(__DIR__.'/var/cache/.php-cs-fixer.cache')
    ->setFinder($finder);
