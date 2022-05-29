<?php

namespace Package\MediaBundle;

use Doctrine\DBAL\Types\Type;
use Package\MediaBundle\Type\MediaType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class MediaBundle extends Bundle
{
    public function __construct()
    {
        if (!Type::getTypeRegistry()->has('media')) {
            Type::getTypeRegistry()->register('media', new MediaType());
        }
    }

    public function boot(): void
    {
        /** @var MediaType $type */
        $type = Type::getType('media');
        $type->setEntityManager($this->container->get('doctrine')->getManager());
    }
}
