<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\People\Student;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ছাত্রভিত্তিক ভিন্ন রেট — ফি স্ট্রাকচারকে হারায়।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $student_id
 * @property int $academic_session_id
 * @property int $fee_head_id
 * @property string $amount
 * @property string|null $reason
 */
class StudentFeeOverride extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
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
}
