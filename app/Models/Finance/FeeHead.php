<?php

declare(strict_types=1);

namespace App\Models\Finance;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * ফি খাত (মাসিক বেতন, ভর্তি ফি, খানা বিল…)।
 *
 * @property string $code
 * @property string $name
 * @property string $type
 */
class FeeHead extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const TYPE_MONTHLY = 'monthly';

    public const TYPE_ONE_TIME = 'one_time';

    public const TYPE_EXAM = 'exam';

    public const TYPE_BOARDING = 'boarding';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
