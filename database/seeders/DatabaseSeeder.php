<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioner;
use Illuminate\Database\Seeder;

/**
 * ডেভেলপমেন্ট ডেটা — একজন সুপার অ্যাডমিন, প্ল্যান, ও একটি নমুনা মাদরাসা।
 *
 * Deliberately does NOT use User::factory() alone: users are unique per
 * (tenant_id, email), and a madrasa's users must be created inside a
 * provisioned tenant rather than floating with a NULL tenant_id.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PlanSeeder::class);

        // সুপার অ্যাডমিন — tenant_id NULL.
        User::query()->updateOrCreate(
            ['tenant_id' => null, 'email' => 'super@example.com'],
            [
                'name' => 'সুপার অ্যাডমিন',
                'password' => 'password',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if (app()->environment('production')) {
            return;
        }

        $this->seedDemoTenant();
    }

    private function seedDemoTenant(): void
    {
        $slug = 'darululum';

        if (Tenant::where('slug', $slug)->exists()) {
            $this->command->warn("  '{$slug}' আগেই আছে — বাদ দেওয়া হলো।");

            return;
        }

        $tenant = app(TenantProvisioner::class)->provision(
            [
                'name' => 'জামিয়া দারুল উলুম',
                'slug' => $slug,
                'madrasa_type' => 'kitab',
                'address' => 'হাটহাজারী, চট্টগ্রাম',
            ],
            $slug.'.'.config('tenancy.central_domains')[0],
            [
                'name' => 'মুহতামিম সাহেব',
                'email' => 'admin@darululum.test',
                'password' => 'password',
                'mobile' => '01712345678',
            ],
        );

        Subscription::create([
            'tenant_id' => $tenant->getKey(),
            'plan_id' => Plan::where('slug', 'standard')->value('id'),
            'starts_at' => now()->toDateString(),
            'ends_at' => now()->addYear()->toDateString(),
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $this->command->info("  ✓ নমুনা মাদরাসা: http://{$tenant->domains()->first()->domain}/panel");
    }
}
