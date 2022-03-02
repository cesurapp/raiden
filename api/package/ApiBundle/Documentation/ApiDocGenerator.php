<?php

namespace Package\ApiBundle\Documentation;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Package\ApiBundle\Exception\ValidationException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Validator\Mapping\PropertyMetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Twig\Environment;

class ApiDocGenerator
{
    protected array $defaultDoc = [];

    public function __construct(
        private RouterInterface $router,
        private ValidatorInterface $validator,
        private Environment $twig,
        protected ParameterBagInterface $bag
    ) {
        if (file_exists($bag->get('apidoc_global_config'))) {
            $config = require $bag->get('apidoc_global_config');
            $this->defaultDoc = $config();
        }
    }

    /**
     * Render Documentation.
     */
    public function render(bool $devMode = true, array $customData = []): string
    {
        return $this->twig->render('@Api/documentation.html.twig', [
            'data' => $this->extractData(true),
            'statusText' => Response::$statusTexts,
            'devMode' => $devMode,
            'customData' => $customData
        ]);
    }

    /**
     * Read All Attributes from Routes.
     */
    public function extractData(bool $grouped = false): array
    {
        $apiDoc = [];

        foreach ($this->routerList() as $path => $route) {
            $controller = new ReflectionClass($route['controller']);
            $method = $controller->getMethod($route['method']);
            $docAttribute = $method->getAttributes(ApiDoc::class);

            // Append Route
            if (!isset($apiDoc[$path])) {
                $docAttr = isset($docAttribute[0]) ? $docAttribute[0]->getArguments() : [];
                $docAttr = array_replace_recursive($this->defaultDoc, $docAttr);

                // Hidden
                if (!empty($docAttr['hidden'])) {
                    continue;
                }

                $apiDoc[$path] = [
                    // Options
                    'group' => $docAttr['group'] ?? '',
                    'desc' => $docAttr['desc'] ?? '',
                    'hidden' => $docAttr['hidden'] ?? false,
                    'paginate' => $docAttr['paginate'] ?? false,
                    'paginateCursor' => $docAttr['paginateCursor'] ?? false,
                    'requireAuth' => $docAttr['requireAuth'] ?? true,

                    // Router
                    'endpointMethod' => $route['router']->getMethods() ?: ['GET', 'POST'],
                    'endpointAttr' => $this->extractEndpointAttr($route['router'], $method, $docAttr),

                    // Controller
                    'controller' => $route['controller'].'::'.$route['method'],
                    'controllerPath' => $controller->getFileName(),
                    'controllerLine' => $method->getStartLine(),
                    'controllerResponse' => $this->extractControllerResponse($docAttr, $method),
                    'controllerResponseType' => $this->extractControllerResponseType($docAttr, $method),

                    // DTO
                    'get' => $this->extractGetParameters($docAttr),
                    'post' => $this->extractPostParameters($docAttr),
                    'header' => $this->extractHeaderParameters($docAttr),
                    'exception' => $this->extractExceptions($docAttr),
                ];
            }
        }

        ksort($apiDoc);

        if ($grouped) {
            $newDoc = [];
            foreach ($apiDoc as $uri => $doc) {
                if ($doc['group']) {
                    $newDoc[$doc['group']][$uri] = $doc;
                    continue;
                }

                $first = explode('/', $uri)[1];
                $newDoc[ucfirst($first)][$uri] = $doc;
            }

            return $newDoc;
        }

        return $apiDoc;
    }

    /**
     * Extract Route Attributes.
     */
    private function extractEndpointAttr(Route $route, ReflectionMethod $method, array $docAttr): array
    {
        $routerVars = $route->compile()->getVariables();
        if (!count($routerVars)) {
            return [];
        }

        /** @var \ReflectionParameter[] $controllerArgs */
        $controllerArgs = array_values(
            array_filter($method->getParameters(), static function ($p) {
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
                    return count(array_filter($p->getType()->getTypes(), static fn ($item) => $check($item->getName())));
                }

                return $check($p->getType()->getName()); // @phpstan-ignore-line
            })
        );

        $matched = [];
        if (count($routerVars) === count($controllerArgs)) {
            foreach ($routerVars as $index => $key) {
                if ($controllerArgs[$index]->getType() instanceof ReflectionUnionType) {
                    $type = implode(
                        '|',
                        array_map(static fn ($p) => $p->getName(), $controllerArgs[$index]->getType()->getTypes())
                    );
                } else {
                    /** @phpstan-ignore-next-line */
                    $type = $controllerArgs[$index]->getType()->getName();
                }

                $matched[$key] = $this->baseClass($type).($route->getRequirement(
                    $key
                ) ? " ({$route->getRequirement($key)})" : '');
            }
        }

        return $matched;
    }

    /**
     * Extract Controller Response Type.
     */
    private function extractControllerResponseType(array $docAttr, ReflectionMethod $method): string
    {
        if (!$method->getReturnType()) {
            return 'Mixed';
        }

        return $this->baseClass($method->getReturnType()->getName()); // @phpstan-ignore-line
    }

    /**
     * Extract Controller Response Structure.
     */
    private function extractControllerResponse(array $docAttr, ReflectionMethod $method): array
    {
        $response = [];

        // Extract Resource
        if (!empty($docAttr['resource'])) {
            $resource = new ReflectionClass($docAttr['resource']);
            $apiResource = $resource->getMethod('toArray')->getAttributes(ApiResource::class);
            if (count($apiResource) > 0) {
                $response[200] = $apiResource[0]->getArguments()['data'];
            }
        }

        // Api Response
        if ('ApiResponse' === $this->extractControllerResponseType($docAttr, $method)) {
            $response[200] = [
                'type' => 'ApiResult',
                'data' => array_replace_recursive($response[200] ?? [], $docAttr['success'][200] ?? []),
            ];

            if (!empty($docAttr['paginate'])) {
                $response[200]['pager'] = [
                    'max' => 'int',
                    'prev' => 'int|null',
                    'next' => 'int|null',
                    'current' => 'int',
                    'total' => 'int|null',
                ];

                if (!empty($docAttr['paginateCursor'])) {
                    // todo Paginate Cursor
                }
            }
        }

        return array_replace_recursive($response, $docAttr['success'] ?? []);
    }

    /**
     * Generate Get|Query Parameters.
     */
    private function extractGetParameters(array $docAttr): array
    {
        $attr = [];

        // Append Paginator Query
        if (!empty($docAttr['paginate'])) {
            if (empty($docAttr['paginateCursor'])) {
                $attr['page'] = 'int';
            } else {
                $attr['next'] = 'string';
            }
        }

        return array_replace_recursive($attr, $docAttr['get'] ?? []);
    }

    /**
     * Generate Post|DTO Parameters.
     */
    private function extractPostParameters(array $docAttr): array
    {
        $attr = [];

        // Extract DTO Parameters
        if (isset($docAttr['dto'])) {
            $dto = new ReflectionClass($docAttr['dto']);
            if ($dto->isSubclassOf(AbstractApiDto::class)) {
                $attr = array_replace_recursive($attr, $this->extractDTOClass($dto));
            }
        }

        return array_replace_recursive($attr, $docAttr['post'] ?? []);
    }

    /**
     * Generate Header Parameters.
     */
    private function extractHeaderParameters(array $docAttr): array
    {
        $attr = [];

        // Append Auth Header
        if (!empty($docAttr['requireAuth'])) {
            $attr = $docAttr['authHeader'] ?? [];
        }

        return array_replace_recursive($attr, $docAttr['header'] ?? []);
    }

    /**
     * Generate Exceptions.
     */
    private function extractExceptions(array $docAttr): array
    {
        $attr = [];

        // Extract Resource Validation Exception
        if (!empty($docAttr['resource'])) {
            $docAttr['exception'] = array_merge_recursive($docAttr['exception'] ?? [], [ValidationException::class]);
        }

        if (!empty($docAttr['exception'])) {
            foreach ($docAttr['exception'] as $exceptionClass => $customException) {
                $exceptionClass = !is_array($customException) ? $customException : $exceptionClass;

                if (class_exists($exceptionClass)) {
                    /** @var \Exception $exception */
                    $exception = new $exceptionClass();

                    $e = [
                        'type' => $this->baseClass($exceptionClass),
                        'code' => $exception->getCode(),
                        'message' => $exception->getMessage(),
                    ];

                    if (method_exists($exception, 'getErrors')) {
                        $e['errors'] = [];
                    }

                    if (is_array($customException)) {
                        $e = array_replace($e, $customException);
                    }

                    $attr[$this->baseClass($exceptionClass)] = $e;
                } else {
                    $attr[$exceptionClass] = $customException;
                }
            }
        }

        return $attr;
    }

    /**
     * Extract Request Validation Parameters using AbstractApiDto.
     */
    private function extractDTOClass(ReflectionClass $class): array
    {
        $parameters = [];
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $parameters[$property->getName()] = implode(
                ' | ',
                array_map(function ($attr) {
                    $args = $attr->getArguments() ? '('.http_build_query($attr->getArguments(), '', ', ').')' : '';

                    return $this->baseClass($attr->getName()).$args;
                }, $property->getAttributes())
            );
        }

        return $parameters;
    }

    /**
     * Extract Request Validation Parameters using Entity Object.
     */
    private function extractDTOEntity(ReflectionClass $class): array
    {
        $parameters = [];

        /*** @var PropertyMetadataInterface $metaData */
        if (!empty($this->validator->getMetadataFor($class)->properties)) {
            foreach ($this->validator->getMetadataFor($class)->properties as $name => $metaData) {
                $parameters[$name] = array_map(fn ($class) => $this->baseClass($class), $metaData->getConstraints());
            }
        }

        return $parameters;
    }

    /**
     * Get All Routes.
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
                    'router' => $router,
                ];
            }
        }

        return $list;
    }

    /**
     * Extract Class Name.
     */
    private function baseClass(string|object|null $class): string|null
    {
        return $class ? basename(str_replace('\\', '/', is_object($class) ? get_class($class) : $class)) : null;
    }
}
