<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Support\Media;
use Stancl\Tenancy\Events\TenantCreated;

/**
 * নতুন মাদরাসার storage ফোল্ডারগুলো তৈরি করে।
 *
 * Without this the tenant's storage root holds nothing, so the first request
 * that writes a real-time facade or a compiled view fails — see
 * Media::prepareTenantStorage() for why that surfaces as a 500.
 */
class PrepareTenantStorage
{
    public function handle(TenantCreated $event): void
    {
        Media::prepareTenantStorage($event->tenant->getTenantKey());
    }
}
