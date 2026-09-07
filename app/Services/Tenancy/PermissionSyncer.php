<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Central\Tenant;
use App\Support\PermissionRegistry;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * PermissionRegistry-কে ডাটাবেসের সাথে মেলায়।
 *
 * Lives in a service (not the console command) so provisioning can call it
 * from HTTP requests and tests, where console I/O is unavailable.
 */
class PermissionSyncer
{
    /**
     * @return array{permissions: int, roles: int}
     */
    public function sync(Tenant $tenant, bool $prune = false): array
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->setPermissionsTeamId($tenant->getTenantKey());

        $defined = PermissionRegistry::all();

        foreach ($defined as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $roles = PermissionRegistry::roles();

        foreach ($roles as $slug => $definition) {
            Role::findOrCreate($slug, 'web')
                ->syncPermissions($this->expand($definition['permissions'], $defined));
        }

        if ($prune) {
            Permission::query()->whereNotIn('name', $defined)->delete();
        }

        $registrar->forgetCachedPermissions();

        return ['permissions' => count($defined), 'roles' => count($roles)];
    }

    /**
     * '*' ও 'prefix.*' প্যাটার্ন প্রকৃত permission নামে রূপান্তর করে।
     *
     * @param  list<string>  $patterns
     * @param  list<string>  $defined
     * @return list<string>
     */
    private function expand(array $patterns, array $defined): array
    {
        $resolved = [];

        foreach ($patterns as $pattern) {
            if ($pattern === '*') {
                return $defined;
            }

            if (str_ends_with($pattern, '.*')) {
                $prefix = substr($pattern, 0, -1); // keep the trailing dot

                foreach ($defined as $name) {
                    if (str_starts_with($name, $prefix)) {
                        $resolved[] = $name;
                    }
                }

                continue;
            }

            if (in_array($pattern, $defined, true)) {
                $resolved[] = $pattern;
            }
        }

        return array_values(array_unique($resolved));
    }
}
