<?php

declare(strict_types=1);

namespace App\Models\People;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\User;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * ভর্তি আবেদন — আবেদনকারী এখনো ছাত্র নয়।
 *
 * আবেদিত → অনুমোদিত → ভর্তি সম্পন্ন
 *            ↓
 *          বাতিল
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $application_no
 * @property int $academic_session_id
 * @property int $jamaat_id
 * @property string $name
 * @property string|null $name_ar
 * @property string $father_name
 * @property string|null $mother_name
 * @property string|null $birth_certificate_no
 * @property string|null $mobile
 * @property string|null $village
 * @property string|null $post_office
 * @property string|null $union
 * @property string|null $upazila
 * @property string|null $district
 * @property string|null $previous_madrasa
 * @property string|null $previous_jamaat
 * @property string|null $remarks
 * @property string|null $guardian_name
 * @property string|null $guardian_relation
 * @property string|null $guardian_mobile
 * @property string $residency_type
 * @property string $status
 * @property string $source
 * @property int|null $student_id
 * @property int|null $decided_by
 * @property Carbon|null $date_of_birth
 * @property Carbon|null $applied_on
 * @property Carbon|null $decided_at
 * @property bool $is_orphan
 * @property bool $is_poor
 */
class Admission extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_APPLIED = 'applied';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ENROLLED = 'enrolled';

    public const SOURCE_OFFICE = 'office';

    public const SOURCE_ONLINE = 'online';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'applied_on' => 'date',
            'shortlisted_at' => 'datetime',
            'decided_at' => 'datetime',
            'exam_marks' => 'decimal:2',
            'is_orphan' => 'boolean',
            'is_poor' => 'boolean',
        ];
    }

    /** @return BelongsTo<AcademicSession, $this> */
    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    /** @return BelongsTo<Jamaat, $this> */
    public function jamaat(): BelongsTo
    {
        return $this->belongsTo(Jamaat::class);
    }

    /**
     * ভর্তি সম্পন্ন হলে তৈরি হওয়া ছাত্র।
     *
     * @return BelongsTo<Student, $this>
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /** @return BelongsTo<User, $this> */
    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /** @param Builder<self> $query */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_APPLIED);
    }

    /**
     * সিদ্ধান্ত নেওয়া বাকি — অনুমোদন বা বাতিল করা যাবে।
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_APPLIED;
    }

    /**
     * ভর্তি করা যাবে কিনা — অনুমোদিত, কিন্তু এখনো ভর্তি হয়নি।
     */
    public function isEnrollable(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->student_id === null;
    }

    public function isEnrolled(): bool
    {
        return $this->status === self::STATUS_ENROLLED;
    }
}
