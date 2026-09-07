<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Central\Tenant;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

/**
 * যে মডেলে এই trait আছে, তার প্রতিটি query স্বয়ংক্রিয়ভাবে চলতি tenant-এ সীমাবদ্ধ।
 *
 * Two responsibilities:
 *  1. Apply TenantScope so reads never cross tenants.
 *  2. Stamp tenant_id on create so writes cannot land in the wrong tenant.
 *
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model): void {
            $column = $model->getTenantIdColumn();

            if ($model->getAttribute($column) !== null) {
                return;
            }

            if (! tenancy()->initialized) {
                throw new RuntimeException(sprintf(
                    'Cannot create [%s] without a tenant context. Either initialize '
                    .'tenancy or set %s explicitly.',
                    $model::class,
                    $column
                ));
            }

            $model->setAttribute($column, tenant()->getTenantKey());
        });
    }

    public function getTenantIdColumn(): string
    {
        return 'tenant_id';
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, $this->getTenantIdColumn());
    }

    /**
     * Escape hatch for super-admin / console work that must span tenants.
     * Use sparingly and never in tenant-facing request code.
     *
     * @return Builder<static>
     */
    public static function withoutTenantScope(): Builder
    {
        return static::query()->withoutGlobalScope(TenantScope::class);
    }
}
