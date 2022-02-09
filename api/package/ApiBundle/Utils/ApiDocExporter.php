<?php

namespace Package\ApiBundle\Utils;

use Package\ApiBundle\AbstractClass\AbstractApiDtoRequest;
use Package\ApiBundle\Attribute\ApiDoc;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionUnionType;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class ApiDocExporter
{
    public function __construct(private RouterInterface    $router,
                                private ValidatorInterface $validator,
                                private Environment        $twig
    )
    {
    }

    /**
     * Render Documentation
     */
    public function render(): string
    {
        return $this->twig->render('api-doc.html.twig', ['data' => $this->extractData(true)]);
    }

    /**
     * Read All Attributes from Routes
     *
     * @throws \ReflectionException
     */
    public function extractData($grouped = false): array
    {
        $apiDoc = [];

        foreach ($this->routerList() as $path => $route) {
            $controller = new ReflectionClass($route['controller']);
            $method = $controller->getMethod($route['method']);
            $docAttribute = $method->getAttributes(ApiDoc::class);

            // Append Route
            if (!isset($apiDoc[$path])) {
                $docAttr = isset($docAttribute[0]) ? $docAttribute[0]->getArguments() : null;

                $apiDoc[$path] = [
                    'controller' => $route['controller'] . '::' . $route['method'],
                    'filePath' => $controller->getFileName(),
                    'fileLine' => $method->getStartLine(),
                    'method' => $route['router']->getMethods() ?: ['GET', 'POST'],
                    /** @phpstan-ignore-next-line */
                    'responseType' => Util::baseClass($method->getReturnType()?->getName()) ?? 'Mixed',
                    'response' => $docAttr['response'] ?? [],
                    'description' => $docAttr['description'] ?? '',
                    'query' => array_merge($this->argumentResolver($route['router'], $method), $docAttr['query'] ?? []),
                    'body' => $docAttr ? array_merge($this->extractDto($docAttribute[0]), $docAttr['body'] ?? []) : null
                ];
            }
        }

        ksort($apiDoc);

        if ($grouped) {
            $newDoc = [];
            foreach ($apiDoc as $uri => $doc) {
                $first = explode('/', $uri)[1];
                $newDoc[ucfirst($first)][$uri] = $doc;
            }

            return $newDoc;
        }

        return $apiDoc;
    }


    /**
     * Extract DTO Parameters
     */
    private function extractDto(ReflectionAttribute $attribute): array
    {
        $requestClass = $attribute->getArguments();
        if (!isset($requestClass['apiDto'])) {
            return [];
        }

        $dto = new ReflectionClass($requestClass['apiDto']);

        // Extract DTO
        if ($dto->isSubclassOf(AbstractApiDtoRequest::class)) {
            return $this->extractDTOClass($dto);
        }

        // Extract Entity DTO
        return $this->extractEntityClass($dto);
    }

    /**
     * Extract Request Validation Parameters using AbstractApiDtoRequest
     */
    private function extractDTOClass(ReflectionClass $class): array
    {
        $parameters = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $parameters[$property->getName()] = implode(' | ', array_map(static function ($attr) {
                $args = $attr->getArguments() ? '(' . http_build_query($attr->getArguments(), '', ', ') . ')' : '';
                return Util::baseClass($attr->getName()) . $args;
            }, $property->getAttributes()));
        }

        return $parameters;
    }

    /**
     * Extract Request Validation Parameters using Entity Object
     */
    private function extractEntityClass(ReflectionClass $class): array
    {
        $parameters = [];

        /*** @var PropertyMetadataInterface $metaData */
        if (!empty($this->validator->getMetadataFor($class)->properties)) {
            foreach ($this->validator->getMetadataFor($class)->properties as $name => $metaData) {
                $parameters[$name] = array_map(static fn($class) => Util::baseClass($class), $metaData->getConstraints());
            }
        }

        return $parameters;
    }

    private function argumentResolver(Route $route, \ReflectionMethod $method): array
    {
        $routerVars = $route->compile()->getVariables();
        if (!count($routerVars)) {
            return [];
        }

        /** @var \ReflectionParameter[] $controllerArgs */
        $controllerArgs = array_values(array_filter($method->getParameters(), static function ($p) {
            $check = static function ($typeName) {
                if (strpos($typeName, 'Entity\\')) {
                    return true;
                }

                if (class_exists($typeName) || in_array($typeName, get_declared_interfaces(), true)) {
                    return false;
                }

                return true;
            };

            if ($p->getType() instanceof ReflectionUnionType) {
                return count(array_filter($p->getType()->getTypes(), static fn($item) => $check($item->getName())));
            }

            /** @phpstan-ignore-next-line */
            return $check($p->getType()->getName());
        }));

        $matched = [];
        if (count($routerVars) === count($controllerArgs)) {
            foreach ($routerVars as $index => $key) {
                if ($controllerArgs[$index]->getType() instanceof ReflectionUnionType) {
                    $type = implode('|', array_map(static fn($p) => $p->getName(), $controllerArgs[$index]->getType()->getTypes()));
                } else {
                    /** @phpstan-ignore-next-line */
                    $type = $controllerArgs[$index]->getType()->getName();
                }

                $matched[$key] = Util::baseClass($type) . ($route->getRequirement($key) ? " ({$route->getRequirement($key)})" : '');
            }
        }

        return $matched;
    }

    /**
     * Get All Routes
     */
    private function routerList(): array
    {
        $list = [];

        foreach ($this->router->getRouteCollection()->all() as $router) {
            if ($router->getDefault('_controller')) {
                [$controller, $method] = explode('::', $router->getDefault('_controller'));
                if (!class_exists($controller)) {
                    continue;
                }

                $list[$router->getPath()] = [
                    'controller' => $controller,
                    'method' => $method,
                    'router' => $router
                ];
            }
        }

        return $list;
    }
}