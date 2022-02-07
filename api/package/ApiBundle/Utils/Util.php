<?php

namespace Package\ApiBundle\Utils;

use Symfony\Component\Finder\Finder;

/**
 * General Static Methods
 */
class Util
{
    /**
     * Get Project Root Directory.
     */
    public static function rootDir(string $path = ''): string
    {
        return \dirname(__DIR__, 3).'/'.$path;
    }

    /**
     * Get Project Root Directory.
     */
    public static function srcDir(string $path = ''): string
    {
        return \dirname(__DIR__, 3).'/src/'.$path;
    }

    /**
     * Extract Class Name
     */
    public static function baseClass(string|object|null $class): string|null
    {
        return $class ? basename(str_replace('\\', '/', is_object($class) ? get_class($class) : $class)) : null;
    }

    /**
     * Read All Attributes src Directory
     */
    public static function findAttributes(array $attributeClass, ?string $findDir = null, array $excludeDirs = ['DependencyInjection', 'Repository', 'Entity']): array
    {
        $attributes = [];

        // Find All PHP Files
        $files = Finder::create()
            ->in($findDir ?? self::srcDir())
            ->exclude($excludeDirs)
            ->files()
            ->name('*.php');

        foreach ($files as $file) {
            $reflection = new \ReflectionClass(
                'App\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname())
            );

            // Class Attribute
            if ($reflection->getAttributes()) {
                foreach ($reflection->getAttributes() as $attribute) {
                    if (in_array($attribute->getName(), $attributeClass, true)) {
                        $attributes[$reflection->name][] = $attribute;
                    }
                }
            }

            // Method Attribute
            foreach ($reflection->getMethods() as $method) {
                foreach ($method->getAttributes() as $attribute) {
                    if (in_array($attribute->getName(), $attributeClass, true)) {
                        $attributes[$method->class][$method->getName()][$attribute->getName()] = $attribute;
                    }
                }
            }
        }

        return $attributes;
    }
}