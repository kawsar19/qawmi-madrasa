<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Throwable;

/**
 * `php artisan tenant:create` — নতুন মাদরাসা তৈরি করে।
 */
class CreateTenant extends Command
{
    protected $signature = 'tenant:create
                            {--name= : মাদরাসার নাম}
                            {--slug= : ইউনিক slug}
                            {--domain= : পূর্ণ ডোমেইন (madrasa.app.localhost)}
                            {--admin-name= : অ্যাডমিনের নাম}
                            {--admin-email= : অ্যাডমিনের ইমেইল}
                            {--admin-password= : অ্যাডমিনের পাসওয়ার্ড}
                            {--preset=kitab_madrasa : kitab_madrasa | hifz_madrasa}';

    protected $description = 'নতুন মাদরাসা (tenant) তৈরি ও প্রস্তুত করে';

    public function handle(TenantProvisioner $provisioner): int
    {
        $name = $this->option('name') ?? $this->ask('মাদরাসার নাম');
        $slug = $this->option('slug') ?? Str::slug((string) $this->ask('Slug (ইংরেজি)', Str::slug($name)));
        $domain = $this->option('domain') ?? $this->ask('ডোমেইন', $slug.'.'.config('tenancy.central_domains')[0]);

        $adminName = $this->option('admin-name') ?? $this->ask('অ্যাডমিনের নাম', 'মুহতামিম');
        $adminEmail = $this->option('admin-email') ?? $this->ask('অ্যাডমিনের ইমেইল');
        $adminPassword = $this->option('admin-password') ?? $this->secret('অ্যাডমিনের পাসওয়ার্ড');

        $preset = (string) $this->option('preset');

        if (! in_array($preset, ['kitab_madrasa', 'hifz_madrasa'], true)) {
            $this->error('preset হতে হবে kitab_madrasa অথবা hifz_madrasa।');

            return self::FAILURE;
        }

        if (Tenant::where('slug', $slug)->exists()) {
            $this->error("'{$slug}' slug আগে থেকেই আছে।");

            return self::FAILURE;
        }

        try {
            $tenant = $provisioner->provision(
                ['name' => $name, 'slug' => $slug],
                $domain,
                [
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => $adminPassword,
                ],
                $preset,
            );
        } catch (Throwable $e) {
            $this->error('তৈরি ব্যর্থ: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("✓ মাদরাসা তৈরি হয়েছে — {$tenant->name} (id: {$tenant->id})");
        $this->line("  ডোমেইন : http://{$domain}");
        $this->line("  প্যানেল  : http://{$domain}/panel");
        $this->line("  লগইন   : {$adminEmail}");

        return self::SUCCESS;
    }
}
