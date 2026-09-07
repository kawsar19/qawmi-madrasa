<?php

declare(strict_types=1);

namespace App\Models\Academic;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * মারহালা — শিক্ষার স্তর (ইবতেদাইয়্যাহ … তাকমিল)।
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $track
 */
class Marhala extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const TRACK_KITAB = 'kitab';

    public const TRACK_HIFZ = 'hifz';

    public const TRACK_NAZERA = 'nazera';

    public const TRACK_QIRAT = 'qirat';

    public const TRACK_IFTA = 'ifta';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function jamaats(): HasMany
    {
        return $this->hasMany(Jamaat::class);
    }

    public function kitabs(): HasMany
    {
        return $this->hasMany(Kitab::class);
    }
}
