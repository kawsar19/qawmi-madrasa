<?php

declare(strict_types=1);

namespace App\Livewire\Central\Tenants;

use App\Models\Central\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class TenantEdit extends Component
{
    public Tenant $tenant;

    public string $name = '';

    public string $eiin = '';

    public string $madrasaType = 'kitab';

    public string $address = '';

    public string $status = '';

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->name = $tenant->name;
        $this->eiin = (string) $tenant->eiin;
        $this->madrasaType = $tenant->madrasa_type;
        $this->address = (string) $tenant->address;
        $this->status = $tenant->status;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'eiin' => ['nullable', 'string', 'max:50'],
            'madrasaType' => ['required', Rule::in(['kitab', 'hifz', 'mixed'])],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in([
                Tenant::STATUS_PENDING,
                Tenant::STATUS_ACTIVE,
                Tenant::STATUS_SUSPENDED,
                Tenant::STATUS_EXPIRED,
            ])],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->tenant->update([
            'name' => $this->name,
            'eiin' => $this->eiin !== '' ? $this->eiin : null,
            'madrasa_type' => $this->madrasaType,
            'address' => $this->address !== '' ? $this->address : null,
            'status' => $this->status,
        ]);

        session()->flash('status', 'তথ্য সংরক্ষণ করা হয়েছে।');
    }

    public function render(): View
    {
        return view('livewire.central.tenants.tenant-edit');
    }
}
