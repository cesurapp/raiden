<?php

namespace Package\ApiBundle\ArgumentResolver;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller Resolve DTO Request Object.
 */
class DtoResolver implements ValueResolverInterface
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if (!is_subclass_of($argument->getType(), AbstractApiDto::class)) {
            return [];
        }

        $dto = $argument->getType();

        yield new $dto($request, $this->validator);
    }
}
