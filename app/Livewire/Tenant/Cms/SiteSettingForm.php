<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Cms;

use App\Models\Cms\SiteSetting;
use App\Support\SiteTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * ওয়েবসাইট সেটিংস — টেমপ্লেট, রঙ, লোগো ও পরিচিতি।
 *
 * এখান থেকেই মাদরাসা নিজের সাইটের চেহারা বদলাতে পারে; ডেভেলপার লাগে না।
 */
class SiteSettingForm extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $tab = 'appearance';

    // ---- চেহারা ----
    public string $template = SiteSetting::TEMPLATE_CLASSIC;

    public string $brandColor = '#15803d';

    public string $accentColor = '#a16207';

    public ?TemporaryUploadedFile $logo = null;

    public ?TemporaryUploadedFile $heroImage = null;

    // ---- পরিচিতি ----
    public string $siteTitle = '';

    public string $siteTitleAr = '';

    public string $tagline = '';

    public string $establishedYear = '';

    public string $aboutShort = '';

    public string $aboutFull = '';

    public string $principalMessage = '';

    public string $principalName = '';

    // ---- যোগাযোগ ----
    public string $phone = '';

    public string $phoneAlt = '';

    public string $email = '';

    public string $address = '';

    public string $facebookUrl = '';

    public string $youtubeUrl = '';

    // ---- সেকশন ----
    public bool $showNotices = true;

    public bool $showTeachers = true;

    public bool $showGallery = true;

    public bool $showAdmissionForm = true;

    public bool $isPublished = true;

    public function mount(): void
    {
        $this->authorize('cms.site_setting.view');

        $settings = SiteSetting::current();

        $this->template = $settings->template;
        $this->brandColor = $settings->brand_color;
        $this->accentColor = $settings->accent_color;

        $this->siteTitle = (string) $settings->site_title;
        $this->siteTitleAr = (string) $settings->site_title_ar;
        $this->tagline = (string) $settings->tagline;
        $this->establishedYear = (string) $settings->established_year;
        $this->aboutShort = (string) $settings->about_short;
        $this->aboutFull = (string) $settings->about_full;
        $this->principalMessage = (string) $settings->principal_message;
        $this->principalName = (string) $settings->principal_name;

        $this->phone = (string) $settings->phone;
        $this->phoneAlt = (string) $settings->phone_alt;
        $this->email = (string) $settings->email;
        $this->address = (string) $settings->address;
        $this->facebookUrl = (string) $settings->facebook_url;
        $this->youtubeUrl = (string) $settings->youtube_url;

        $this->showNotices = $settings->show_notices;
        $this->showTeachers = $settings->show_teachers;
        $this->showGallery = $settings->show_gallery;
        $this->showAdmissionForm = $settings->show_admission_form;
        $this->isPublished = $settings->is_published;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            // ইনস্টল করা টেমপ্লেটই কেবল বাছাই করা যাবে।
            'template' => ['required', Rule::in(SiteTemplate::installed())],
            // CSS ভেরিয়েবলে সরাসরি বসে, তাই কড়া hex যাচাই — নইলে
            // ইচ্ছেমতো লেখা স্টাইলশিটে ঢুকে যেতে পারত।
            'brandColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'accentColor' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'logo' => ['nullable', 'image', 'max:1024'],
            'heroImage' => ['nullable', 'image', 'max:3072'],

            'siteTitle' => ['nullable', 'string', 'max:255'],
            'siteTitleAr' => ['nullable', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'establishedYear' => ['nullable', 'string', 'max:10'],
            'aboutShort' => ['nullable', 'string', 'max:600'],
            'aboutFull' => ['nullable', 'string', 'max:5000'],
            'principalMessage' => ['nullable', 'string', 'max:2000'],
            'principalName' => ['nullable', 'string', 'max:255'],

            'phone' => ['nullable', 'string', 'max:20'],
            'phoneAlt' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'facebookUrl' => ['nullable', 'url', 'max:255'],
            'youtubeUrl' => ['nullable', 'url', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'template' => 'টেমপ্লেট',
            'brandColor' => 'প্রধান রঙ',
            'accentColor' => 'অ্যাকসেন্ট রঙ',
            'logo' => 'লোগো',
            'heroImage' => 'ব্যানার ছবি',
            'siteTitle' => 'সাইটের নাম',
            'siteTitleAr' => 'আরবি নাম',
            'tagline' => 'সংক্ষিপ্ত পরিচয়',
            'establishedYear' => 'প্রতিষ্ঠাকাল',
            'aboutShort' => 'সংক্ষিপ্ত পরিচিতি',
            'aboutFull' => 'বিস্তারিত পরিচিতি',
            'principalMessage' => 'মুহতামিমের বাণী',
            'principalName' => 'মুহতামিমের নাম',
            'phone' => 'ফোন',
            'phoneAlt' => 'বিকল্প ফোন',
            'email' => 'ইমেইল',
            'address' => 'ঠিকানা',
            'facebookUrl' => 'ফেসবুক লিংক',
            'youtubeUrl' => 'ইউটিউব লিংক',
        ];
    }

    public function save(): void
    {
        $this->authorize('cms.site_setting.update');

        $this->validate();

        $settings = SiteSetting::current();

        $attributes = [
            'template' => $this->template,
            'brand_color' => strtolower($this->brandColor),
            'accent_color' => strtolower($this->accentColor),

            'site_title' => $this->blankToNull($this->siteTitle),
            'site_title_ar' => $this->blankToNull($this->siteTitleAr),
            'tagline' => $this->blankToNull($this->tagline),
            'established_year' => $this->blankToNull($this->establishedYear),
            'about_short' => $this->blankToNull($this->aboutShort),
            'about_full' => $this->blankToNull($this->aboutFull),
            'principal_message' => $this->blankToNull($this->principalMessage),
            'principal_name' => $this->blankToNull($this->principalName),

            'phone' => $this->blankToNull($this->phone),
            'phone_alt' => $this->blankToNull($this->phoneAlt),
            'email' => $this->blankToNull($this->email),
            'address' => $this->blankToNull($this->address),
            'facebook_url' => $this->blankToNull($this->facebookUrl),
            'youtube_url' => $this->blankToNull($this->youtubeUrl),

            'show_notices' => $this->showNotices,
            'show_teachers' => $this->showTeachers,
            'show_gallery' => $this->showGallery,
            'show_admission_form' => $this->showAdmissionForm,
            'is_published' => $this->isPublished,
        ];

        // ছবি আপলোড হলেই কেবল পথ বদলাবে; নইলে পুরনোটাই থাকবে।
        if ($this->logo !== null) {
            $attributes['logo_path'] = $this->logo->store('site', 'public');
        }

        if ($this->heroImage !== null) {
            $attributes['hero_image_path'] = $this->heroImage->store('site', 'public');
        }

        $settings->update($attributes);

        $this->logo = null;
        $this->heroImage = null;

        session()->flash('status', 'ওয়েবসাইট সেটিংস সংরক্ষণ করা হয়েছে।');
    }

    public function removeLogo(): void
    {
        $this->authorize('cms.site_setting.update');

        SiteSetting::current()->update(['logo_path' => null]);

        session()->flash('status', 'লোগো সরানো হয়েছে।');
    }

    public function removeHeroImage(): void
    {
        $this->authorize('cms.site_setting.update');

        SiteSetting::current()->update(['hero_image_path' => null]);

        session()->flash('status', 'ব্যানার ছবি সরানো হয়েছে।');
    }

    private function blankToNull(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    public function render(): View
    {
        return view('livewire.tenant.cms.site-setting-form', [
            'settings' => SiteSetting::current(),
            'templates' => SiteTemplate::available(),
        ]);
    }
}
