<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Cms\SiteSetting;

/**
 * পাবলিক সাইটের টেমপ্লেট রেজোলভার।
 *
 * একটি নতুন টেমপ্লেট = `resources/views/public/templates/<নাম>/` ফোল্ডার,
 * তাতে `layout.blade.php` ও `pages/*.blade.php`। কোনো PHP লেখা লাগে না —
 * কন্ট্রোলার, রুট বা মডেল অপরিবর্তিত থাকে।
 *
 * Every page receives the same variables regardless of template, so a new
 * folder can be a straight copy of an existing one with restyled markup.
 */
final class SiteTemplate
{
    /**
     * প্রতিটি টেমপ্লেটে যে পেজগুলো থাকা দরকার।
     *
     * A template missing one of these still works: the resolver falls back to
     * the classic version of that single page rather than 404-ing.
     */
    public const PAGES = ['home', 'about', 'notices', 'notice', 'teachers', 'contact'];

    public const FALLBACK = SiteSetting::TEMPLATE_CLASSIC;

    /**
     * চলতি টেমপ্লেটের পেজ ভিউয়ের নাম।
     *
     * খুঁজে না পেলে classic-এ ফিরে যায়, যাতে অসম্পূর্ণ টেমপ্লেটেও
     * সাইট সাদা পর্দা না দেখায়।
     */
    public static function view(SiteSetting $settings, string $page): string
    {
        $view = self::pageView($settings->template, $page);

        if (view()->exists($view)) {
            return $view;
        }

        return self::pageView(self::FALLBACK, $page);
    }

    /**
     * টেমপ্লেটের লেআউট কম্পোনেন্টের নাম — হেডার ও ফুটার এখানেই, প্রতি পেজে নয়।
     *
     * AppServiceProvider প্রতিটি টেমপ্লেট ফোল্ডারকে anonymous component
     * namespace হিসেবে রেজিস্টার করে, তাই "<template>::layout" কাজ করে।
     */
    public static function layout(string $template): string
    {
        return view()->exists("public.templates.{$template}.layout")
            ? "{$template}::layout"
            : self::FALLBACK.'::layout';
    }

    /**
     * সাইটের মেনু — সেকশন বন্ধ থাকলে তার লিংকও থাকে না।
     *
     * @return list<array{label: string, route: string, params?: array<string, mixed>}>
     */
    public static function menu(SiteSetting $settings): array
    {
        $menu = [
            ['label' => 'হোম', 'route' => 'public.home'],
            ['label' => 'পরিচিতি', 'route' => 'public.about'],
        ];

        if ($settings->show_notices) {
            $menu[] = ['label' => 'নোটিশ', 'route' => 'public.notices'];
        }

        if ($settings->show_teachers) {
            $menu[] = ['label' => 'শিক্ষকমণ্ডলী', 'route' => 'public.teachers'];
        }

        $menu[] = ['label' => 'যোগাযোগ', 'route' => 'public.contact'];

        return $menu;
    }

    /**
     * ইনস্টল করা সব টেমপ্লেট — প্যানেলের ড্রপডাউনের জন্য।
     *
     * Discovered from the filesystem, so dropping in a folder is enough to
     * make it selectable.
     *
     * @return array<string, string>
     */
    public static function available(): array
    {
        $labels = [
            SiteSetting::TEMPLATE_CLASSIC => 'ক্লাসিক — গম্ভীর ও ঐতিহ্যবাহী',
            SiteSetting::TEMPLATE_MODERN => 'মডার্ন — পরিষ্কার ও আধুনিক',
            SiteSetting::TEMPLATE_MINIMAL => 'মিনিমাল — সাদামাটা',
            SiteSetting::TEMPLATE_HERITAGE => 'হেরিটেজ — ঐতিহ্য',
        ];

        $available = [];

        foreach (self::installed() as $template) {
            $available[$template] = $labels[$template] ?? ucfirst($template);
        }

        return $available;
    }

    /**
     * ফাইলসিস্টেমে যেসব টেমপ্লেট ফোল্ডার আছে।
     *
     * @return list<string>
     */
    public static function installed(): array
    {
        $base = resource_path('views/public/templates');

        if (! is_dir($base)) {
            return [self::FALLBACK];
        }

        $templates = [];

        foreach ((array) glob($base.'/*', GLOB_ONLYDIR) as $directory) {
            $name = basename((string) $directory);

            // হোম পেজ ছাড়া ফোল্ডারটি ব্যবহারযোগ্য টেমপ্লেট নয়।
            if (view()->exists(self::pageView($name, 'home'))) {
                $templates[] = $name;
            }
        }

        return $templates === [] ? [self::FALLBACK] : $templates;
    }

    private static function pageView(string $template, string $page): string
    {
        return "public.templates.{$template}.pages.{$page}";
    }
}
