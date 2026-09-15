<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\Finance;

use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\People\Student;
use App\Services\Academic\CurrentSession;
use App\Services\Finance\PaymentCollector;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Throwable;

/**
 * আদায় — ছাত্র খুঁজে বকেয়া দেখে টাকা নেওয়া।
 *
 * কয়েক মাসের বকেয়া একসাথে নেওয়া যায় এবং একটাই রসিদ হয়; কোন বিলে কত
 * বসবে তা PaymentCollector ঠিক করে — পুরনো বকেয়া আগে।
 */
class PaymentCollect extends Component
{
    use AuthorizesRequests;

    public string $search = '';

    public ?int $studentId = null;

    public string $amount = '';

    public string $method = Payment::METHOD_CASH;

    public string $reference = '';

    public string $paidOn = '';

    public string $notes = '';

    /** সদ্য কাটা রসিদ — স্ক্রিনে লিংক দেখানোর জন্য। */
    public ?int $lastPaymentId = null;

    /**
     * @return array<string, string>
     */
    public static function methods(): array
    {
        return [
            Payment::METHOD_CASH => 'নগদ',
            Payment::METHOD_BANK => 'ব্যাংক',
            Payment::METHOD_MOBILE => 'মোবাইল ব্যাংকিং',
        ];
    }

    public function mount(): void
    {
        $this->paidOn = now()->toDateString();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'studentId' => [
                'required',
                Rule::exists('students', 'id')->where('tenant_id', tenant('id')),
            ],
            // বকেয়ার চেয়ে বেশি নেওয়া যায় — বাকিটা অগ্রিম জমা থাকে।
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999999'],
            'method' => ['required', Rule::in(array_keys(self::methods()))],
            'reference' => ['nullable', 'string', 'max:255'],
            'paidOn' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'studentId' => 'ছাত্র',
            'amount' => 'টাকা',
            'method' => 'পদ্ধতি',
            'paidOn' => 'তারিখ',
        ];
    }

    public function selectStudent(int $id): void
    {
        $this->authorize('finance.payment.create');

        $this->studentId = $id;
        $this->lastPaymentId = null;
        $this->resetValidation();

        // বকেয়ার পুরো অঙ্কই আগে থেকে বসানো — বেশিরভাগ সময় এটাই নেওয়া হয়।
        $this->amount = (string) $this->outstandingTotal();
    }

    public function clearStudent(): void
    {
        $this->studentId = null;
        $this->amount = '';
        $this->reference = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function save(PaymentCollector $collector, CurrentSession $currentSession): void
    {
        $this->authorize('finance.payment.create');

        $session = $currentSession->get();

        if ($session === null) {
            session()->flash('error', 'চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই।');

            return;
        }

        $this->validate();

        $student = Student::findOrFail($this->studentId);

        try {
            $payment = $collector->collect(
                $student,
                $session->id,
                (float) $this->amount,
                [
                    'paid_on' => $this->paidOn,
                    'method' => $this->method,
                    'reference' => $this->reference !== '' ? $this->reference : null,
                    'notes' => $this->notes !== '' ? $this->notes : null,
                ],
            );
        } catch (Throwable $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        $this->lastPaymentId = $payment->id;
        $this->amount = '';
        $this->reference = '';
        $this->notes = '';
        $this->resetValidation();

        session()->flash('status', "রসিদ {$payment->receipt_no} — {$student->name}-এর আদায় সম্পন্ন।");
    }

    /**
     * বাছা ছাত্রের মোট বকেয়া।
     */
    public function outstandingTotal(): float
    {
        $total = 0.0;

        foreach ($this->outstandingInvoices() as $invoice) {
            $total += $invoice->dueAmount();
        }

        return round($total, 2);
    }

    /**
     * @return Collection<int, Invoice>
     */
    private function outstandingInvoices(): Collection
    {
        if ($this->studentId === null) {
            return new Collection;
        }

        return Invoice::query()
            ->where('student_id', $this->studentId)
            ->outstanding()
            ->get();
    }

    /**
     * নাম, আইডি বা মোবাইল দিয়ে ছাত্র খোঁজা।
     *
     * @return Collection<int, Student>
     */
    private function results(): Collection
    {
        if ($this->studentId !== null || mb_strlen($this->search) < 2) {
            return new Collection;
        }

        $term = Search::term($this->search);

        return Student::query()
            ->active()
            ->where(function ($query) use ($term): void {
                Search::anyOf($query, ['name', 'student_uid', 'father_name', 'mobile'], $term);
            })
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function render(): View
    {
        $this->authorize('finance.payment.view');

        return view('livewire.tenant.finance.payment-collect', [
            'results' => $this->results(),
            'student' => $this->studentId !== null ? Student::find($this->studentId) : null,
            'invoices' => $this->outstandingInvoices(),
            'outstanding' => $this->outstandingTotal(),
            'methodLabels' => self::methods(),
            'lastPayment' => $this->lastPaymentId !== null ? Payment::find($this->lastPaymentId) : null,
        ]);
    }
}
