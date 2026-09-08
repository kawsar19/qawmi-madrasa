<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * শিক্ষাবর্ষ।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $hijri_year
 * @property string|null $gregorian_year
 * @property Carbon|null $starts_on
 * @property Carbon|null $ends_on
 * @property bool $is_current
 * @property bool $is_locked
 */
class AcademicSession extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    /** @param Builder<self> $query */
    public function scopeCurrent(Builder $query): void
    {
        $query->where('is_current', true);
    }
}
