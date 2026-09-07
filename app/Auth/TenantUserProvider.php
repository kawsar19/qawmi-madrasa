<?php

declare(strict_types=1);

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * লগইনের সময় ইউজার খোঁজা চলতি tenant-এ সীমাবদ্ধ রাখে।
 *
 * Users are unique per (tenant_id, email), not globally — two madrasas may
 * each have an "admin@..." account. Without this scoping, Eloquent's default
 * lookup would match the first row with that email regardless of tenant, so a
 * user could sign in on another madrasa's domain.
 *
 * In the central context (tenancy not initialized) it scopes to
 * tenant_id IS NULL, i.e. super admins only.
 *
 * Implemented through the parent's query callback, which newModelQuery()
 * applies to every retrieval path (by id, by credentials, by remember token),
 * rather than by overriding each of those methods.
 */
class TenantUserProvider extends EloquentUserProvider
{
    /**
     * @param  class-string<Model>  $model
     */
    public function __construct(Hasher $hasher, string $model)
    {
        parent::__construct($hasher, $model);

        $this->withQuery(static function (Builder $query): void {
            tenancy()->initialized
                ? $query->where('tenant_id', tenant()->getTenantKey())
                : $query->whereNull('tenant_id');
        });
    }
}
