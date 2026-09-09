<?php

declare(strict_types=1);

namespace App\Models\Cms;

use App\Contracts\TenantScoped;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

/**
 * পাবলিক সাইটের সেটিংস — প্রতি মাদরাসার একটি সারি।
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $template
 * @property string|null $site_title
 * @property string|null $site_title_ar
 * @property string|null $tagline
 * @property string|null $established_year
 * @property string|null $about_short
 * @property string|null $about_full
 * @property string|null $principal_message
 * @property string|null $principal_name
 * @property string|null $phone
 * @property string|null $phone_alt
 * @property string|null $email
 * @property string|null $address
 * @property string|null $facebook_url
 * @property string|null $youtube_url
 * @property string $brand_color
 * @property string $accent_color
 * @property string|null $logo_path
 * @property string|null $hero_image_path
 * @property bool $show_notices
 * @property bool $show_teachers
 * @property bool $show_gallery
 * @property bool $show_admission_form
 * @property bool $is_published
 */
class SiteSetting extends Model implements TenantScoped
{
    use BelongsToTenant;

    public const TEMPLATE_CLASSIC = 'classic';

    public const TEMPLATE_MODERN = 'modern';

    public const TEMPLATE_MINIMAL = 'minimal';

    public const TEMPLATE_HERITAGE = 'heritage';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'show_notices' => 'boolean',
            'show_teachers' => 'boolean',
            'show_gallery' => 'boolean',
            'show_admission_form' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    /**
     * চলতি মাদরাসার সেটিংস — না থাকলে ডিফল্ট সহ তৈরি হয়।
     *
     * The public site must render for a madrasa that has never opened the
     * settings screen, so this never returns null.
     */
    public static function current(): self
    {
        $settings = static::query()->first();

        if ($settings !== null) {
            return $settings;
        }

        // firstOrCreate() would insert the row but hand back a model whose
        // DB-default columns are still NULL, so `is_published` would read
        // false and the site would 404 on its very first visit. Create it
        // with the defaults spelled out instead.
        return static::query()->create([
            'template' => self::TEMPLATE_CLASSIC,
            'brand_color' => '#15803d',
            'accent_color' => '#a16207',
            'show_notices' => true,
            'show_teachers' => true,
            'show_gallery' => true,
            'show_admission_form' => true,
            'is_published' => true,
        ]);
    }

    /**
     * সাইটে দেখানোর নাম — আলাদা না দিলে মাদরাসার নামই।
     */
    public function displayTitle(): string
    {
        return $this->site_title ?: (string) tenant('name');
    }
}
