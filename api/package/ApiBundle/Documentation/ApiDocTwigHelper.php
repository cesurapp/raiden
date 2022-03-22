<?php

namespace Package\ApiBundle\Documentation;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class ApiDocTwigHelper extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('ucfirst', [$this, 'ucfirst']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('renderAttibutes', [$this, 'renderAttibutes']),
            new TwigFunction('renderEndpointPath', [$this, 'renderEndpointPath']),
            new TwigFunction('renderQueryAttributes', [$this, 'renderQueryAttributes']),
        ];
    }

    public function ucfirst(string $text): string
    {
        return ucfirst($text);
    }

    public function renderAttibutes(array $data): string
    {
        $attrs = [];

        // Attr
        foreach ($data['endpointAttr'] as $key => $value) {
            if (str_starts_with($value, '?')) {
                $key .= '?';
            }
            $attrs[] = $key.': '.$this->convertTsType(str_replace('?', '', $value));
        }

        // Query
        if ($data['get']) {
            $attrs[] = 'query?: '.ucfirst($data['shortName']).'Query';
        }

        // DTO/Post
        if ($data['post']) {
            $attrs[] = 'request?: '.ucfirst($data['shortName']).'Request';
        }

        return implode(', ', $attrs);
    }

    public function renderEndpointPath(string $path): string
    {
        $isVars = str_contains($path, '{');
        if ($isVars) {
            $path = str_replace('{', '${', $path);
            return "`$path`";
        }

        return "'$path'";
    }

    public function renderQueryAttributes(array $attributes, int $sub = 1, bool $parent = false): string|array|null
    {
        $attrs = [];
        $allNull = true;

        foreach ($attributes as $key => $value) {
            $isNull = false;

            if (is_array($value)) {
                $r = $this->renderQueryAttributes($value, $sub + 1, true);
                if (!$r['items']) {
                    continue;
                }
                if ($r['nullable']) {
                    $isNull = true;
                }
                $value = "{\n".$r['items']."\n".str_repeat('    ', $sub).'}';
            } else {
                $isNull = str_contains($value, '?');
                if (!$isNull) {
                    $allNull = false;
                }
                $value = implode('|', array_unique(array_map(function ($item) {
                    return $this->convertTsType(str_replace('?', '', $item));
                }, explode('|', explode(';', $value)[0]))));
            }

            $attrs[] = str_repeat('    ', $sub).$key.($isNull ? '?: ' : ': ').$value;
        }

        if ($parent) {
            return [
                'nullable' => $allNull,
                'items' => $attrs ? implode(",\n", $attrs) : null,
            ];
        }

        return $attrs ? implode(",\n", $attrs) : null;
    }

    private function convertTsType(string $type): string
    {
        return match ($type) {
            'int' => 'number',
            'bool' => 'boolean',
            'string' => 'string',
            'null' => 'null',
            'array' => 'Array<string|number|boolean>',
            default => "'$type'"
        };
    }
}
