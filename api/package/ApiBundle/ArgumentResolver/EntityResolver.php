<?php

namespace Package\ApiBundle\ArgumentResolver;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller Resolve Entity Object
 */
class EntityResolver implements ArgumentValueResolverInterface
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if (!strpos($argument->getType(), 'Entity\\')) {
            return false;
        }

        return true;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $attrs = $request->attributes->get('_route_params');
        $key = array_key_first($attrs);

        if (!$object = $this->em->getRepository($argument->getType())->findOneBy([$key => $attrs[$key]])) {
            throw new NotFoundHttpException(
                sprintf('%s %s object not found', ucfirst($argument->getName()), $attrs[$key])
            );
        }

        yield $object;
    }
}