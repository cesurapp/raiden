<?php

namespace Package\ApiBundle\ArgumentResolver;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller Resolve DTO Request Object.
 */
class DtoResolver implements ArgumentValueResolverInterface
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return is_subclass_of($argument->getType(), AbstractApiDto::class);
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $dto = $argument->getType();

        yield new $dto($request, $this->validator);
    }
}