<?php

namespace Package\SwooleBundle\Task;

use Symfony\Component\DependencyInjection\ServiceLocator;

class TaskLocator
{
    public function __construct(private ServiceLocator $locator)
    {
    }

    public function get()
    {
        dump($this->locator->getProvidedServices());
    }
}
