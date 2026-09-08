<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Academic;

use App\Models\Academic\AcademicSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * শিক্ষাবর্ষ ব্যবস্থাপনা — তালিকা, তৈরি, সম্পাদনা, চলতি নির্ধারণ ও লক।
 */
class SessionList extends Component
{
    use AuthorizesRequests;

    /** সম্পাদনাধীন বর্ষের id; null মানে নতুন বর্ষ তৈরি হচ্ছে। */
    public ?int $editingId = null;

    public bool $showForm = false;

    public string $name = '';

    public string $hijriYear = '';

    public string $gregorianYear = '';

    public string $startsOn = '';

    public string $endsOn = '';

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            // The global scope does not apply to the `unique` rule, so the
            // tenant has to be added by hand or names would collide globally.
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('academic_sessions', 'name')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($this->editingId)
                    ->whereNull('deleted_at'),
            ],
            'hijriYear' => ['nullable', 'string', 'max:20'],
            'gregorianYear' => ['nullable', 'string', 'max:20'],
            'startsOn' => ['nullable', 'date'],
            'endsOn' => ['nullable', 'date', 'after_or_equal:startsOn'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'নাম',
            'hijriYear' => 'হিজরি বর্ষ',
            'gregorianYear' => 'ইংরেজি বর্ষ',
            'startsOn' => 'শুরুর তারিখ',
            'endsOn' => 'শেষের তারিখ',
        ];
    }

    public function create(): void
    {
        $this->authorize('academic.session.create');

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('academic.session.update');

        $session = AcademicSession::findOrFail($id);

        $this->editingId = $session->id;
        $this->name = $session->name;
        $this->hijriYear = (string) $session->hijri_year;
        $this->gregorianYear = (string) $session->gregorian_year;
        $this->startsOn = $session->starts_on?->format('Y-m-d') ?? '';
        $this->endsOn = $session->ends_on?->format('Y-m-d') ?? '';

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->authorize($this->editingId === null
            ? 'academic.session.create'
            : 'academic.session.update');

        $this->validate();

        $attributes = [
            'name' => $this->name,
            'hijri_year' => $this->hijriYear !== '' ? $this->hijriYear : null,
            'gregorian_year' => $this->gregorianYear !== '' ? $this->gregorianYear : null,
            'starts_on' => $this->startsOn !== '' ? $this->startsOn : null,
            'ends_on' => $this->endsOn !== '' ? $this->endsOn : null,
        ];

        if ($this->editingId === null) {
            $session = AcademicSession::create($attributes);

            // প্রথম বর্ষটিই স্বয়ংক্রিয়ভাবে চলতি হয়, নাহলে ড্যাশবোর্ড
            // "কোনো চলতি শিক্ষাবর্ষ নেই" দেখাতেই থাকবে।
            if (AcademicSession::count() === 1) {
                $session->update(['is_current' => true]);
            }

            session()->flash('status', "{$session->name} — শিক্ষাবর্ষ তৈরি করা হয়েছে।");
        } else {
            $session = AcademicSession::findOrFail($this->editingId);

            if ($session->is_locked) {
                session()->flash('error', "{$session->name} লক করা — সম্পাদনা করতে হলে আগে আনলক করুন।");

                return;
            }

            $session->update($attributes);

            session()->flash('status', "{$session->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function makeCurrent(int $id): void
    {
        $this->authorize('academic.session.update');

        $session = AcademicSession::findOrFail($id);

        // ঠিক একটি বর্ষই চলতি থাকবে — দুটো হয়ে গেলে হাজিরা ও ফি ভুল
        // বর্ষে বসবে, তাই দুটো লেখা এক ট্রানজেকশনে।
        DB::transaction(function () use ($session): void {
            AcademicSession::query()
                ->where('is_current', true)
                ->update(['is_current' => false]);

            $session->update(['is_current' => true]);
        });

        session()->flash('status', "{$session->name} — এখন চলতি শিক্ষাবর্ষ।");
    }

    public function toggleLock(int $id): void
    {
        $this->authorize('academic.session.lock');

        $session = AcademicSession::findOrFail($id);

        $session->update(['is_locked' => ! $session->is_locked]);

        session()->flash('status', $session->is_locked
            ? "{$session->name} লক করা হয়েছে — এই বর্ষে আর পরিবর্তন করা যাবে না।"
            : "{$session->name} আনলক করা হয়েছে।");
    }

    public function delete(int $id): void
    {
        $this->authorize('academic.session.delete');

        $session = AcademicSession::findOrFail($id);

        if ($session->is_current) {
            session()->flash('error', 'চলতি শিক্ষাবর্ষ মুছে ফেলা যাবে না। আগে অন্য একটি বর্ষ চলতি করুন।');

            return;
        }

        if ($session->is_locked) {
            session()->flash('error', "{$session->name} লক করা — মুছতে হলে আগে আনলক করুন।");

            return;
        }

        $name = $session->name;
        $session->delete();

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
        $this->hijriYear = '';
        $this->gregorianYear = '';
        $this->startsOn = '';
        $this->endsOn = '';
        $this->resetValidation();
    }

    /**
     * @return Collection<int, AcademicSession>
     */
    private function sessions(): Collection
    {
        return AcademicSession::query()
            ->orderByDesc('is_current')
            ->orderByDesc('starts_on')
            ->orderByDesc('id')
            ->get();
    }

    public function render(): View
    {
        // The route gate only guards the initial page load; Livewire's own
        // update endpoint re-renders the component without it.
        $this->authorize('academic.session.view');

        return view('livewire.tenant.academic.session-list', [
            'sessions' => $this->sessions(),
        ]);
    }
}
