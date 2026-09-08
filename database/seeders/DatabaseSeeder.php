<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\People\Employee;
use App\Models\User;
use App\Services\People\EmployeeRegistrar;
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

    /**
     * নমুনা মাদরাসা। সব ডেমো পাসওয়ার্ড 'password' — লগইন পেজের ডেমো বক্স
     * এটাই ধরে নেয়।
     */
    private function seedDemoTenant(): void
    {
        $demos = [
            ['জামিয়া দারুল উলুম', 'darululum', 'হাটহাজারী, চট্টগ্রাম', 'kitab_madrasa', 'kitab'],
            ['জামিয়া হিফজুল কুরআন', 'hifzul-quran', 'সাভার, ঢাকা', 'hifz_madrasa', 'hifz'],
        ];

        foreach ($demos as [$name, $slug, $address, $preset, $type]) {
            if (Tenant::where('slug', $slug)->exists()) {
                $this->command->warn("  '{$slug}' আগেই আছে — বাদ দেওয়া হলো।");

                continue;
            }

            $tenant = app(TenantProvisioner::class)->provision(
                [
                    'name' => $name,
                    'slug' => $slug,
                    'madrasa_type' => $type,
                    'address' => $address,
                ],
                $slug.'.'.config('tenancy.central_domains')[0],
                [
                    'name' => 'মুহতামিম সাহেব',
                    'email' => "admin@{$slug}.test",
                    'password' => 'password',
                    'mobile' => '01712345678',
                ],
                $preset,
            );

            Subscription::create([
                'tenant_id' => $tenant->getKey(),
                'plan_id' => Plan::where('slug', 'standard')->value('id'),
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addYear()->toDateString(),
                'status' => Subscription::STATUS_ACTIVE,
            ]);

            $this->seedDemoEmployees($tenant, $slug);

            $this->command->info("  ✓ {$name} — http://{$tenant->domains()->first()->domain}/panel");
            $this->command->line("     admin@{$slug}.test / password");
        }
    }

    /**
     * নমুনা শিক্ষক ও কর্মচারী। উস্তাদ একজন লগইন পান, বাকিরা নন —
     * বাস্তবেও বাবুর্চি বা দারোয়ানের প্যানেলে ঢোকার দরকার হয় না।
     */
    private function seedDemoEmployees(Tenant $tenant, string $slug): void
    {
        // Employees are tenant-scoped rows, so they must be written inside the
        // tenant context or the global scope has nothing to stamp them with.
        tenancy()->initialize($tenant);

        $registrar = app(EmployeeRegistrar::class);

        $staff = [
            ['মাওলানা আব্দুল করিম', 'ustad', Employee::TYPE_TEACHER, 15000, 'দাওরায়ে হাদিস', 'ustad'],
            ['মাওলানা ইব্রাহিম খলিল', 'ustad', Employee::TYPE_TEACHER, 14000, 'দাওরায়ে হাদিস', null],
            ['হাফেজ মুহাম্মদ ইউসুফ', 'hafez', Employee::TYPE_TEACHER, 12000, 'হিফজুল কুরআন', null],
            ['ক্বারী আব্দুল্লাহ', 'qari', Employee::TYPE_TEACHER, 12500, 'ক্বিরাআত', null],
            ['মুহাম্মদ শফিক', 'muhasib', Employee::TYPE_STAFF, 10000, 'বি.কম', 'hisab_rokkhok'],
            ['আব্দুস সালাম', 'baburchi', Employee::TYPE_STAFF, 8000, null, null],
            ['নূর মোহাম্মদ', 'daroan', Employee::TYPE_STAFF, 7500, null, null],
        ];

        foreach ($staff as $i => [$name, $designation, $type, $salary, $qualification, $role]) {
            $registrar->register(
                [
                    'name' => $name,
                    'designation' => $designation,
                    'type' => $type,
                    'monthly_salary' => $salary,
                    'qualification' => $qualification,
                    'mobile' => '018'.str_pad((string) (10000000 + $i), 8, '0', STR_PAD_LEFT),
                    'joined_on' => now()->subYears(2)->toDateString(),
                ],
                // Only the roles that actually use the panel get an account.
                $role === null ? null : [
                    'email' => "{$role}@{$slug}.test",
                    'password' => 'password',
                    'role' => $role,
                ],
            );
        }

        tenancy()->end();

        $this->command->line("     ustad@{$slug}.test, hisab_rokkhok@{$slug}.test / password");
    }
}
