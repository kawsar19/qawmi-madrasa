<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * যে মডেল tenant-এর মালিকানাধীন।
 *
 * Implemented via the BelongsToTenant trait. Having a real interface (rather
 * than relying on the trait alone) lets TenantScope depend on a type that
 * actually declares getTenantIdColumn().
 */
interface TenantScoped
{
    public function getTenantIdColumn(): string;
}
