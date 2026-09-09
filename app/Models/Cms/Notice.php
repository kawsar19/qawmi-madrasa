<?php

declare(strict_types=1);

namespace App\Models\Cms;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * নোটিশ / এলান।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $title
 * @property string|null $body
 * @property string $category
 * @property string|null $attachment_path
 * @property bool $is_published
 * @property bool $is_pinned
 * @property Carbon|null $published_on
 * @property Carbon|null $expires_on
 */
class Notice extends Model implements TenantScoped
{
    use BelongsToTenant;
    use SoftDeletes;

    public const CATEGORY_GENERAL = 'general';

    public const CATEGORY_ADMISSION = 'admission';

    public const CATEGORY_EXAM = 'exam';

    public const CATEGORY_HOLIDAY = 'holiday';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'published_on' => 'date',
            'expires_on' => 'date',
            'is_published' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    /**
     * পাবলিক সাইটে যা দেখানো যাবে — প্রকাশিত ও মেয়াদ শেষ হয়নি।
     *
     * @param  Builder<self>  $query
     */
    public function scopeVisible(Builder $query): void
    {
        $query->where('is_published', true)
            ->where(function (Builder $inner): void {
                $inner->whereNull('expires_on')
                    ->orWhereDate('expires_on', '>=', now()->toDateString());
            });
    }

    /**
     * পিন করা আগে, তারপর নতুন তারিখ আগে।
     *
     * @param  Builder<self>  $query
     */
    public function scopeRanked(Builder $query): void
    {
        $query->orderByDesc('is_pinned')
            ->orderByDesc('published_on')
            ->orderByDesc('id');
    }

    /**
     * @return array<string, string>
     */
    public static function categories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'সাধারণ',
            self::CATEGORY_ADMISSION => 'ভর্তি',
            self::CATEGORY_EXAM => 'পরীক্ষা',
            self::CATEGORY_HOLIDAY => 'ছুটি',
        ];
    }
}
