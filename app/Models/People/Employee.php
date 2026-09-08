<?php

declare(strict_types=1);

namespace App\Models\People;

use App\Contracts\TenantScoped;
use App\Models\Academic\Section;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * শিক্ষক ও কর্মচারী।
 *
 * A teacher is an employee with type=teacher; the split matters because only
 * teachers take sections, kitabs and mark entry, while a cook or guard is on
 * the payroll and the attendance register but nothing else.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $employee_uid
 * @property int|null $user_id
 * @property string $name
 * @property string|null $name_ar
 * @property string|null $father_name
 * @property string|null $mobile
 * @property string|null $email
 * @property string $type
 * @property string|null $designation
 * @property string|null $qualification
 * @property string $monthly_salary
 * @property string|null $district
 * @property string|null $upazila
 * @property string $status
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $joined_on
 * @property Carbon|null $left_on
 */
class Employee extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const TYPE_TEACHER = 'teacher';

    public const TYPE_STAFF = 'staff';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_ON_LEAVE = 'on_leave';

    public const STATUS_RETIRED = 'retired';

    public const STATUS_TERMINATED = 'terminated';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joined_on' => 'date',
            'left_on' => 'date',
            // টাকা কখনো float নয়।
            'monthly_salary' => 'decimal:2',
        ];
    }

    /**
     * লগইন অ্যাকাউন্ট (থাকলে)।
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ইনচার্জ হিসেবে যেসব শাখা।
     *
     * @return HasMany<Section, $this>
     */
    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /** @param Builder<self> $query */
    public function scopeTeachers(Builder $query): void
    {
        $query->where('type', self::TYPE_TEACHER);
    }

    public function isTeacher(): bool
    {
        return $this->type === self::TYPE_TEACHER;
    }
}
