<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\People\Student;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ছাত্রভিত্তিক ছাড় — এতিম, গরিব, কর্মচারীর সন্তান।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $student_id
 * @property int $academic_session_id
 * @property int|null $fee_head_id
 * @property string $type
 * @property string $value
 * @property string|null $reason
 * @property int|null $granted_by
 * @property bool $is_active
 */
class StudentDiscount extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    /** শতকরা — রেট বাড়লে ছাড়ও আনুপাতিক বাড়ে। */
    public const TYPE_PERCENT = 'percent';

    /** নির্দিষ্ট টাকা। */
    public const TYPE_FIXED = 'fixed';

    /** পূর্ণ মওকুফ — এতিম ছাত্র কিছুই দেবে না। */
    public const TYPE_FULL = 'full';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * এই ছাড় প্রয়োগ করলে কত টাকা কমবে।
     *
     * ছাড় কখনো রেটের চেয়ে বেশি হবে না — নইলে ইনভয়েস ঋণাত্মক হয়ে
     * মাদরাসা ছাত্রকে টাকা দিত।
     */
    public function amountFor(float $rate): float
    {
        $discount = match ($this->type) {
            self::TYPE_FULL => $rate,
            self::TYPE_PERCENT => $rate * ((float) $this->value / 100),
            default => (float) $this->value,
        };

        return round(min($discount, $rate), 2);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<AcademicSession, $this> */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /** @return BelongsTo<FeeHead, $this> */
    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
