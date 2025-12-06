<?php

namespace App\Admin\Core\Service;

use App\Admin\Core\Permission\Types\PermissionInterface;
use App\Admin\Core\Permission\UserType;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Finder\Finder;

/**
 * Permission Finder.
 */
class PermissionManager
{
    private array $cache = [];

    public function __construct(private readonly array $permissions)
    {
    }

    public static function findPermission(ContainerBuilder $container): array
    {
        $permissions = [];
        $finder = new Finder();
        $finder->in($container->getParameter('kernel.project_dir').'/src/')
            ->files()
            ->name('*Permission.php')
            ->notName('*Interface*');

        foreach ($finder as $file) {
            $enum = pathinfo(str_replace([$container->getParameter('kernel.project_dir'), '/src', '/'], ['', 'App', '\\'], $file->getRealPath()), PATHINFO_FILENAME);
            if (is_subclass_of($enum, PermissionInterface::class)) {
                $extractType = str_replace(['PermissionInterface', '\\'], ['', '/'], array_values(class_implements($enum, false))[0]);
                $enumBasename = basename(str_replace('\\', '/', $enum));
                $permissions[strtoupper(basename($extractType))][$enumBasename] = $enum::cases();
            }
        }

        return $permissions;
    }

    public function getPermissions(?UserType $type = null): array
    {
        $cacheKey = 'perms_'.($type->name ?? 'all');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $result = $type ? ($this->permissions[$type->name] ?? []) : $this->permissions;
        $this->cache[$cacheKey] = $result;

        return $result;
    }

    public function getPermissionsValues(?UserType $type = null): array
    {
        $cacheKey = 'perms_values_'.($type->name ?? 'all');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $p = $this->getPermissions($type);
        array_walk_recursive($p, static fn (&$enum) => $enum = $enum->value);

        $this->cache[$cacheKey] = $p;

        return $p;
    }

    public function getPermissionsFlatten(?UserType $type = null): array
    {
        $cacheKey = 'perms_flatten_'.($type->name ?? 'all');

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        if ($type) {
            $result = array_merge(...array_values($this->getPermissionsValues($type)));
        } else {
            $result = array_map(static fn ($p) => array_merge(...array_values($p)), $this->getPermissionsValues());
        }

        $this->cache[$cacheKey] = $result;

        return $result;
    }

    public function getPermissionToEnum(array $permissions): array
    {
        $permissionAll = $this->getPermissions();
        $selectedPerms = [];

        array_walk_recursive($permissionAll, static function (PermissionInterface $p) use (&$selectedPerms, $permissions) {
            if (in_array($p->value, $permissions, true)) {
                $selectedPerms[] = $p;
            }
        });

        return $selectedPerms;
    }
}
