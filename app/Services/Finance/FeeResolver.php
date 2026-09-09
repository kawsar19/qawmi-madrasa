<?php

declare(strict_types=1);

namespace App\Services\Finance;

use App\Models\Finance\FeeHead;
use App\Models\Finance\FeeStructure;
use App\Models\Finance\StudentDiscount;
use App\Models\Finance\StudentFeeOverride;
use App\Models\People\Student;

/**
 * এক ছাত্রের এক খাতে প্রকৃত টাকা কত।
 *
 * রেট দুই স্তরে: ছাত্রভিত্তিক override আগে, না থাকলে জামাতের সাধারণ
 * structure. তার *উপরে* ছাড় বসে।
 *
 * বিল রান ও আদায় স্ক্রিন — দুটোই এই এক জায়গা থেকে হিসাব নেয়, নইলে
 * একই ছাত্রের বেতন দুই স্ক্রিনে দুরকম দেখাত।
 */
class FeeResolver
{
    /**
     * @return array{rate: float, discount: float, amount: float, discount_type: string|null}
     */
    public function resolve(
        Student $student,
        FeeHead $feeHead,
        int $sessionId,
        int $jamaatId,
    ): array {
        $rate = $this->rateFor($student, $feeHead, $sessionId, $jamaatId);

        if ($rate <= 0) {
            return ['rate' => 0.0, 'discount' => 0.0, 'amount' => 0.0, 'discount_type' => null];
        }

        $discount = $this->discountFor($student, $feeHead, $sessionId);

        if ($discount === null) {
            return ['rate' => $rate, 'discount' => 0.0, 'amount' => $rate, 'discount_type' => null];
        }

        $off = $discount->amountFor($rate);

        return [
            'rate' => $rate,
            'discount' => $off,
            'amount' => round($rate - $off, 2),
            'discount_type' => $discount->type,
        ];
    }

    /**
     * ছাড়ের আগের রেট — override থাকলে সেটাই, নইলে structure.
     */
    public function rateFor(
        Student $student,
        FeeHead $feeHead,
        int $sessionId,
        int $jamaatId,
    ): float {
        $override = StudentFeeOverride::query()
            ->where('student_id', $student->id)
            ->where('academic_session_id', $sessionId)
            ->where('fee_head_id', $feeHead->id)
            ->value('amount');

        if ($override !== null) {
            return round((float) $override, 2);
        }

        // আবাসিক ধরনভিত্তিক রেট আগে; না থাকলে সবার জন্য একই রেট
        // (residency_type খালি) — তাই ORDER BY, LIMIT 1.
        $amount = FeeStructure::query()
            ->active()
            ->where('academic_session_id', $sessionId)
            ->where('jamaat_id', $jamaatId)
            ->where('fee_head_id', $feeHead->id)
            ->where(function ($query) use ($student): void {
                $query->where('residency_type', $student->residency_type)
                    ->orWhereNull('residency_type');
            })
            ->orderByRaw('residency_type IS NULL')
            ->value('amount');

        return round((float) $amount, 2);
    }

    /**
     * প্রযোজ্য ছাড় — নির্দিষ্ট খাতের ছাড় সব-খাতের ছাড়কে হারায়।
     */
    private function discountFor(Student $student, FeeHead $feeHead, int $sessionId): ?StudentDiscount
    {
        return StudentDiscount::query()
            ->active()
            ->where('student_id', $student->id)
            ->where('academic_session_id', $sessionId)
            ->where(function ($query) use ($feeHead): void {
                $query->where('fee_head_id', $feeHead->id)
                    ->orWhereNull('fee_head_id');
            })
            // খাত-নির্দিষ্ট ছাড় আগে।
            ->orderByRaw('fee_head_id IS NULL')
            ->first();
    }
}
