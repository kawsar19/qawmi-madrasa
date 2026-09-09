<?php

declare(strict_types=1);

namespace App\Models\Cms;

use App\Contracts\TenantScoped;
use App\Support\Media;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * পাবলিক সাইটের ছবি — স্লাইডার ও গ্যালারি।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $collection
 * @property string $image_path
 * @property string|null $title
 * @property string|null $subtitle
 * @property string|null $link_label
 * @property string|null $link_url
 * @property int $sort_order
 * @property bool $is_active
 */
class SiteImage extends Model implements TenantScoped
{
    use BelongsToTenant;

    public const COLLECTION_SLIDER = 'slider';

    public const COLLECTION_GALLERY = 'gallery';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    /**
     * সাইটে দেখানোর মতো ছবি — সক্রিয়, ক্রম অনুযায়ী।
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function url(): ?string
    {
        return Media::url($this->image_path);
    }

    /**
     * বোতাম দেখাবে কি না — লেখা ও লিংক দুটোই লাগে।
     */
    public function hasLink(): bool
    {
        return $this->link_url !== null && $this->link_label !== null;
    }
}
