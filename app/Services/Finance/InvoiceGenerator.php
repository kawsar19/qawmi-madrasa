<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Academic\AcademicSession;
use App\Models\Finance\FeeHead;
use App\Models\Finance\Invoice;
use App\Models\People\Enrollment;
use App\Services\Support\DocumentNumberService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * মাসিক বিল রান।
 *
 * idempotent — একই মাসে দুবার চালালে দ্বিগুণ বিল হবে না। গ্যারান্টিটা
 * invoices টেবিলের unique index-এ, PHP-র "আগে চালানো হয়েছে কিনা" চেকে
 * নয়: দুটো রিকোয়েস্ট একসাথে এলে দুটোই সেই চেক পাশ করে যেত।
 */
class InvoiceGenerator
{
    public function __construct(
        private readonly FeeResolver $resolver,
        private readonly DocumentNumberService $numbers,
    ) {}

    /**
     * এক মাসের বিল তৈরি করে — যাদের বিল আগেই আছে তারা বাদ।
     *
     * @param  string  $billingMonth  "YYYY-MM"
     * @param  list<int>|null  $feeHeadIds  নির্দিষ্ট খাত বাছা থাকলে সেগুলোই;
     *                                      নইলে প্রতি মাসে যেগুলো আসে।
     * @return array{created: int, skipped: int}
     */
    public function run(AcademicSession $session, string $billingMonth, ?array $feeHeadIds = null): array
    {
        $feeHeads = $this->feeHeadsFor($feeHeadIds);

        if ($feeHeads->isEmpty()) {
            return ['created' => 0, 'skipped' => 0];
        }

        $created = 0;
        $skipped = 0;

        // চলতি বর্ষে যারা পড়ছে কেবল তাদের বিল — ছেড়ে যাওয়া বা উত্তীর্ণ
        // ছাত্রের নয়।
        $enrollments = Enrollment::query()
            ->with('student')
            ->where('academic_session_id', $session->id)
            ->where('status', Enrollment::STATUS_STUDYING)
            ->get();

        foreach ($enrollments as $enrollment) {
            $student = $enrollment->student;

            if ($student === null) {
                continue;
            }

            $invoice = $this->generateFor($enrollment, $feeHeads, $billingMonth);

            $invoice === null ? $skipped++ : $created++;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * এক ছাত্রের এক মাসের বিল। আগেই থাকলে null.
     *
     * @param  Collection<int, FeeHead>  $feeHeads
     */
    public function generateFor(
        Enrollment $enrollment,
        Collection $feeHeads,
        string $billingMonth,
    ): ?Invoice {
        $student = $enrollment->student;

        if ($student === null) {
            return null;
        }

        $lines = [];
        $gross = 0.0;
        $discount = 0.0;

        foreach ($feeHeads as $feeHead) {
            $resolved = $this->resolver->resolve(
                $student,
                $feeHead,
                $enrollment->academic_session_id,
                $enrollment->jamaat_id,
            );

            // যে খাতে রেট বসানো হয়নি সেটি বিলে আসবে না।
            if ($resolved['rate'] <= 0) {
                continue;
            }

            $gross += $resolved['rate'];
            $discount += $resolved['discount'];

            $lines[] = [
                'fee_head_id' => $feeHead->id,
                // খাতের নাম কপি — পরে নাম বদলালেও পুরনো বিল অক্ষত থাকে।
                'fee_head_name' => $feeHead->name,
                'rate' => $resolved['rate'],
                'discount_amount' => $resolved['discount'],
                'amount' => $resolved['amount'],
                'discount_type' => $resolved['discount_type'],
            ];
        }

        if ($lines === []) {
            return null;
        }

        return $this->store($enrollment, $billingMonth, $lines, $gross, $discount);
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    private function store(
        Enrollment $enrollment,
        string $billingMonth,
        array $lines,
        float $gross,
        float $discount,
    ): ?Invoice {
        $net = round($gross - $discount, 2);

        try {
            return DB::transaction(function () use ($enrollment, $billingMonth, $lines, $gross, $discount, $net): Invoice {
                $invoice = Invoice::create([
                    'student_id' => $enrollment->student_id,
                    'academic_session_id' => $enrollment->academic_session_id,
                    'jamaat_id' => $enrollment->jamaat_id,
                    'invoice_no' => $this->numbers->nextFormatted(
                        'invoice_no',
                        "session:{$enrollment->academic_session_id}",
                        'INV-',
                    ),
                    'billing_month' => $billingMonth,
                    'issued_on' => now()->toDateString(),
                    'gross_amount' => round($gross, 2),
                    'discount_amount' => round($discount, 2),
                    'net_amount' => $net,
                    // পূর্ণ মওকুফের ০ টাকার বিলও তৈরি হয় — রেকর্ড থাকে —
                    // কিন্তু সাথে সাথেই পরিশোধিত।
                    'status' => $net <= 0 ? Invoice::STATUS_PAID : Invoice::STATUS_UNPAID,
                ]);

                $invoice->lines()->createMany($lines);

                return $invoice;
            });
        } catch (QueryException $e) {
            // এই মাসের বিল আগেই আছে — unique index আটকে দিয়েছে। দুবার
            // চালানোর স্বাভাবিক ফল, ত্রুটি নয়।
            if ($this->isDuplicate($e)) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * প্রতি মাসে আপনা-আপনি যেসব খাত বিলে আসে।
     *
     * সিট ভাড়া (boarding) এখানে আছে — এটি প্রতি মাসের নির্দিষ্ট ভাড়া,
     * আর যার রেট বসানো হয়নি তার লাইনও তৈরি হয় না, তাই অনাবাসিক ছাত্র
     * এমনিতেই বাদ পড়ে (residency_type দিয়ে রেট আলাদা করা যায়)।
     *
     * ভর্তি বা পরীক্ষার ফি (one_time / exam) স্বয়ংক্রিয়ভাবে আসে না —
     * সেগুলো বছরে একবার, তাই বিল রানে হাতে বেছে দিতে হয়।
     *
     * @return list<string>
     */
    public static function recurringTypes(): array
    {
        return [FeeHead::TYPE_MONTHLY, FeeHead::TYPE_BOARDING];
    }

    /**
     * বিলযোগ্য খাত — বাছা থাকলে সেগুলোই, নইলে নিয়মিত খাতগুলো।
     *
     * @param  list<int>|null  $feeHeadIds
     * @return Collection<int, FeeHead>
     */
    private function feeHeadsFor(?array $feeHeadIds): Collection
    {
        return FeeHead::query()
            ->where('is_active', true)
            ->when(
                $feeHeadIds !== null && $feeHeadIds !== [],
                fn ($query) => $query->whereIn('id', $feeHeadIds),
                fn ($query) => $query->whereIn('type', self::recurringTypes()),
            )
            ->orderBy('sort_order')
            ->get();
    }

    private function isDuplicate(QueryException $e): bool
    {
        // 23000/23505 — SQLite, MySQL ও Postgres সবেতেই integrity violation.
        return in_array($e->getCode(), ['23000', '23505'], true);
    }
}
