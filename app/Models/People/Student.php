<?php

declare(strict_types=1);

namespace App\Models\People;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * ছাত্র — স্থায়ী পরিচয়। বর্ষভিত্তিক তথ্য Enrollment-এ।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $student_uid
 * @property string $name
 * @property string $father_name
 * @property string|null $mobile
 * @property string|null $district
 * @property string|null $upazila
 * @property string $residency_type
 * @property string $status
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $admitted_on
 * @property bool $is_orphan
 * @property bool $is_poor
 */
class Student extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const RESIDENCY_RESIDENTIAL = 'residential';

    public const RESIDENCY_NON_RESIDENTIAL = 'non_residential';

    public const RESIDENCY_DAY_CARE = 'day_care';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_TRANSFERRED = 'transferred';

    public const STATUS_DROPPED = 'dropped';

    public const STATUS_GRADUATED = 'graduated';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admitted_on' => 'date',
            'is_orphan' => 'boolean',
            'is_poor' => 'boolean',
        ];
    }

    /** @return HasMany<Enrollment, $this> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return BelongsToMany<Guardian, $this> */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(Guardian::class)
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * চলতি বর্ষের এনরোলমেন্ট (থাকলে)।
     */
    public function enrollmentFor(int $sessionId): ?Enrollment
    {
        return $this->enrollments()
            ->where('academic_session_id', $sessionId)
            ->first();
    }
}
