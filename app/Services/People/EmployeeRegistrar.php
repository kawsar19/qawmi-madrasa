<?php

declare(strict_types=1);

namespace App\Services\People;

use App\Models\People\Employee;
use App\Models\User;
use App\Services\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

/**
 * শিক্ষক/কর্মচারী নিয়োগ — কর্মচারী ও (ঐচ্ছিক) লগইন অ্যাকাউন্ট এক ট্রানজেকশনে।
 *
 * Kept out of the Livewire component so payroll and the teacher-import flow
 * can reuse the same UID and account rules instead of duplicating them.
 */
class EmployeeRegistrar
{
    public function __construct(private DocumentNumberService $numbers) {}

    /**
     * @param  array<string, mixed>  $attributes  employees table attributes
     * @param  array{email: string, password?: string, role?: string}|null  $account  লগইন অ্যাকাউন্ট
     */
    public function register(array $attributes, ?array $account = null): Employee
    {
        return DB::transaction(function () use ($attributes, $account): Employee {
            // Race-safe: never MAX(uid)+1 — two clerks hiring at the same
            // moment would otherwise share one permanent id.
            $attributes['employee_uid'] ??= $this->numbers->nextFormatted('employee_uid', '', '', 4);

            $employee = Employee::create($attributes);

            if ($account !== null && $account['email'] !== '') {
                $this->attachAccount($employee, $account);
            }

            return $employee;
        });
    }

    /**
     * লগইন অ্যাকাউন্ট তৈরি করে কর্মচারীর সাথে যুক্ত করা।
     *
     * Users are unique per (tenant_id, email) and User does NOT use
     * BelongsToTenant, so the tenant id is set by hand here.
     *
     * @param  array{email: string, password?: string, role?: string}  $account
     */
    public function attachAccount(Employee $employee, array $account): User
    {
        $tenantId = tenant()->getTenantKey();

        $attributes = [
            'name' => $employee->name,
            'is_active' => true,
            'email_verified_at' => now(),
        ];

        // A blank password means "keep the current one". Writing the stored
        // hash back would re-hash it through the `hashed` cast and lock the
        // employee out of their own account.
        if (($account['password'] ?? '') !== '') {
            $attributes['password'] = $account['password'];
        }

        $user = User::query()->updateOrCreate(
            ['tenant_id' => $tenantId, 'email' => $account['email']],
            $attributes,
        );

        if (($account['role'] ?? '') !== '') {
            $user->syncRoles([$account['role']]);
        }

        $employee->forceFill(['user_id' => $user->getKey()])->save();

        return $user;
    }

    /**
     * অ্যাকাউন্ট বিচ্ছিন্ন করা — ইউজার রেকর্ড নিষ্ক্রিয় হয়, মুছে যায় না,
     * কারণ activity log ও নম্বর এন্ট্রি ঐ ইউজারকে রেফার করে।
     */
    public function detachAccount(Employee $employee): void
    {
        $user = $employee->user;

        if ($user === null) {
            return;
        }

        $user->update(['is_active' => false]);
        $employee->forceFill(['user_id' => null])->save();
    }
}
