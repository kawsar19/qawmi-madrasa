<?php

declare(strict_types=1);

namespace App\Models\People;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\Academic\Section;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * এনরোলমেন্ট — কোন ছাত্র কোন বর্ষে কোন ক্লাসে, রোল কত।
 *
 * রোল এখানে থাকে, Student-এ নয়: রোল প্রতি বছর বদলায় (প্রায়ই মেধাক্রমে),
 * কিন্তু student_uid কখনো বদলায় না।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $student_id
 * @property int $academic_session_id
 * @property int $jamaat_id
 * @property int|null $section_id
 * @property int|null $roll_no
 * @property string $status
 * @property Carbon|null $enrolled_on
 */
class Enrollment extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const STATUS_STUDYING = 'studying';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_LEFT = 'left';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['enrolled_on' => 'date'];
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

    /** @return BelongsTo<Jamaat, $this> */
    public function jamaat(): BelongsTo
    {
        return $this->belongsTo(Jamaat::class);
    }

    /** @return BelongsTo<Section, $this> */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
