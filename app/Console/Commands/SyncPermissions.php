<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\Tenancy\PermissionSyncer;
use Illuminate\Console\Command;

/**
 * `php artisan permissions:sync` — PermissionRegistry থেকে DB সিঙ্ক করে।
 *
 * The actual work lives in PermissionSyncer so it can also run outside the
 * console (provisioning, tests).
 */
class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync
                            {--tenant= : নির্দিষ্ট tenant id (না দিলে সব tenant)}
                            {--prune : রেজিস্ট্রিতে নেই এমন permission মুছে ফেলবে}';

    protected $description = 'PermissionRegistry অনুসারে permission ও role সিঙ্ক করে';

    public function handle(PermissionSyncer $syncer): int
    {
        $query = Tenant::query();

        if ($this->option('tenant') !== null) {
            $query->whereKey($this->option('tenant'));
        }

        /** @var list<Tenant> $tenants */
        $tenants = $query->get()->all();

        if ($tenants === []) {
            $this->warn('কোনো tenant পাওয়া যায়নি।');

            return self::SUCCESS;
        }

        foreach ($tenants as $tenant) {
            $result = $syncer->sync($tenant, (bool) $this->option('prune'));

            $this->line(sprintf(
                '  <info>%s</info> — %d permission, %d role',
                $tenant->name,
                $result['permissions'],
                $result['roles'],
            ));
        }

        $this->info('সিঙ্ক সম্পন্ন হয়েছে।');

        return self::SUCCESS;
    }
}
