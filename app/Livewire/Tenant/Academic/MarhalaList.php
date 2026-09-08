<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Academic;

use App\Models\Academic\Marhala;
use App\Support\BnSlug;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * বিভাগ (মারহালা) ব্যবস্থাপনা — মক্তব, হিফজ, কিতাব ইত্যাদি।
 *
 * The UI calls these "বিভাগ" because that is what madrasa staff say; the table
 * stays `marhalas`. `track` is set from a plain dropdown rather than shown as
 * a separate concept — it drives behaviour (fees, boarding, board exams) but
 * nobody thinks of it as a second layer.
 */
class MarhalaList extends Component
{
    use AuthorizesRequests;

    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $nameAr = '';

    public string $track = Marhala::TRACK_KITAB;

    public string $durationYears = '';

    /**
     * @return array<string, string>
     */
    public static function tracks(): array
    {
        return [
            Marhala::TRACK_KITAB => 'কিতাব',
            Marhala::TRACK_HIFZ => 'হিফজ',
            Marhala::TRACK_NAZERA => 'নাযেরা',
            Marhala::TRACK_QIRAT => 'কিরাআত',
            Marhala::TRACK_IFTA => 'ইফতা',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            // Scoped by hand: Laravel's `unique` ignores the tenant global
            // scope, so two madrasas could not reuse the same name otherwise.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('marhalas', 'name')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($this->editingId)
                    ->whereNull('deleted_at'),
            ],
            'nameAr' => ['nullable', 'string', 'max:255'],
            'track' => ['required', Rule::in(array_keys(self::tracks()))],
            'durationYears' => ['nullable', 'integer', 'min:1', 'max:15'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'নাম',
            'nameAr' => 'আরবি নাম',
            'track' => 'ধরন',
            'durationYears' => 'সময়কাল',
        ];
    }

    public function create(): void
    {
        $this->authorize('academic.marhala.create');

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('academic.marhala.update');

        $marhala = Marhala::findOrFail($id);

        $this->editingId = $marhala->id;
        $this->name = $marhala->name;
        $this->nameAr = (string) $marhala->name_ar;
        $this->track = $marhala->track;
        $this->durationYears = (string) $marhala->duration_years;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize($this->editingId === null
            ? 'academic.marhala.create'
            : 'academic.marhala.update');

        $this->validate();

        $attributes = [
            'name' => $this->name,
            'name_ar' => $this->nameAr !== '' ? $this->nameAr : null,
            'track' => $this->track,
            'duration_years' => $this->durationYears !== '' ? (int) $this->durationYears : null,
        ];

        if ($this->editingId === null) {
            // `code` is required and unique but means nothing to the user, so
            // it is derived from the name rather than asked for.
            $attributes['code'] = $this->uniqueCode($this->name);
            $attributes['sort_order'] = (int) Marhala::max('sort_order') + 10;

            $marhala = Marhala::create($attributes);

            session()->flash('status', "{$marhala->name} — বিভাগ যোগ করা হয়েছে।");
        } else {
            $marhala = Marhala::findOrFail($this->editingId);
            $marhala->update($attributes);

            session()->flash('status', "{$marhala->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $this->authorize('academic.marhala.update');

        $marhala = Marhala::findOrFail($id);
        $marhala->update(['is_active' => ! $marhala->is_active]);

        session()->flash('status', $marhala->is_active
            ? "{$marhala->name} — চালু করা হয়েছে।"
            : "{$marhala->name} — বন্ধ করা হয়েছে।");
    }

    public function delete(int $id): void
    {
        $this->authorize('academic.marhala.delete');

        $marhala = Marhala::withCount(['jamaats', 'kitabs'])->findOrFail($id);

        // Classes and kitabs cascade on delete at the database level, so a
        // madrasa could wipe a year of curriculum with one click.
        if ($marhala->jamaats_count > 0) {
            session()->flash('error', "{$marhala->name}-এ ক্লাস আছে। আগে ক্লাসগুলো সরান, অথবা বিভাগটি বন্ধ করে দিন।");

            return;
        }

        if ($marhala->kitabs_count > 0) {
            session()->flash('error', "{$marhala->name}-এ কিতাব আছে। আগে কিতাবগুলো সরান, অথবা বিভাগটি বন্ধ করে দিন।");

            return;
        }

        $name = $marhala->name;
        $marhala->delete();

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
        $this->name = '';
        $this->nameAr = '';
        $this->track = Marhala::TRACK_KITAB;
        $this->durationYears = '';
        $this->resetValidation();
    }

    /**
     * নাম থেকে slug; একই slug থাকলে পেছনে সংখ্যা বসে।
     */
    private function uniqueCode(string $name): string
    {
        $base = BnSlug::make($name) ?: 'bibhag';
        $code = $base;
        $suffix = 2;

        while (Marhala::withTrashed()->where('code', $code)->exists()) {
            $code = "{$base}-{$suffix}";
            $suffix++;
        }

        return $code;
    }

    /**
     * @return Collection<int, Marhala>
     */
    private function marhalas(): Collection
    {
        return Marhala::withCount('jamaats')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    public function render(): View
    {
        // Livewire's update endpoint re-renders without the route gate.
        $this->authorize('academic.marhala.view');

        return view('livewire.tenant.academic.marhala-list', [
            'marhalas' => $this->marhalas(),
            'trackLabels' => self::tracks(),
        ]);
    }
}
