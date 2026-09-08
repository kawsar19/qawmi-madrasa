<?php

declare(strict_types=1);

namespace App\Livewire\Central\Tenants;

use App\Models\Central\Plan;
use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Services\Tenancy\TenantProvisioner;
use App\Support\BnSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * নতুন মাদরাসা তৈরি — CLI-র tenant:create কমান্ডের UI সংস্করণ।
 */
class TenantCreate extends Component
{
    public string $name = '';

    public string $slug = '';

    public string $eiin = '';

    public string $madrasaType = 'kitab';

    public string $address = '';

    public string $domain = '';

    public string $preset = 'kitab_madrasa';

    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    public string $adminMobile = '';

    public ?int $planId = null;

    public int $subscriptionMonths = 12;

    public function mount(): void
    {
        $this->planId = Plan::where('is_active', true)->orderBy('sort_order')->value('id');
    }

    /**
     * নাম লিখলে slug ও ডোমেইন স্বয়ংক্রিয়ভাবে প্রস্তাব করা হয়।
     */
    public function updatedName(string $value): void
    {
        if ($this->slug === '' || $this->slug === BnSlug::make($this->name)) {
            $this->slug = BnSlug::make($value);
            $this->updatedSlug($this->slug);
        }
    }

    public function updatedSlug(string $value): void
    {
        $this->slug = BnSlug::make($value);
        $this->domain = $this->slug === ''
            ? ''
            : $this->slug.'.'.config('tenancy.central_domains')[0];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('tenants', 'slug')],
            'eiin' => ['nullable', 'string', 'max:50'],
            'madrasaType' => ['required', Rule::in(['kitab', 'hifz', 'mixed'])],
            'address' => ['nullable', 'string', 'max:500'],
            'domain' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain')],
            'preset' => ['required', Rule::in(['kitab_madrasa', 'hifz_madrasa'])],
            'adminName' => ['required', 'string', 'max:255'],
            'adminEmail' => ['required', 'email', 'max:255'],
            'adminPassword' => ['required', 'string', 'min:8'],
            'adminMobile' => ['nullable', 'string', 'regex:/^01[3-9]\d{8}$/'],
            'planId' => ['nullable', 'exists:plans,id'],
            'subscriptionMonths' => ['required', 'integer', 'min:1', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'মাদরাসার নাম',
            'slug' => 'slug',
            'domain' => 'ডোমেইন',
            'adminName' => 'অ্যাডমিনের নাম',
            'adminEmail' => 'অ্যাডমিনের ইমেইল',
            'adminPassword' => 'পাসওয়ার্ড',
            'adminMobile' => 'মোবাইল নম্বর',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'adminMobile.regex' => 'মোবাইল নম্বর ০১৩–০১৯ দিয়ে শুরু হয়ে ১১ সংখ্যার হতে হবে।',
        ];
    }

    public function save(TenantProvisioner $provisioner): void
    {
        $this->validate();

        $tenant = $provisioner->provision(
            [
                'name' => $this->name,
                'slug' => $this->slug,
                'eiin' => $this->eiin !== '' ? $this->eiin : null,
                'madrasa_type' => $this->madrasaType,
                'address' => $this->address !== '' ? $this->address : null,
            ],
            $this->domain,
            [
                'name' => $this->adminName,
                'email' => $this->adminEmail,
                'password' => $this->adminPassword,
                'mobile' => $this->adminMobile !== '' ? $this->adminMobile : null,
            ],
            $this->preset,
        );

        if ($this->planId !== null) {
            Subscription::create([
                'tenant_id' => $tenant->getKey(),
                'plan_id' => $this->planId,
                'starts_at' => now()->toDateString(),
                'ends_at' => now()->addMonths($this->subscriptionMonths)->toDateString(),
                'status' => Subscription::STATUS_ACTIVE,
            ]);
        }

        session()->flash('status', "{$tenant->name} তৈরি হয়েছে।");

        $this->redirectRoute('central.tenants.index', navigate: false);
    }

    public function render(): View
    {
        return view('livewire.central.tenants.tenant-create', [
            'plans' => Plan::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }
}
