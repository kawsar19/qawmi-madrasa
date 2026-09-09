<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Livewire\Tenant\People\EmployeeList;
use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\Cms\Notice;
use App\Models\Cms\SiteSetting;
use App\Models\People\Employee;
use App\Models\People\Student;
use App\Support\SiteTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * মাদরাসার পাবলিক ওয়েবসাইট।
 *
 * এই কন্ট্রোলার টেমপ্লেট-নিরপেক্ষ: প্রতিটি পেজের জন্য একই ডেটা তৈরি করে
 * SiteTemplate-কে দেয়, সে চলতি টেমপ্লেটের ভিউ খুঁজে নেয়।
 *
 * A new template is therefore just a folder of Blade files — no controller,
 * route or model change. See SiteTemplate for the contract.
 */
class PublicSiteController
{
    public function home(): View
    {
        $settings = $this->settings();

        return $this->render($settings, 'home', [
            'stats' => $this->stats(),
            // হোমপেজে সংক্ষিপ্ত — বিস্তারিত আলাদা পেজে।
            'notices' => $settings->show_notices
                ? Notice::query()->visible()->ranked()->limit(3)->get()
                : collect(),
            'teachers' => $settings->show_teachers
                ? $this->teacherQuery()->limit(4)->get()
                : collect(),
            'departments' => $this->departments(),
        ]);
    }

    public function about(): View
    {
        return $this->render($this->settings(), 'about', [
            'departments' => $this->departments(),
        ]);
    }

    public function notices(Request $request): View
    {
        $settings = $this->settings();

        $this->assertSectionVisible($settings->show_notices);

        $category = (string) $request->query('category', '');

        return $this->render($settings, 'notices', [
            'notices' => Notice::query()
                ->visible()
                ->when(
                    array_key_exists($category, Notice::categories()),
                    fn ($query) => $query->where('category', $category),
                )
                ->ranked()
                ->paginate(10)
                ->withQueryString(),
            'categories' => Notice::categories(),
            'activeCategory' => $category,
        ]);
    }

    public function notice(int $id): View
    {
        $settings = $this->settings();

        $this->assertSectionVisible($settings->show_notices);

        // visible() স্কোপ ছাড়া খসড়া বা মেয়াদোত্তীর্ণ নোটিশও URL জানা
        // থাকলে পড়া যেত।
        $notice = Notice::query()->visible()->findOrFail($id);

        return $this->render($settings, 'notice', [
            'notice' => $notice,
            'related' => Notice::query()
                ->visible()
                ->whereKeyNot($notice->getKey())
                ->ranked()
                ->limit(4)
                ->get(),
        ]);
    }

    public function teachers(): View
    {
        $settings = $this->settings();

        $this->assertSectionVisible($settings->show_teachers);

        return $this->render($settings, 'teachers', [
            'teachers' => $this->teacherQuery()->get(),
        ]);
    }

    public function contact(): View
    {
        return $this->render($this->settings(), 'contact');
    }

    /**
     * চলতি মাদরাসার সেটিংস; সাইট বন্ধ থাকলে ৪০৪।
     */
    private function settings(): SiteSetting
    {
        $settings = SiteSetting::current();

        // সাইট বন্ধ রেখে কাজ করার সুযোগ — দর্শক ৪০৪ পাবেন, ভাঙা পেজ নয়।
        if (! $settings->is_published) {
            throw new NotFoundHttpException;
        }

        return $settings;
    }

    /**
     * সেকশন বন্ধ থাকলে তার পেজও থাকা উচিত নয়।
     */
    private function assertSectionVisible(bool $visible): void
    {
        if (! $visible) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * টেমপ্লেটের পেজ রেন্ডার — প্রতিটি পেজ যে ডেটা সবসময় পায়
     * (settings, menu, designationLabels) তা এখানেই যোগ হয়।
     *
     * @param  array<string, mixed>  $data
     */
    private function render(SiteSetting $settings, string $page, array $data = []): View
    {
        return view(SiteTemplate::view($settings, $page), [
            'settings' => $settings,
            'menu' => SiteTemplate::menu($settings),
            'designationLabels' => EmployeeList::designations(),
            ...$data,
        ]);
    }

    /**
     * @return array<string, int>
     */
    private function stats(): array
    {
        return [
            'students' => Student::query()->active()->count(),
            'employees' => Employee::query()->active()->count(),
            'marhalas' => Marhala::query()->count(),
            'kitabs' => Kitab::query()->count(),
        ];
    }

    /**
     * শিক্ষকমণ্ডলী — কর্মচারী নয়, শুধু শিক্ষক।
     *
     * @return Builder<Employee>
     */
    private function teacherQuery()
    {
        return Employee::query()->active()->teachers()->orderBy('name');
    }

    /**
     * বিভাগ ও প্রতিটির ক্লাস সংখ্যা।
     *
     * @return Collection<int, Marhala>
     */
    private function departments()
    {
        return Marhala::query()->withCount('jamaats')->orderBy('sort_order')->get();
    }
}
