<?php

namespace Package\ApiBundle\Thor\Generator;

use Package\ApiBundle\Response\ApiResourceInterface;

class TypeScriptHelper
{
    public function renderAttributes(array $data): string
    {
        $attrs = [];

        // Attr
        foreach ($data['routerAttr'] as $key => $value) {
            if (str_starts_with($value, '?')) {
                $key .= '?';
            }
            $attrs[] = $key.': '.$this->convertTsType(str_replace('?', '', $value));
        }

        // Query
        if ($data['query']) {
            $attrs[] = 'query?: '.ucfirst($data['shortName']).'Query';
        }

        // DTO/Post
        if ($data['request']) {
            $attrs[] = 'request?: '.ucfirst($data['shortName']).'Request';
        }

        usort($attrs, static function ($a, $b) {
            if (str_contains($a, '?:') === str_contains($b, '?:')) {
                return 0;
            }

            return str_contains($a, '?:') ? 1 : -1;
        });

        // Append Axios Configurator
        $attrs[] = 'config: AxiosRequestConfig = {}';

        return implode(', ', $attrs);
    }

    public function renderEndpointPath(string $path, string $attributes): string
    {
        if (($qs = str_contains($attributes, 'query')) || str_contains($path, '{')) {
            $path = str_replace('{', '${', $path);
            $qs = $qs ? '${toQueryString(query)}' : '';

            $newPath = "`{$path}{$qs}`";
        } else {
            $newPath = "'$path'";
        }

        return $newPath;
    }

    public function renderVariables(array $attributes, int $sub = 1, bool $parent = false): string|array|null
    {
        $attrs = [];
        $allNull = true;

        foreach ($attributes as $key => $value) {
            $isNull = false;

            if (is_array($value)) {
                $r = $this->renderVariables($value, $sub + 1, true);
                if (!$r['items']) {
                    continue;
                }
                if ($r['nullable']) {
                    $isNull = true;
                }
                if (array_is_list($value)) {
                    $value = "[\n".implode(",\n", $r['items'])."\n".str_repeat('  ', $sub).']';
                } else {
                    $value = "{\n".implode(",\n", $r['items'])."\n".str_repeat('  ', $sub).'}';
                }
            } else {
                $isNull = str_contains($value, '?');
                if (!$isNull) {
                    $allNull = false;
                }

                $value = implode(
                    ' | ',
                    array_unique(
                        array_map(function ($item) {
                            return $this->convertTsType(str_replace('?', '', $item));
                        }, explode('|', explode(';', is_bool($value) & !$value ? '0' : $value)[0]))
                    )
                );
            }

            if (is_array($attributes) && is_int($key)) {
                $attrs[] = str_repeat('  ', $sub).$value;
            } else {
                $attrs[] = str_repeat('  ', $sub).$key.($isNull ? '?: ' : ': ').$value;
            }

            usort($attrs, static function ($a, $b) {
                if (str_contains($a, '?:') === str_contains($b, '?:')) {
                    return 0;
                }

                return str_contains($a, '?:') ? 1 : -1;
            });
        }

        if ($parent) {
            return [
                'nullable' => $allNull,
                'items' => $attrs ?: null,
            ];
        }

        return $attrs ? implode(",\n", $attrs) : null;
    }

    private function convertTsType(string $type): string
    {
        if (class_exists($type) && in_array(ApiResourceInterface::class, class_implements($type), true)) {
            return self::baseClass($type);
        }
        if (enum_exists($type)) {
            return self::baseClass($type);
        }
        if (class_exists($type)) {
            $type = 'string';
        }

        // Convert Array
        if (str_starts_with($type, '[')) {
            return sprintf("Array<'%s'", str_replace('[', '', $type));
        }
        if (str_ends_with($type, ']')) {
            return sprintf("'%s'>", str_replace(']', '', $type));
        }

        return match ($type) {
            'int' => 'number',
            'bool', 'boolean' => 'boolean',
            'string' => 'string',
            'null' => 'null',
            'any' => 'any',
            'array' => 'Array<string|number|boolean>',
            '1' => 'true',
            '0' => 'false',
            default => "'$type'"
        };
    }

    public function renderEnum(array $data): array
    {
        $enums = [];

        foreach ($data as $item) {
            if (is_array($item)) {
                array_walk_recursive($item, function ($val) use (&$enums) {
                    $items = explode(';', $val);
                    foreach ($items as $el) {
                        if (enum_exists($el)) {
                            $enums[] = self::baseClass($el);
                        }
                    }
                });

                continue;
            }

            $items = explode(';', $item);
            foreach ($items as $el) {
                if (enum_exists($el)) {
                    $enums[] = self::baseClass($el);
                }
            }
        }

        return $enums;
    }

    /**
     * Extract Class Name.
     */
    public static function baseClass(string|object|null $class): string|null
    {
        return $class ? basename(str_replace('\\', '/', is_object($class) ? get_class($class) : $class)) : null;
    }
}
