<?php

declare(strict_types=1);

namespace App\Services\People;

use App\Models\People\Enrollment;
use App\Models\People\Guardian;
use App\Models\People\Student;
use App\Services\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;

/**
 * ছাত্র ভর্তি — ছাত্র, অভিভাবক ও এনরোলমেন্ট একটি ট্রানজেকশনে।
 *
 * Kept out of the Livewire component so the admission flow (phase 1, block 4)
 * can reuse exactly this path instead of duplicating the UID/roll rules.
 */
class StudentRegistrar
{
    public function __construct(private DocumentNumberService $numbers) {}

    /**
     * @param  array<string, mixed>  $student  students table attributes
     * @param  array<string, mixed>|null  $guardian  name/relation/mobile/...
     * @param  array<string, mixed>|null  $enrollment  session/jamaat/section
     */
    public function register(array $student, ?array $guardian = null, ?array $enrollment = null): Student
    {
        return DB::transaction(function () use ($student, $guardian, $enrollment): Student {
            // Race-safe: never MAX(uid)+1 — two clerks admitting at the same
            // moment would otherwise produce the same permanent id.
            $student['student_uid'] ??= $this->numbers->nextFormatted('student_uid', '', '', 6);

            $created = Student::create($student);

            if ($guardian !== null && ($guardian['name'] ?? '') !== '') {
                $this->attachGuardian($created, $guardian);
            }

            if ($enrollment !== null && ($enrollment['jamaat_id'] ?? null) !== null) {
                $this->enroll($created, $enrollment);
            }

            return $created;
        });
    }

    /**
     * অভিভাবক যুক্ত করা — একই মোবাইল নম্বরের অভিভাবক থাকলে তাকেই ব্যবহার করা হয়।
     *
     * @param  array<string, mixed>  $data
     */
    public function attachGuardian(Student $student, array $data): Guardian
    {
        $mobile = $data['mobile'] ?? null;

        // ভাইবোনের অভিভাবক দুবার তৈরি না হওয়ার জন্য।
        $guardian = $mobile !== null && $mobile !== ''
            ? Guardian::query()->where('mobile', $mobile)->where('name', $data['name'])->first()
            : null;

        $guardian ??= Guardian::create($data);

        $student->guardians()->syncWithoutDetaching([
            $guardian->getKey() => ['is_primary' => true, 'tenant_id' => tenant()->getTenantKey()],
        ]);

        return $guardian;
    }

    /**
     * এনরোলমেন্ট — রোল না দিলে ক্রমিক নম্বর বরাদ্দ হয়।
     *
     * @param  array<string, mixed>  $data
     */
    public function enroll(Student $student, array $data): Enrollment
    {
        $sessionId = (int) $data['academic_session_id'];
        $jamaatId = (int) $data['jamaat_id'];

        // রোল বর্ষ+ক্লাস ভিত্তিক — তাই scope key-তে দুটোই।
        $roll = $data['roll_no'] ?? null;
        $roll = $roll === null || $roll === ''
            ? $this->numbers->next('roll_no', "session:{$sessionId}|jamaat:{$jamaatId}")
            : (int) $roll;

        return Enrollment::create([
            'student_id' => $student->getKey(),
            'academic_session_id' => $sessionId,
            'jamaat_id' => $jamaatId,
            'section_id' => $data['section_id'] ?? null,
            'roll_no' => $roll,
            'status' => $data['status'] ?? Enrollment::STATUS_STUDYING,
            'enrolled_on' => $data['enrolled_on'] ?? now()->toDateString(),
        ]);
    }
}
