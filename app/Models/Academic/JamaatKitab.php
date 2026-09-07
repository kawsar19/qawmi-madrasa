<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * শিক্ষাবর্ষভিত্তিক কারিকুলাম — কোন বর্ষে কোন জামাতে কোন কিতাব, কত নম্বরে।
 *
 * Marks reference this row rather than kitab_id, which is what keeps each
 * year's full marks frozen in history.
 *
 * @property int $id
 * @property int $full_marks
 * @property int $pass_marks
 */
class JamaatKitab extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function academicSession(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class);
    }

    public function jamaat(): BelongsTo
    {
        return $this->belongsTo(Jamaat::class);
    }

    public function kitab(): BelongsTo
    {
        return $this->belongsTo(Kitab::class);
    }
}
