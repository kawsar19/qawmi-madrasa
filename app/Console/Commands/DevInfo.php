<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

/**
 * `php artisan dev:info` — সব লগইন লিংক ও ডেমো তথ্য এক জায়গায়।
 *
 * Saves hunting through the database for "which URL do I log in at again?".
 */
class DevInfo extends Command
{
    protected $signature = 'dev:info {--port=8000 : ডেভ সার্ভারের পোর্ট}';

    protected $description = 'ডেভেলপমেন্টের লগইন লিংক ও ডেমো তথ্য দেখায়';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('এই কমান্ড শুধু ডেভেলপমেন্টে চলে।');

            return self::FAILURE;
        }

        $port = (string) $this->option('port');
        $suffix = $port === '80' ? '' : ':'.$port;
        $central = config('tenancy.central_domains')[0];

        $this->newLine();
        $this->info('সুপার অ্যাডমিন');
        $this->line("  http://{$central}{$suffix}/login");

        foreach (User::query()->whereNull('tenant_id')->get() as $user) {
            $this->line("  {$user->email}");
        }

        /** @var list<Tenant> $tenants */
        $tenants = Tenant::with('domains')->get()->all();

        if ($tenants === []) {
            $this->newLine();
            $this->warn('কোনো মাদরাসা নেই। `php artisan migrate:fresh --seed` চালান।');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('মাদরাসা');

        foreach ($tenants as $tenant) {
            // firstWhere() on the relation is typed against stancl's Domain
            // contract, which does not declare $domain; our model does.
            $primary = $tenant->domains->firstWhere('is_primary', true)
                ?? $tenant->domains->first();

            $domain = $primary instanceof Domain ? $primary->domain : null;

            if ($domain === null) {
                continue;
            }

            $this->line("  <options=bold>{$tenant->name}</> [{$tenant->status}]");
            $this->line("    http://{$domain}{$suffix}/panel/login");
            $this->listTenantUsers($tenant);
        }

        $this->newLine();
        $this->comment('ডেমো পাসওয়ার্ড: password');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * মাদরাসার সব লগইন অ্যাকাউন্ট, রোল সহ।
     *
     * Roles are read inside the tenant context: spatie/permission scopes its
     * pivot rows by team id, which SetPermissionsTeam sets per request but
     * no middleware runs in a console command.
     */
    private function listTenantUsers(Tenant $tenant): void
    {
        tenancy()->initialize($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());

        /** @var list<User> $users */
        $users = User::query()
            ->where('tenant_id', $tenant->getKey())
            ->with('roles')
            ->orderBy('id')
            ->get()
            ->all();

        tenancy()->end();

        if ($users === []) {
            $this->line('    কোনো ইউজার নেই');

            return;
        }

        foreach ($users as $user) {
            $role = $user->getRoleNames()->first();

            $this->line('    '.$user->email.($role === null ? '' : "  <fg=gray>[{$role}]</>"));
        }
    }
}
