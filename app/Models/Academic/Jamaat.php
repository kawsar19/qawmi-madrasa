<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * জামাত — মারহালার ভেতরের একটি বর্ষ/শ্রেণি।
 *
 * @property int $id
 * @property string $name
 */
class Jamaat extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function marhala(): BelongsTo
    {
        return $this->belongsTo(Marhala::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function jamaatKitabs(): HasMany
    {
        return $this->hasMany(JamaatKitab::class);
    }
}
