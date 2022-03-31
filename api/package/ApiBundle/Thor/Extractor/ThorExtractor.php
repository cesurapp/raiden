<?php

namespace Package\ApiBundle\Thor\Extractor;

use Package\ApiBundle\AbstractClass\AbstractApiDto;
use Package\ApiBundle\Exception\ValidationException;
use Package\ApiBundle\Response\ApiResourceInterface;
use Package\ApiBundle\Response\ApiResponse;
use Package\ApiBundle\Response\ResponseTypeEnum;
use Package\ApiBundle\Thor\Attribute\Thor;
use Package\ApiBundle\Thor\Attribute\ThorResource;
use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class ThorExtractor
{
    protected array $defaults = [];

    public function __construct(private RouterInterface $router, protected ParameterBagInterface $bag)
    {
        if (file_exists($bag->get('thor.globals'))) {
            $config = require $bag->get('thor.globals');
            $this->defaults = $config();
        }
    }

    /**
     * Render Documentation Template.
     */
    public function render(?array $data = null, array $customData = []): string
    {
        // Template Data
        if (!$data) {
            $data = $this->extractData(true);
        }
        $projectDir = $this->bag->get('kernel.project_dir');
        $statusText = Response::$statusTexts;
        $baseUrl = $this->bag->get('thor.base_url');
        extract($customData, EXTR_OVERWRITE);

        // Render Response
        ob_start();
        include __DIR__.'/../Template/base.html.php';

        return ob_get_clean();
    }

    /**
     * Extract Data.
     */
    public function extractData(bool $grouped = false): array
    {
        $data = [];

        foreach ($this->routerList() as $path => $route) {
            $refController = new ReflectionClass($route['controller']);
            $refMethod = $refController->getMethod($route['method']);
            $attrThor = $refMethod->getAttributes(Thor::class);

            // Append Route
            [$key, $path] = explode('::', $path);

            if (!isset($data[$key])) {
                $attrThor = isset($attrThor[0]) ? $attrThor[0]->getArguments() : [];
                $attrThor = array_replace_recursive($this->defaults, $attrThor);

                // Hidden
                if (!empty($attrThor['hidden'])) {
                    continue;
                }

                $data[$key] = [
                    'path' => $path,
                    'shortController' => ucfirst(str_replace('Controller', '', $refController->getShortName())),
                    'shortName' => lcfirst(str_replace('Controller', '', $refController->getShortName())).ucfirst($refMethod->getShortName()),

                    // Options
                    'group' => $attrThor['group'] ?? '',
                    'desc' => $attrThor['desc'] ?? '',
                    'hidden' => $attrThor['hidden'] ?? false,
                    'paginate' => $attrThor['paginate'] ?? false,
                    'requireAuth' => $attrThor['requireAuth'] ?? true,

                    // Router
                    'routerPath' => $route['router']->getPath(),
                    'routerMethod' => $route['router']->getMethods() ?: ['GET'],
                    'routerAttr' => $this->extractRouteAttr($route['router'], $refMethod, $attrThor),

                    // Controller
                    'controller' => $route['controller'].'::'.$route['method'],
                    'controllerPath' => str_replace($this->bag->get('kernel.project_dir'), '', $refController->getFileName()),
                    'controllerLine' => $refMethod->getStartLine(),
                    'controllerResponseType' => !$refMethod->getReturnType() ? 'Mixed' : $this->baseClass($refMethod->getReturnType()->getName()), // @phpstan-ignore-line

                    // DTO
                    'query' => $this->extractQueryParameters($attrThor),
                    'request' => $this->extractRequestParameters($attrThor),
                    'header' => $this->extractHeaderParameters($attrThor),
                    'response' => $this->extractResponse($attrThor, $refMethod, $route['router']->getMethods() ?: ['GET']),
                ];
            }
        }

        // Sort Data
        sort($data);

        if ($grouped) {
            $newDoc = [];
            foreach ($data as $name => $doc) {
                if ($doc['group']) {
                    $newDoc[$doc['group']][$name] = $doc;
                    continue;
                }

                $first = explode('/', $doc['path'])[1];
                $newDoc[ucfirst($first)][$name] = $doc;
            }

            return $newDoc;
        }

        return $data;
    }

    /**
     * Extract Route Attributes.
     */
    private function extractRouteAttr(Route $route, ReflectionMethod $method, array $attrThor): array
    {
        $routerVars = $route->compile()->getVariables();
        if (!count($routerVars)) {
            return [];
        }

        /** @var \ReflectionParameter[] $controllerArgs */
        $controllerArgs = array_values(
            array_filter($method->getParameters(), static function ($p) {
                $check = static function ($typeName) {
                    // Entity Object
                    if (strpos($typeName, 'Entity\\')) {
                        return true;
                    }

                    // Vendor Class
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
                $isNull = false;

                if ($controllerArgs[$index]->getType() instanceof ReflectionUnionType) {
                    if ($controllerArgs[$index]->getType()->allowsNull()) {
                        $isNull = true;
                    }
                    $types = array_map(static fn ($p) => $p->getName(), $controllerArgs[$index]->getType()->getTypes());
                } else {
                    if ($controllerArgs[$index]->getType()->allowsNull()) {
                        $isNull = true;
                    }
                    $types = [$controllerArgs[$index]->getType()->getName()]; //@phpstan-ignore-line
                }

                // Remove Null
                if (in_array('null', $types, true)) {
                    unset($types[array_search('null', $types, true)]);
                }

                $matched[$key] = implode('|', array_unique(array_map(function ($type) use ($key, $isNull) {
                    if (class_exists($type)) {
                        $ref = new ReflectionClass($type);
                        if ($ref->hasProperty($key)) {
                            return implode('|', $this->extractTypes($ref->getProperty($key)->getType(), $isNull));
                        }

                        return ($isNull ? '?' : '').'mixed';
                    }

                    return ($isNull ? '?' : '').$type;
                }, $types)));
            }
        }

        return $matched;
    }

    /**
     * Generate Get|Query Parameters.
     */
    private function extractQueryParameters(array $attrThor): array
    {
        $attr = [];

        // Append Paginator Query
        if (!empty($attrThor['paginate'])) {
            $attr['page'] = 'int';
        }

        return array_replace_recursive($attr, $attrThor['get'] ?? []);
    }

    /**
     * Generate Header Parameters.
     */
    private function extractHeaderParameters(array $attrThor): array
    {
        $attr = [];

        // Append Auth Header
        if (!empty($attrThor['requireAuth'])) {
            $attr = $attrThor['authHeader'] ?? [];
        }

        return array_replace_recursive($attr, $attrThor['header'] ?? []);
    }

    /**
     * Generate Exceptions.
     */
    private function extractResponse(array $thorAttr, ReflectionMethod $refMethod, array $methods): array
    {
        $response = [];

        // Render Exception Class
        $renderException = static function (ReflectionClass|string $refClass) {
            if (is_string($refClass)) {
                $refClass = new ReflectionClass($refClass);
            }
            $parameters = array_reduce($refClass->getConstructor()?->getParameters(), static function ($result, $item) {
                $result[$item->name] = $item;

                return $result;
            }, []);

            $exception = [
                'type' => ResponseTypeEnum::ApiException->name,
                'code' => $parameters['code']->getDefaultValue(),
                'message' => $parameters['message']->getDefaultValue(),
            ];

            if (isset($parameters['errors'])) {
                $exception['errors'] = [];
            }

            return $exception;
        };

        // Render Resource
        $renderResource = static function (ReflectionClass|string $refClass) use ($thorAttr) {
            if (is_string($refClass)) {
                $refClass = new ReflectionClass($refClass);
            }
            $thorResource = $refClass->getMethod('toArray')->getAttributes(ThorResource::class);
            if (count($thorResource)) {
                $data = $thorResource[0]->getArguments()['data'];

                return [
                    'type' => 'ApiResult',
                    'data' => !empty($thorAttr['paginate']) ? [$data] : $data,
                ];
            }

            return [];
        };

        foreach ($thorAttr['response'] as $resKey => $resValue) {
            // Class
            if (!is_array($resValue) && class_exists($resValue)) {
                $refClass = new ReflectionClass($resValue);

                // Resources
                if ($refClass->implementsInterface(ApiResourceInterface::class)) {
                    $response[$resKey > 50 ? $resKey : 200] = $renderResource($refClass);
                }

                // Exceptions
                if ($refClass->implementsInterface(\Throwable::class)) {
                    $exception = $renderException($resValue);
                    $response[$exception['code']] = $exception;
                }
            } else {
                // Custom
                $response[$resKey] = $resValue;
            }
        }

        // Append DTO Exception Response
        if (isset($thorAttr['dto']) && !in_array('GET', $methods, false)) {
            $exception = $renderException(ValidationException::class);
            $response[$exception['code']] = $exception;
        }

        // Append Pagination
        if (!empty($thorAttr['paginate'])) {
            $response[200]['pager'] = [
                'max' => 'int',
                'current' => 'int',
                'prev' => '?int',
                'next' => '?int',
                'total' => '?int',
            ];
        }

        // Empty Response
        if (ApiResponse::class === $refMethod->getReturnType()?->getName()) { // @phpstan-ignore-line
            if (!$response) {
                $response[200] = [
                    'type' => ResponseTypeEnum::ApiResult->name,
                ];
            }
        }

        ksort($response);

        return $response;
    }

    /**
     * Generate Post|DTO Parameters.
     */
    private function extractRequestParameters(array $attrThor): array
    {
        $attr = [];

        // Extract DTO Parameters
        if (isset($attrThor['dto'])) {
            $dto = new ReflectionClass($attrThor['dto']);
            if ($dto->isSubclassOf(AbstractApiDto::class)) {
                $attr = array_replace_recursive($attr, $this->extractDTOClass($dto));
            }
        }

        return array_replace_recursive($attr, $attrThor['post'] ?? []);
    }

    /**
     * Extract Request Validation Parameters using AbstractApiDto.
     */
    private function extractDTOClass(ReflectionClass $class): array
    {
        $parameters = [];

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            $values = [];

            // Extract Types
            $types = implode('|', $this->extractTypes($property->getType()));
            if ($types) {
                $values['types'] = $types;
            }

            // Api Resource
            $apiResource = $property->getAttributes(ThorResource::class);
            if (str_contains($types, 'array') && count($apiResource)) {
                $r = $apiResource[0]->getArguments()['data'];
                $parameters[$property->getName()] = $r;
                continue;
            }

            // Validation
            $valids = $this->renderValidationAttributes($property->getAttributes());
            if ($valids['validations']) {
                $values['validations'] = $valids['validations'];
            }

            $parameters[$property->getName()] = implode(';', $values);
        }

        return $parameters;
    }

    /**
     * @param \ReflectionAttribute[] $attributes
     */
    private function renderValidationAttributes(array $attributes): array
    {
        $validations = implode('|', array_map(function ($attribute) {
            // Find Constants
            foreach ($attribute->getArguments() as $key => $value) {
                if (is_array($value)) {
                    continue;
                }
            }

            $args = $attribute->getArguments() ? '('.http_build_query($attribute->getArguments(), '', ', ').')' : '';

            return $this->baseClass($attribute->getName()).$args;
        }, $attributes));

        return [
            'validations' => $validations,
            'items' => [],
        ];
    }

    private function extractTypes(\ReflectionType|\ReflectionNamedType $type, bool $isNull = false): array
    {
        $types = [];

        if ($type instanceof ReflectionUnionType) {
            $isNull = !$isNull ? $type->allowsNull() : true;

            foreach ($type->getTypes() as $item) {
                if (class_exists($item->getName())) {
                    $types[] = $isNull ? '?string' : 'string';
                    $types[] = $isNull ? '?int' : 'int';
                } else {
                    $types[] = ($isNull ? '?' : '').$item->getName();
                }
            }
        } else {
            $types[] = ($type->allowsNull() ? '?' : '').$type->getName(); //@phpstan-ignore-line
        }

        return array_unique($types);
    }

    /**
     * Get All Routes.
     */
    private function routerList(): array
    {
        $list = [];

        foreach ($this->router->getRouteCollection()->all() as $index => $router) {
            if ($router->getDefault('_controller')) {
                [$controller, $method] = explode('::', $router->getDefault('_controller'));
                if (!class_exists($controller)) {
                    continue;
                }

                $list[$index.'::'.$router->getPath()] = [
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
