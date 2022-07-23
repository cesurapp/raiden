<?php

namespace Package\ApiBundle\ArgumentResolver;

use Doctrine\ORM\EntityManagerInterface;
use Package\ApiBundle\Exception\UuidNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Uid\Uuid;

/**
 * Controller Resolve Entity Object.
 */
class EntityResolver implements ArgumentValueResolverInterface
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        if ($argument->getAttributes(CurrentUser::class) || !strpos($argument->getType(), 'Entity\\')) {
            return false;
        }

        return true;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        $attrs = $request->attributes->get('_route_params');
        $key = array_key_first($attrs);

        // Check ID is Uuid
        if ('id' === strtolower($key) && !Ulid::isValid($attrs[$key])) {
            throw new UuidNotFoundException();
        }

        /* @phpstan-ignore-next-line */
        if (!$object = $this->em->getRepository($argument->getType())->findOneBy([$key => $attrs[$key]])) {
            throw new NotFoundHttpException(sprintf('%s %s object not found', ucfirst($argument->getName()), $attrs[$key]));
        }

        yield $object;
    }
}
