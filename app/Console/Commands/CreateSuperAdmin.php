<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * `php artisan admin:create` — একটি সুপার অ্যাডমিন অ্যাকাউন্ট তৈরি করে।
 *
 * DatabaseSeeder returns early on production, deliberately, so a freshly
 * migrated production database has no way in at all. This is that way in,
 * without seeding the demo madrasas alongside it.
 *
 * tenant_id stays NULL — that is what makes a user a super admin, and it is
 * why User does not use BelongsToTenant.
 */
class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create
        {--email= : ইমেইল (না দিলে জিজ্ঞেস করা হবে)}
        {--name= : নাম}
        {--password= : পাসওয়ার্ড (না দিলে জিজ্ঞেস করা হবে)}
        {--if-missing : অ্যাকাউন্টটি আগে থেকে থাকলে চুপচাপ সফল হয়ে ফেরে}';

    protected $description = 'একজন সুপার অ্যাডমিন তৈরি করে';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('ইমেইল');
        $name = $this->option('name') ?: $this->ask('নাম', 'সুপার অ্যাডমিন');

        // Deploy platforms without a shell (Render's free plan, for one) can
        // only run this from the boot script, which runs on every deploy.
        // Without this the second deploy would fail validation on the email
        // and take the whole boot down with it.
        if ($this->option('if-missing')
            && User::query()->whereNull('tenant_id')->where('email', $email)->exists()) {
            $this->line("সুপার অ্যাডমিন '{$email}' আগেই আছে — বাদ দেওয়া হলো।");

            return self::SUCCESS;
        }

        // secret() hides the typing; on a non-interactive boot the option
        // carries it instead.
        $password = $this->option('password') ?: $this->secret('পাসওয়ার্ড');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'name' => ['required', 'string', 'max:255'],
                // Scoped to tenant_id IS NULL by hand: the unique index is on
                // (tenant_id, email), so an unscoped rule would reject an
                // address a madrasa already uses for its own admin.
                'email' => [
                    'required', 'email', 'max:255',
                    Rule::unique('users', 'email')->whereNull('tenant_id'),
                ],
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create([
            'tenant_id' => null,
            'name' => $name,
            'email' => $email,
            // The 'hashed' cast on User hashes this; Hash::make here would
            // double-hash it and no password would ever match.
            'password' => $password,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $this->newLine();
        $this->info('✓ সুপার অ্যাডমিন তৈরি হয়েছে।');
        $this->line("  ইমেইল: {$email}");
        $this->line('  লগইন: https://'.config('tenancy.central_domains')[0].'/login');
        $this->newLine();

        return self::SUCCESS;
    }
}
