<?php

namespace App\Admin\Core\Permission;

use App\Admin\Core\Enum\UserType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;

/**
 * Permission Finder.
 */
class PermissionManager
{
    private array $permissions = [];

    public function __construct(private readonly ParameterBagInterface $parameterBag)
    {
    }

    public function getPermissions(?UserType $type = null): array
    {
        if (!$this->permissions) {
            $finder = new Finder();
            $finder->in($this->parameterBag->get('kernel.project_dir').'/src/')
                ->files()
                ->name('*Permission.php')
                ->notName('*Interface*');

            foreach ($finder as $file) {
                $enum = $this->pathToNamespace($file->getRealPath());
                if (is_subclass_of($enum, PermissionInterface::class)) {
                    $extractType = str_replace(['PermissionInterface', '\\'], ['', '/'], array_values(class_implements($enum, false))[0]);
                    $enumBasename = basename(str_replace('\\', '/', $enum));
                    $this->permissions[strtoupper(basename($extractType))][$enumBasename] = $enum::cases();
                }
            }
        }

        return $type ? ($this->permissions[$type->name] ?? []) : $this->permissions;
    }

    public function getPermissionsValues(?UserType $type = null): array
    {
        $p = $this->getPermissions($type);
        array_walk_recursive($p, static fn (&$enum) => $enum = $enum->value);

        return $p;
    }

    public function getPermissionsFlatten(?UserType $type = null): array
    {
        if ($type) {
            return array_merge(...array_values($this->getPermissionsValues($type)));
        }

        return array_map(static fn ($p) => array_merge(...array_values($p)), $this->getPermissionsValues());
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

    public function pathToNamespace(string $realPath): string
    {
        return pathinfo(
            str_replace([$this->parameterBag->get('kernel.project_dir'), '/src', '/'], ['', 'App', '\\'], $realPath),
            PATHINFO_FILENAME
        );
    }
}
