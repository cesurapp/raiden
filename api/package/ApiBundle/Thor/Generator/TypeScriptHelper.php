<?php

namespace Package\ApiBundle\Thor\Generator;

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
        $newPath = '';

        if (($qs = str_contains($attributes, 'query')) || str_contains($path, '{')) {
            $path = str_replace('{', '${', $path);
            $qs = $qs ? '?${toQueryString(query)}' : '';

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
                $value = implode('|', array_unique(array_map(function ($item) {
                    return $this->convertTsType(str_replace('?', '', $item));
                }, explode('|', explode(';', $value)[0]))));
            }

            if (is_array($attributes[$key]) && is_int($key)) {
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
        if (class_exists($type)) {
            $type = 'string';
        }
        return match ($type) {
            'int' => 'number',
            'bool', 'boolean' => 'boolean',
            'string' => 'string',
            'null' => 'null',
            'array' => 'Array<string|number|boolean>',
            default => "'$type'"
        };
    }
}
