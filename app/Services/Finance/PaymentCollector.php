<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\People\Student;
use App\Models\User;
use App\Services\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * টাকা আদায় ও রসিদ বাতিল।
 *
 * এক রসিদের টাকা একাধিক ইনভয়েসে বসে, তাই অভিভাবক ৩ মাসের বেতন
 * একসাথে দিলেও একটাই কাগজ পান।
 */
class PaymentCollector
{
    public function __construct(private readonly DocumentNumberService $numbers) {}

    /**
     * টাকা নিয়ে রসিদ তৈরি করে ও বকেয়া ইনভয়েসে বসিয়ে দেয়।
     *
     * @param  list<int>|null  $invoiceIds  নির্দিষ্ট বিল বাছা থাকলে; নইলে
     *                                      পুরনো বকেয়া থেকে ক্রমানুসারে।
     */
    public function collect(
        Student $student,
        int $sessionId,
        float $amount,
        array $attributes = [],
        ?array $invoiceIds = null,
    ): Payment {
        if ($amount <= 0) {
            throw new RuntimeException('আদায়ের পরিমাণ শূন্যের বেশি হতে হবে।');
        }

        return DB::transaction(function () use ($student, $sessionId, $amount, $attributes, $invoiceIds): Payment {
            $payment = Payment::create([
                'student_id' => $student->id,
                'academic_session_id' => $sessionId,
                'receipt_no' => $this->numbers->nextFormatted(
                    'receipt_no',
                    "session:{$sessionId}",
                    'RCP-',
                ),
                'paid_on' => $attributes['paid_on'] ?? now()->toDateString(),
                'amount' => round($amount, 2),
                'method' => $attributes['method'] ?? Payment::METHOD_CASH,
                'reference' => $attributes['reference'] ?? null,
                'received_by' => $attributes['received_by'] ?? auth()->id(),
                'notes' => $attributes['notes'] ?? null,
            ]);

            $this->allocate($payment, $student, $sessionId, $invoiceIds);

            return $payment->refresh();
        });
    }

    /**
     * রসিদ বাতিল — ডিলিট নয়।
     *
     * অভিভাবকের হাতে কাগজের কপি আছে; মুছে ফেললে দুই পক্ষের হিসাব আলাদা
     * হয়ে যায়। বণ্টন ফিরিয়ে নেওয়া হয়, ইনভয়েস আবার বকেয়া হয়।
     */
    public function cancel(Payment $payment, string $reason, ?User $by = null): Payment
    {
        if ($payment->isCancelled()) {
            return $payment;
        }

        return DB::transaction(function () use ($payment, $reason, $by): Payment {
            $invoices = $payment->allocations()->with('invoice')->get()
                ->pluck('invoice')
                ->filter();

            $payment->allocations()->delete();

            $payment->update([
                'cancelled_at' => now(),
                'cancelled_by' => $by === null ? auth()->id() : $by->id,
                'cancel_reason' => $reason,
            ]);

            foreach ($invoices as $invoice) {
                $invoice->recalculatePaid();
            }

            return $payment->refresh();
        });
    }

    /**
     * টাকা ইনভয়েসে বসায় — পুরনো বকেয়া আগে।
     *
     * নইলে চলতি মাস শোধ হয়ে গত মাসের বকেয়া ঝুলে থাকত।
     *
     * @param  list<int>|null  $invoiceIds
     */
    private function allocate(
        Payment $payment,
        Student $student,
        int $sessionId,
        ?array $invoiceIds,
    ): void {
        $query = Invoice::query()
            ->where('student_id', $student->id)
            ->where('academic_session_id', $sessionId)
            ->outstanding();

        if ($invoiceIds !== null && $invoiceIds !== []) {
            $query->whereIn('id', $invoiceIds);
        }

        $remaining = round((float) $payment->amount, 2);

        foreach ($query->get() as $invoice) {
            if ($remaining <= 0) {
                break;
            }

            $due = $invoice->dueAmount();

            if ($due <= 0) {
                continue;
            }

            $applied = min($due, $remaining);

            $payment->allocations()->create([
                'invoice_id' => $invoice->id,
                'amount' => $applied,
            ]);

            $invoice->recalculatePaid();

            $remaining = round($remaining - $applied, 2);
        }

        // যা বাকি থাকল তা অগ্রিম জমা — Payment::unallocatedAmount() এটাই
        // দেখায়। পরের মাসের বিল হলে তখন বসানো যাবে।
    }
}
