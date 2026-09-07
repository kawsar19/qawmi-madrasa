<?php

declare(strict_types=1);

namespace App\Models\Exam;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * গ্রেড স্কেল — শতাংশ থেকে গ্রেড নির্ধারণ।
 *
 * @property string $code
 * @property string $name
 * @property string $min_percent
 * @property string $max_percent
 * @property bool $is_fail
 */
class GradeScale extends Model implements TenantScoped
{
    use BelongsToTenant;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'min_percent' => 'decimal:2',
            'max_percent' => 'decimal:2',
            'point' => 'decimal:2',
            'is_fail' => 'boolean',
        ];
    }

    /**
     * শতাংশ থেকে গ্রেড খুঁজে বের করে।
     */
    public static function resolveFor(float $percent): ?self
    {
        return static::query()
            ->where('min_percent', '<=', $percent)
            ->where('max_percent', '>=', $percent)
            ->orderBy('sort_order')
            ->first();
    }
}
