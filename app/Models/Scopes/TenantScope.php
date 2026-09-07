<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Contracts\TenantScoped;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * প্রতিটি tenant-scoped query-তে `WHERE tenant_id = ?` যোগ করে।
 *
 * This is the single mechanism that keeps madrasas from seeing each other's
 * data. It applies whenever tenancy is initialized; in the central context it
 * is a no-op, so super-admin queries can still see across tenants.
 *
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! $model instanceof TenantScoped) {
            return;
        }

        if (! tenancy()->initialized) {
            return;
        }

        $tenant = tenant();

        if ($tenant === null) {
            return;
        }

        $builder->where(
            $model->qualifyColumn($model->getTenantIdColumn()),
            $tenant->getTenantKey()
        );
    }
}
