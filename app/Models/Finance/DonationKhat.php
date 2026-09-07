<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * দানের খাত (যাকাত, লিল্লাহ, ফিতরা…)।
 *
 * @property string $code
 * @property string $name
 * @property bool $is_restricted
 */
class DonationKhat extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_restricted' => 'boolean',
            'target_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
