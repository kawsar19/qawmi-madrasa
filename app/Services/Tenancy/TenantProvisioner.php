<?php

declare(strict_types=1);

namespace App\Services\Tenancy;

use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\Central\Domain;
use App\Models\Central\Tenant;
use App\Models\Exam\GradeScale;
use App\Models\Finance\DonationKhat;
use App\Models\Finance\FeeHead;
use App\Models\Finance\LedgerAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

/**
 * নতুন মাদরাসা (tenant) সম্পূর্ণ প্রস্তুত করে।
 *
 * Creates the tenant, its domain, roles/permissions, reference data
 * (marhalas, kitabs, grades, fee heads, khats, ledger) and the first admin
 * user — all in one transaction, so a failure leaves no half-built tenant.
 *
 * Reference data is COPIED into the tenant rather than shared, so each
 * madrasa can edit its own curriculum freely.
 */
class TenantProvisioner
{
    /**
     * @param  array{name: string, slug: string, eiin?: string|null, madrasa_type?: string, address?: string|null, status?: string}  $attributes
     * @param  array{name: string, email: string, password: string, mobile?: string|null}  $admin
     * @param  'kitab_madrasa'|'hifz_madrasa'  $preset
     */
    public function provision(
        array $attributes,
        string $domain,
        array $admin,
        string $preset = 'kitab_madrasa',
    ): Tenant {
        return DB::transaction(function () use ($attributes, $domain, $admin, $preset): Tenant {
            $tenant = Tenant::create([
                'status' => Tenant::STATUS_ACTIVE,
                ...$attributes,
            ]);

            $tenant->domains()->create([
                'domain' => $domain,
                'is_primary' => true,
                'type' => $this->domainType($domain),
            ]);

            // Everything below writes tenant-scoped rows, so run it inside the
            // tenant context and restore the previous one afterwards.
            tenancy()->initialize($tenant);

            $this->syncPermissions($tenant);
            $this->seedReferenceData($preset);
            $adminUser = $this->createAdminUser($tenant, $admin);

            tenancy()->end();

            $tenant->setRelation('adminUser', $adminUser);

            return $tenant;
        });
    }

    /**
     * ডোমেইনটি আমাদের subdomain না ক্লায়েন্টের নিজস্ব ডোমেইন।
     *
     * A hostname ending in one of the configured central domains is a
     * subdomain we issued (madrasa.app.example.com); anything else is a
     * custom domain the madrasa owns (madrasa.edu.bd).
     */
    private function domainType(string $domain): string
    {
        foreach (config('tenancy.central_domains') as $central) {
            if (str_ends_with($domain, '.'.$central)) {
                return Domain::TYPE_SUBDOMAIN;
            }
        }

        return Domain::TYPE_CUSTOM;
    }

    private function syncPermissions(Tenant $tenant): void
    {
        app(PermissionSyncer::class)->sync($tenant);
        app(PermissionRegistrar::class)->setPermissionsTeamId($tenant->getTenantKey());
    }

    private function seedReferenceData(string $preset): void
    {
        $marhalas = $this->load('marhalas');
        $kitabs = $this->load('kitabs');

        // A hifz-only madrasa should not be handed the full kitab syllabus.
        if ($preset === 'hifz_madrasa') {
            $hifzTracks = ['hifz', 'nazera', 'qirat'];
            $marhalas = array_values(array_filter(
                $marhalas,
                static fn (array $m): bool => in_array($m['track'], $hifzTracks, true)
            ));
        }

        $marhalaIdByCode = [];

        foreach ($marhalas as $row) {
            $marhala = Marhala::create($row);
            $marhalaIdByCode[$row['code']] = $marhala->id;
        }

        foreach ($kitabs as $row) {
            $marhalaCode = $row['marhala_code'] ?? null;
            unset($row['marhala_code']);

            // Skip kitabs whose marhala was filtered out by the preset.
            if ($marhalaCode !== null && ! isset($marhalaIdByCode[$marhalaCode])) {
                continue;
            }

            Kitab::create([
                ...$row,
                'marhala_id' => $marhalaCode === null ? null : $marhalaIdByCode[$marhalaCode],
            ]);
        }

        foreach ($this->load('grade_scales') as $row) {
            GradeScale::create($row);
        }

        foreach ($this->load('fee_heads') as $row) {
            FeeHead::create($row);
        }

        foreach ($this->load('donation_khats') as $row) {
            DonationKhat::create($row);
        }

        foreach ($this->load('ledger_accounts') as $row) {
            LedgerAccount::create($row);
        }
    }

    /**
     * @param  array{name: string, email: string, password: string, mobile?: string|null}  $admin
     */
    private function createAdminUser(Tenant $tenant, array $admin): User
    {
        $user = User::create([
            'tenant_id' => $tenant->getTenantKey(),
            'name' => $admin['name'],
            'email' => $admin['email'],
            'mobile' => $admin['mobile'] ?? null,
            'password' => $admin['password'],
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $user->assignRole('madrasa_admin');

        return $user;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function load(string $file): array
    {
        $path = database_path("seeders/data/{$file}.php");

        if (! file_exists($path)) {
            return [];
        }

        /** @var list<array<string, mixed>> $data */
        $data = require $path;

        return $data;
    }
}
