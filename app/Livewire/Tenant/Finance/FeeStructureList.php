<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Finance;

use App\Models\Academic\Jamaat;
use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeStructure;
use App\Models\People\Student;
use App\Services\Academic\CurrentSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * ফি স্ট্রাকচার — কোন জামাতের কোন খাতে কত টাকা।
 *
 * রেট ক্লাসভিত্তিক বলে ২০০ ছাত্রের জন্য ২০০টা সারি লাগে না; ছাত্রভিত্তিক
 * ব্যতিক্রম ও ছাড় আলাদা স্ক্রিনে।
 */
class FeeStructureList extends Component
{
    use AuthorizesRequests;

    public string $filterJamaat = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $jamaatId = '';

    public string $feeHeadId = '';

    public string $residencyType = '';

    public string $amount = '';

    public bool $isActive = true;

    /**
     * আবাসিক ধরন — খালি মানে সব ধরনের ছাত্রের জন্য একই রেট।
     *
     * @return array<string, string>
     */
    public static function residencyTypes(): array
    {
        return [
            Student::RESIDENCY_RESIDENTIAL => 'আবাসিক',
            Student::RESIDENCY_NON_RESIDENTIAL => 'অনাবাসিক',
            Student::RESIDENCY_DAY_CARE => 'ডে-কেয়ার',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'jamaatId' => [
                'required',
                Rule::exists('jamaats', 'id')->where('tenant_id', tenant('id')),
            ],
            'feeHeadId' => [
                'required',
                Rule::exists('fee_heads', 'id')->where('tenant_id', tenant('id')),
            ],
            'residencyType' => ['nullable', Rule::in(array_keys(self::residencyTypes()))],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'jamaatId' => 'ক্লাস',
            'feeHeadId' => 'ফি খাত',
            'residencyType' => 'আবাসিক ধরন',
            'amount' => 'টাকা',
        ];
    }

    public function create(): void
    {
        $this->authorize('finance.fee_structure.create');

        $this->resetForm();

        // ফিল্টার করা থাকলে ওই ক্লাসটিই আগে থেকে বাছা থাকবে।
        $this->jamaatId = $this->filterJamaat;

        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('finance.fee_structure.update');

        $structure = FeeStructure::findOrFail($id);

        $this->editingId = $structure->id;
        $this->jamaatId = (string) $structure->jamaat_id;
        $this->feeHeadId = (string) $structure->fee_head_id;
        $this->residencyType = (string) $structure->residency_type;
        $this->amount = (string) $structure->amount;
        $this->isActive = $structure->is_active;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(CurrentSession $currentSession): void
    {
        $this->authorize($this->editingId === null
            ? 'finance.fee_structure.create'
            : 'finance.fee_structure.update');

        $session = $currentSession->get();

        if ($session === null) {
            session()->flash('error', 'চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই।');

            return;
        }

        $this->validate();

        $attributes = [
            'academic_session_id' => $session->id,
            'jamaat_id' => (int) $this->jamaatId,
            'fee_head_id' => (int) $this->feeHeadId,
            'residency_type' => $this->residencyType !== '' ? $this->residencyType : null,
            'amount' => $this->amount,
            'is_active' => $this->isActive,
        ];

        // একই জামাত+খাত+ধরনের রেট দুবার বসানো যাবে না — ডাটাবেসের unique
        // index আটকাবে, তাই আগেই বলে দিই।
        $duplicate = FeeStructure::query()
            ->where('academic_session_id', $attributes['academic_session_id'])
            ->where('jamaat_id', $attributes['jamaat_id'])
            ->where('fee_head_id', $attributes['fee_head_id'])
            ->where('residency_type', $attributes['residency_type'])
            ->when($this->editingId !== null, fn ($query) => $query->whereKeyNot($this->editingId))
            ->exists();

        if ($duplicate) {
            $this->addError('feeHeadId', 'এই ক্লাসে এই খাতের রেট আগে থেকেই বসানো আছে।');

            return;
        }

        if ($this->editingId === null) {
            FeeStructure::create($attributes);

            session()->flash('status', 'রেট যোগ করা হয়েছে।');
        } else {
            FeeStructure::findOrFail($this->editingId)->update($attributes);

            session()->flash('status', 'রেট সংরক্ষণ করা হয়েছে।');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $this->authorize('finance.fee_structure.delete');

        FeeStructure::findOrFail($id)->delete();

        session()->flash('status', 'রেট মুছে ফেলা হয়েছে।');
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->jamaatId = '';
        $this->feeHeadId = '';
        $this->residencyType = '';
        $this->amount = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    /**
     * @return Collection<int, FeeStructure>
     */
    private function structures(CurrentSession $currentSession): Collection
    {
        $sessionId = $currentSession->id();

        if ($sessionId === null) {
            return new Collection;
        }

        return FeeStructure::with(['jamaat', 'feeHead'])
            ->where('academic_session_id', $sessionId)
            ->when($this->filterJamaat !== '', fn ($query) => $query->where('jamaat_id', (int) $this->filterJamaat))
            ->orderBy('jamaat_id')
            ->orderBy('fee_head_id')
            ->get();
    }

    /**
     * @return Collection<int, Jamaat>
     */
    private function jamaatOptions(): Collection
    {
        return Jamaat::query()->orderBy('marhala_id')->orderBy('sort_order')->get();
    }

    /**
     * @return Collection<int, FeeHead>
     */
    private function feeHeadOptions(): Collection
    {
        return FeeHead::query()->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function render(CurrentSession $currentSession): View
    {
        $this->authorize('finance.fee_structure.view');

        return view('livewire.tenant.finance.fee-structure-list', [
            'structures' => $this->structures($currentSession),
            'jamaatOptions' => $this->jamaatOptions(),
            'feeHeadOptions' => $this->feeHeadOptions(),
            'residencyLabels' => self::residencyTypes(),
            'currentSession' => $currentSession->get(),
        ]);
    }
}
