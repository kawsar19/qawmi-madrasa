<?php

declare(strict_types=1);

namespace App\Services\People;

use App\Models\People\Admission;
use App\Models\People\Student;
use App\Services\Support\DocumentNumberService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * ভর্তি আবেদন — গ্রহণ, অনুমোদন, বাতিল ও এক ক্লিকে ভর্তি।
 *
 * The enrol step deliberately delegates to StudentRegistrar rather than
 * writing students/guardians/enrollments itself: the UID and roll rules live
 * in one place, so the two entry points can never drift apart.
 */
class AdmissionService
{
    public function __construct(
        private DocumentNumberService $numbers,
        private StudentRegistrar $registrar,
    ) {}

    /**
     * আবেদন গ্রহণ।
     *
     * @param  array<string, mixed>  $attributes
     */
    public function apply(array $attributes): Admission
    {
        return DB::transaction(function () use ($attributes): Admission {
            $sessionId = (int) $attributes['academic_session_id'];

            // আবেদন নম্বর বর্ষভিত্তিক — প্রতি বছর ১ থেকে শুরু, যেভাবে
            // মাদরাসাগুলো কাগজে লেখে।
            $attributes['application_no'] ??= $this->numbers->nextFormatted(
                'admission_no',
                "session:{$sessionId}",
                '',
                4,
            );

            $attributes['applied_on'] ??= now()->toDateString();
            $attributes['status'] ??= Admission::STATUS_APPLIED;

            return Admission::create($attributes);
        });
    }

    /**
     * অনুমোদন — এখনো ছাত্র তৈরি হয় না, শুধু সিদ্ধান্ত রেকর্ড হয়।
     */
    public function approve(Admission $admission, ?string $remarks = null): Admission
    {
        $this->assertPending($admission);

        $admission->update([
            'status' => Admission::STATUS_APPROVED,
            'remarks' => $remarks ?? $admission->remarks,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return $admission;
    }

    public function reject(Admission $admission, ?string $remarks = null): Admission
    {
        $this->assertPending($admission);

        $admission->update([
            'status' => Admission::STATUS_REJECTED,
            'remarks' => $remarks ?? $admission->remarks,
            'decided_by' => auth()->id(),
            'decided_at' => now(),
        ]);

        return $admission;
    }

    /**
     * এক ক্লিকে ভর্তি — ছাত্র + অভিভাবক + এনরোলমেন্ট + UID + রোল
     * একটি ট্রানজেকশনে।
     *
     * @param  int|null  $rollNo  খালি রাখলে ক্রমিক নম্বর বসবে
     */
    public function enroll(Admission $admission, ?int $rollNo = null): Student
    {
        if ($admission->isEnrolled()) {
            throw new RuntimeException("{$admission->name} আগেই ভর্তি হয়েছে।");
        }

        if ($admission->status !== Admission::STATUS_APPROVED) {
            throw new RuntimeException('অনুমোদিত আবেদনই কেবল ভর্তি করা যায়।');
        }

        return DB::transaction(function () use ($admission, $rollNo): Student {
            $student = $this->registrar->register(
                $this->studentAttributes($admission),
                $this->guardianAttributes($admission),
                [
                    'academic_session_id' => $admission->academic_session_id,
                    'jamaat_id' => $admission->jamaat_id,
                    'roll_no' => $rollNo,
                ],
            );

            // Same transaction as the student: a failure here must not leave
            // an admission marked enrolled with no student behind it.
            $admission->update([
                'status' => Admission::STATUS_ENROLLED,
                'student_id' => $student->getKey(),
            ]);

            return $student;
        });
    }

    /**
     * আবেদনের তথ্য → ছাত্রের কলাম।
     *
     * @return array<string, mixed>
     */
    private function studentAttributes(Admission $admission): array
    {
        return [
            'name' => $admission->name,
            'name_ar' => $admission->name_ar,
            'father_name' => $admission->father_name,
            'mother_name' => $admission->mother_name,
            'date_of_birth' => $admission->date_of_birth,
            'birth_certificate_no' => $admission->birth_certificate_no,
            'mobile' => $admission->mobile,
            'village' => $admission->village,
            'post_office' => $admission->post_office,
            'union' => $admission->union,
            'upazila' => $admission->upazila,
            'district' => $admission->district,
            // These three are NOT NULL with a DB default. Passing an explicit
            // null overrides the default and fails the constraint, so fall
            // back rather than copying a null straight through.
            'residency_type' => $admission->residency_type ?? Student::RESIDENCY_NON_RESIDENTIAL,
            'is_orphan' => (bool) $admission->is_orphan,
            'is_poor' => (bool) $admission->is_poor,
            'admitted_on' => now()->toDateString(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function guardianAttributes(Admission $admission): ?array
    {
        if (($admission->guardian_name ?? '') === '') {
            return null;
        }

        return [
            'name' => $admission->guardian_name,
            'relation' => $admission->guardian_relation,
            'mobile' => $admission->guardian_mobile,
        ];
    }

    private function assertPending(Admission $admission): void
    {
        if (! $admission->isPending()) {
            throw new RuntimeException("{$admission->name} — এই আবেদনের সিদ্ধান্ত আগেই নেওয়া হয়েছে।");
        }
    }
}
