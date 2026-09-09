<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Finance;

use App\Models\Finance\FeeHead;
use App\Models\Finance\Invoice;
use App\Services\Academic\CurrentSession;
use App\Services\Finance\InvoiceGenerator;
use App\Support\Bn;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * মাসিক বিল রান ও বিলের তালিকা।
 *
 * একই মাসে দুবার চালালে দ্বিগুণ বিল হয় না — যাদের বিল আগেই আছে তারা
 * বাদ পড়ে, আর কতটা বাদ পড়ল তা জানিয়ে দেওয়া হয়।
 */
class InvoiceRun extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $billingMonth = '';

    public string $filterMonth = '';

    public string $filterStatus = '';

    /**
     * এই রানে কোন খাতগুলো বিলে বসবে।
     *
     * খালি থাকলে নিয়মিত খাত (মাসিক বেতন, সিট ভাড়া)। পরীক্ষার ফি বছরে
     * একবার, তাই সেটি হাতে বেছে নিতে হয়।
     *
     * @var list<int>
     */
    public array $selectedHeads = [];

    public function mount(): void
    {
        $this->billingMonth = now()->format('Y-m');
        $this->filterMonth = $this->billingMonth;

        // ডিফল্টে নিয়মিত খাতগুলো টিক দেওয়া থাকে।
        $this->selectedHeads = $this->recurringHeadIds();
    }

    /**
     * @return list<int>
     */
    private function recurringHeadIds(): array
    {
        return FeeHead::query()
            ->where('is_active', true)
            ->whereIn('type', InvoiceGenerator::recurringTypes())
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    public function updatedFilterMonth(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            Invoice::STATUS_UNPAID => 'বকেয়া',
            Invoice::STATUS_PARTIAL => 'আংশিক',
            Invoice::STATUS_PAID => 'পরিশোধিত',
        ];
    }

    public function generate(InvoiceGenerator $generator, CurrentSession $currentSession): void
    {
        $this->authorize('finance.invoice.generate');

        $session = $currentSession->get();

        if ($session === null) {
            session()->flash('error', 'চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই।');

            return;
        }

        // "2026-01" — মাসটাই একক।
        $this->validate([
            'billingMonth' => ['required', 'regex:/^\d{4}-\d{2}$/'],
        ], [], ['billingMonth' => 'মাস']);

        if ($this->selectedHeads === []) {
            session()->flash('error', 'অন্তত একটি ফি খাত বাছাই করুন।');

            return;
        }

        $result = $generator->run($session, $this->billingMonth, $this->selectedHeads);

        $this->filterMonth = $this->billingMonth;
        $this->resetPage();

        if ($result['created'] === 0 && $result['skipped'] === 0) {
            session()->flash('error', 'কোনো ছাত্র বা রেট পাওয়া যায়নি। আগে ফি স্ট্রাকচার বসান।');

            return;
        }

        $message = Bn::num($result['created']).' টি বিল তৈরি হয়েছে।';

        if ($result['skipped'] > 0) {
            // দুবার চালানোর স্বাভাবিক ফল — ত্রুটি নয়।
            $message .= ' '.Bn::num($result['skipped']).' জনের বিল আগেই ছিল।';
        }

        session()->flash('status', $message);
    }

    /**
     * @return LengthAwarePaginator<int, Invoice>
     */
    private function invoices(): LengthAwarePaginator
    {
        return Invoice::query()
            ->with(['student', 'jamaat'])
            ->when($this->filterMonth !== '', fn ($query) => $query->where('billing_month', $this->filterMonth))
            ->when($this->filterStatus !== '', fn ($query) => $query->where('status', $this->filterStatus))
            ->orderByDesc('billing_month')
            ->orderBy('id')
            ->paginate(20);
    }

    public function render(CurrentSession $currentSession): View
    {
        $this->authorize('finance.invoice.view');

        return view('livewire.tenant.finance.invoice-run', [
            'invoices' => $this->invoices(),
            'statusLabels' => self::statuses(),
            'feeHeadOptions' => FeeHead::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(),
            'currentSession' => $currentSession->get(),
        ]);
    }
}
