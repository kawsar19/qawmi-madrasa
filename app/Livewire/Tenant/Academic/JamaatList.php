<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Academic;

use App\Models\Academic\Jamaat;
use App\Models\Academic\Marhala;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * ক্লাস (জামাত) ব্যবস্থাপনা — বিভাগের ভেতরের শ্রেণিগুলো।
 *
 * A hifz section names its classes "১০ পারা / ২০ পারা", a maktab names them
 * "কায়দা / আমপারা" — so the name is free text, not a fixed list.
 */
class JamaatList extends Component
{
    use AuthorizesRequests;

    /** তালিকা ফিল্টার; খালি মানে সব বিভাগ। */
    public string $filterMarhala = '';

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $marhalaId = '';

    public string $name = '';

    public string $nameAr = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'marhalaId' => [
                'required',
                Rule::exists('marhalas', 'id')->where('tenant_id', tenant('id')),
            ],
            // Unique per bibhag, and tenant-scoped by hand because Laravel's
            // `unique` does not see the global scope.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('jamaats', 'name')
                    ->where('tenant_id', tenant('id'))
                    ->where('marhala_id', $this->marhalaId !== '' ? (int) $this->marhalaId : null)
                    ->ignore($this->editingId)
                    ->whereNull('deleted_at'),
            ],
            'nameAr' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'marhalaId' => 'বিভাগ',
            'name' => 'নাম',
            'nameAr' => 'আরবি নাম',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.unique' => 'এই বিভাগে একই নামের ক্লাস আগে থেকেই আছে।',
        ];
    }

    public function create(): void
    {
        $this->authorize('academic.jamaat.create');

        $this->resetForm();

        // ফিল্টার করা থাকলে ওই বিভাগটিই আগে থেকে বাছা থাকবে।
        $this->marhalaId = $this->filterMarhala;

        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('academic.jamaat.update');

        $jamaat = Jamaat::findOrFail($id);

        $this->editingId = $jamaat->id;
        $this->marhalaId = (string) $jamaat->marhala_id;
        $this->name = $jamaat->name;
        $this->nameAr = (string) $jamaat->name_ar;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize($this->editingId === null
            ? 'academic.jamaat.create'
            : 'academic.jamaat.update');

        $this->validate();

        $attributes = [
            'marhala_id' => (int) $this->marhalaId,
            'name' => $this->name,
            'name_ar' => $this->nameAr !== '' ? $this->nameAr : null,
        ];

        if ($this->editingId === null) {
            // Ordered within the bibhag, so classes stay in teaching order.
            $attributes['sort_order'] = (int) Jamaat::query()
                ->where('marhala_id', $attributes['marhala_id'])
                ->max('sort_order') + 10;

            $jamaat = Jamaat::create($attributes);

            session()->flash('status', "{$jamaat->name} — ক্লাস যোগ করা হয়েছে।");
        } else {
            $jamaat = Jamaat::findOrFail($this->editingId);
            $jamaat->update($attributes);

            session()->flash('status', "{$jamaat->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('academic.jamaat.update');

        $jamaat = Jamaat::findOrFail($id);
        $jamaat->update(['is_active' => ! $jamaat->is_active]);

        session()->flash('status', $jamaat->is_active
            ? "{$jamaat->name} — চালু করা হয়েছে।"
            : "{$jamaat->name} — বন্ধ করা হয়েছে।");
    }

    public function delete(int $id): void
    {
        $this->authorize('academic.jamaat.delete');

        $jamaat = Jamaat::withCount(['sections', 'jamaatKitabs'])->findOrFail($id);

        // Sections and curriculum rows cascade at the database level; deleting
        // a class in use would silently take a year of marks setup with it.
        if ($jamaat->sections_count > 0 || $jamaat->jamaat_kitabs_count > 0) {
            session()->flash('error', "{$jamaat->name}-এ শাখা বা কিতাব যুক্ত আছে। মুছে ফেলার বদলে ক্লাসটি বন্ধ করে দিন।");

            return;
        }

        $name = $jamaat->name;
        $jamaat->delete();

        session()->flash('status', "{$name} — মুছে ফেলা হয়েছে।");
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->marhalaId = '';
        $this->name = '';
        $this->nameAr = '';
        $this->resetValidation();
    }

    /**
     * @return Collection<int, Marhala>
     */
    private function marhalaOptions(): Collection
    {
        return Marhala::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * @return Collection<int, Jamaat>
     */
    private function jamaats(): Collection
    {
        return Jamaat::with('marhala')
            ->withCount('sections')
            ->when($this->filterMarhala !== '', fn ($query) => $query->where('marhala_id', (int) $this->filterMarhala))
            ->orderBy('marhala_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        $this->authorize('academic.jamaat.view');

        return view('livewire.tenant.academic.jamaat-list', [
            'jamaats' => $this->jamaats(),
            'marhalaOptions' => $this->marhalaOptions(),
        ]);
    }
}
