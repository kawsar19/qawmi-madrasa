<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * কিতাব — মাস্টার বই তালিকা।
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $name_ar
 */
class Kitab extends Model implements TenantScoped
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

    /**
     * প্রদর্শনের নাম — বাংলা ও আরবি একসাথে। ("হেদায়া (الهداية)")
     */
    public function displayName(): string
    {
        return $this->name_ar !== null && $this->name_ar !== ''
            ? sprintf('%s (%s)', $this->name, $this->name_ar)
            : $this->name;
    }
}
