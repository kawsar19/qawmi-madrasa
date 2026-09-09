<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Support\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * `php artisan storage:link-tenants` — প্রতি মাদরাসার ফাইল ফোল্ডারের symlink।
 *
 * FilesystemTenancyBootstrapper writes uploads to
 * storage/tenant{id}/app/public, which the stock public/storage symlink
 * (pointing at storage/app/public) cannot reach — so uploaded logos and
 * banners 404. This creates public/storage/tenants/{id} for each tenant,
 * which is the path App\Support\Media builds URLs against.
 *
 * Only needed on the local/public disk. Once MEDIA_DISK is r2, files are
 * served by the CDN and this command becomes a no-op.
 */
class LinkTenantStorage extends Command
{
    protected $signature = 'storage:link-tenants {--force : বিদ্যমান লিংক মুছে আবার তৈরি করে}';

    protected $description = 'প্রতিটি মাদরাসার storage ফোল্ডারের symlink তৈরি করে';

    public function handle(): int
    {
        if (Media::disk() !== 'public') {
            $this->info('MEDIA_DISK লোকাল নয় — symlink লাগবে না।');

            return self::SUCCESS;
        }

        $base = public_path('storage/tenants');

        File::ensureDirectoryExists($base);

        $created = 0;
        $skipped = 0;

        /** @var list<Tenant> $tenants */
        $tenants = Tenant::query()->get()->all();

        foreach ($tenants as $tenant) {
            $link = $base.'/'.$tenant->getTenantKey();
            $target = Media::prepareTenantStorage($tenant->getTenantKey());

            if (is_link($link) || file_exists($link)) {
                if (! $this->option('force')) {
                    $skipped++;

                    continue;
                }

                File::delete($link);
            }

            File::link($target, $link);
            $created++;

            $this->line("  ✓ {$tenant->slug} → storage/tenants/{$tenant->getTenantKey()}");
        }

        $this->info("লিংক তৈরি: {$created}টি".($skipped > 0 ? ", আগে থেকেই ছিল: {$skipped}টি" : ''));

        return self::SUCCESS;
    }
}
