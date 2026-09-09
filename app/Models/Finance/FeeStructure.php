<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ফি স্ট্রাকচার — বর্ষ + জামাত + খাত + আবাসিক ধরন → টাকা।
 *
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_session_id
 * @property int $jamaat_id
 * @property int $fee_head_id
 * @property string|null $residency_type
 * @property string $amount
 * @property bool $is_active
 */
class FeeStructure extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            // টাকা কখনো float নয়।
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
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

    /** @return BelongsTo<FeeHead, $this> */
    public function feeHead(): BelongsTo
    {
        return $this->belongsTo(FeeHead::class);
    }

    /** @param Builder<self> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }
}
