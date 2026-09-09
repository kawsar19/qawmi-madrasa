<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\People;

use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\People\Admission;
use App\Models\People\Student;
use App\Services\Academic\CurrentSession;
use App\Services\People\AdmissionService;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use RuntimeException;

/**
 * ভর্তি আবেদন — গ্রহণ, অনুমোদন/বাতিল, এক ক্লিকে ভর্তি।
 *
 * Applicants live in `admissions`, not `students`: a rejected application must
 * never inflate the student count. The student row is created only by enroll().
 */
class AdmissionList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $filterStatus = Admission::STATUS_APPLIED;

    public string $filterJamaat = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    // ---- আবেদনকারী ----
    public string $name = '';

    public string $nameAr = '';

    public string $fatherName = '';

    public string $motherName = '';

    public string $dateOfBirth = '';

    public string $birthCertificateNo = '';

    public string $mobile = '';

    // ---- ঠিকানা ----
    public string $village = '';

    public string $postOffice = '';

    public string $union = '';

    public string $upazila = '';

    public string $district = '';

    // ---- পূর্ববর্তী শিক্ষা ----
    public string $previousMadrasa = '';

    public string $previousJamaat = '';

    // ---- অন্যান্য ----
    public string $residencyType = Student::RESIDENCY_NON_RESIDENTIAL;

    public bool $isOrphan = false;

    public bool $isPoor = false;

    public string $remarks = '';

    // ---- অভিভাবক ----
    public string $guardianName = '';

    public string $guardianRelation = '';

    public string $guardianMobile = '';

    // ---- কোন ক্লাসে ----
    public string $jamaatId = '';

    // ভর্তির সময় রোল — খালি রাখলে ক্রমিক।
    public string $enrollRollNo = '';

    public ?int $enrollingId = null;

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            Admission::STATUS_APPLIED => 'আবেদিত',
            Admission::STATUS_APPROVED => 'অনুমোদিত',
            Admission::STATUS_ENROLLED => 'ভর্তি সম্পন্ন',
            Admission::STATUS_REJECTED => 'বাতিল',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedFilterJamaat(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nameAr' => ['nullable', 'string', 'max:255'],
            'fatherName' => ['required', 'string', 'max:255'],
            'motherName' => ['nullable', 'string', 'max:255'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'birthCertificateNo' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'village' => ['nullable', 'string', 'max:255'],
            'postOffice' => ['nullable', 'string', 'max:255'],
            'union' => ['nullable', 'string', 'max:255'],
            'upazila' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'previousMadrasa' => ['nullable', 'string', 'max:255'],
            'previousJamaat' => ['nullable', 'string', 'max:255'],
            'residencyType' => ['required', Rule::in(array_keys(StudentList::residencyTypes()))],
            'remarks' => ['nullable', 'string', 'max:1000'],

            'guardianName' => ['nullable', 'string', 'max:255'],
            'guardianRelation' => ['nullable', Rule::in(array_keys(StudentList::relations()))],
            'guardianMobile' => ['nullable', 'string', 'max:20'],

            'jamaatId' => [
                'required',
                Rule::exists('jamaats', 'id')->where('tenant_id', tenant('id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'নাম',
            'fatherName' => 'পিতার নাম',
            'motherName' => 'মাতার নাম',
            'dateOfBirth' => 'জন্মতারিখ',
            'mobile' => 'মোবাইল',
            'district' => 'জেলা',
            'upazila' => 'উপজেলা',
            'union' => 'ইউনিয়ন',
            'village' => 'গ্রাম',
            'previousMadrasa' => 'পূর্ববর্তী মাদরাসা',
            'residencyType' => 'আবাসিক ধরন',
            'guardianName' => 'অভিভাবকের নাম',
            'jamaatId' => 'কোন ক্লাসে ভর্তি চান',
            'enrollRollNo' => 'রোল',
        ];
    }

    public function create(): void
    {
        $this->authorize('people.admission.create');

        $this->resetForm();
        $this->jamaatId = $this->filterJamaat;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('people.admission.update');

        $admission = Admission::findOrFail($id);

        // ভর্তি হয়ে যাওয়া আবেদন আর বদলানো যাবে না — ছাত্রের সারি
        // তৈরি হয়ে গেছে, দুটো আলাদা হয়ে যাবে।
        if ($admission->isEnrolled()) {
            session()->flash('error', "{$admission->name} আগেই ভর্তি হয়েছে — আবেদন আর সম্পাদনা করা যাবে না।");

            return;
        }

        $this->editingId = $admission->id;
        $this->name = $admission->name;
        $this->nameAr = (string) $admission->name_ar;
        $this->fatherName = $admission->father_name;
        $this->motherName = (string) $admission->mother_name;
        $this->dateOfBirth = $admission->date_of_birth?->format('Y-m-d') ?? '';
        $this->birthCertificateNo = (string) $admission->birth_certificate_no;
        $this->mobile = (string) $admission->mobile;
        $this->village = (string) $admission->village;
        $this->postOffice = (string) $admission->post_office;
        $this->union = (string) $admission->union;
        $this->upazila = (string) $admission->upazila;
        $this->district = (string) $admission->district;
        $this->previousMadrasa = (string) $admission->previous_madrasa;
        $this->previousJamaat = (string) $admission->previous_jamaat;
        $this->residencyType = $admission->residency_type;
        $this->isOrphan = $admission->is_orphan;
        $this->isPoor = $admission->is_poor;
        $this->remarks = (string) $admission->remarks;
        $this->guardianName = (string) $admission->guardian_name;
        $this->guardianRelation = (string) $admission->guardian_relation;
        $this->guardianMobile = (string) $admission->guardian_mobile;
        $this->jamaatId = (string) $admission->jamaat_id;

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(AdmissionService $admissions, CurrentSession $currentSession): void
    {
        $this->authorize($this->editingId === null
            ? 'people.admission.create'
            : 'people.admission.update');

        $session = $this->resolveSession($currentSession);

        // আবেদন বর্ষের সাথে বাঁধা — বর্ষ ছাড়া রোল ও ফলাফল বসানোর জায়গা নেই।
        if ($session === null) {
            session()->flash('error', 'চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই। আগে একটি শিক্ষাবর্ষ চলতি করুন।');

            return;
        }

        $this->validate();

        $attributes = [
            'name' => $this->name,
            'name_ar' => $this->blankToNull($this->nameAr),
            'father_name' => $this->fatherName,
            'mother_name' => $this->blankToNull($this->motherName),
            'date_of_birth' => $this->blankToNull($this->dateOfBirth),
            'birth_certificate_no' => $this->blankToNull($this->birthCertificateNo),
            'mobile' => $this->blankToNull($this->mobile),
            'village' => $this->blankToNull($this->village),
            'post_office' => $this->blankToNull($this->postOffice),
            'union' => $this->blankToNull($this->union),
            'upazila' => $this->blankToNull($this->upazila),
            'district' => $this->blankToNull($this->district),
            'previous_madrasa' => $this->blankToNull($this->previousMadrasa),
            'previous_jamaat' => $this->blankToNull($this->previousJamaat),
            'residency_type' => $this->residencyType,
            'is_orphan' => $this->isOrphan,
            'is_poor' => $this->isPoor,
            'remarks' => $this->blankToNull($this->remarks),
            'guardian_name' => $this->blankToNull($this->guardianName),
            'guardian_relation' => $this->blankToNull($this->guardianRelation),
            'guardian_mobile' => $this->blankToNull($this->guardianMobile),
            'jamaat_id' => (int) $this->jamaatId,
        ];

        if ($this->editingId === null) {
            $admission = $admissions->apply([
                ...$attributes,
                'academic_session_id' => $session->id,
                'source' => Admission::SOURCE_OFFICE,
            ]);

            session()->flash('status', "{$admission->name} — আবেদন গ্রহণ করা হয়েছে। আবেদন নম্বর: {$admission->application_no}");
        } else {
            $admission = Admission::findOrFail($this->editingId);
            $admission->update($attributes);

            session()->flash('status', "{$admission->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function approve(int $id, AdmissionService $admissions): void
    {
        $this->authorize('people.admission.approve');

        $admission = Admission::findOrFail($id);

        try {
            $admissions->approve($admission);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        // ডিফল্ট ফিল্টার "আবেদিত" — অনুমোদনের পর সারিটা ঐ তালিকা থেকে সরে
        // যায়। ব্যবহারকারীকে "ভর্তি করুন" চাপতে বলে সারিটা লুকিয়ে ফেলা
        // চলে না, তাই ফিল্টারটাও সাথে সরিয়ে দিই।
        $this->followRowTo(Admission::STATUS_APPROVED);

        session()->flash('status', "{$admission->name} — অনুমোদিত। এখন \"ভর্তি করুন\" চাপলে ছাত্র তৈরি হবে।");
    }

    /**
     * সিদ্ধান্তের পর সারিটি যে তালিকায় গেল, ফিল্টারও সেখানে নিয়ে যাওয়া।
     */
    private function followRowTo(string $status): void
    {
        if ($this->filterStatus === Admission::STATUS_APPLIED) {
            $this->filterStatus = $status;
            $this->resetPage();
        }
    }

    public function reject(int $id, AdmissionService $admissions): void
    {
        $this->authorize('people.admission.approve');

        $admission = Admission::findOrFail($id);

        try {
            $admissions->reject($admission);
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());

            return;
        }

        session()->flash('status', "{$admission->name} — আবেদন বাতিল করা হয়েছে।");
    }

    /**
     * ভর্তির ফর্ম খোলা — রোল চাওয়ার জন্য।
     */
    public function startEnroll(int $id): void
    {
        $this->authorize('people.admission.enroll');

        $this->enrollingId = $id;
        $this->enrollRollNo = '';
        $this->resetValidation();
    }

    public function cancelEnroll(): void
    {
        $this->enrollingId = null;
        $this->enrollRollNo = '';
    }

    /**
     * এক ক্লিকে ভর্তি।
     */
    public function enroll(AdmissionService $admissions): void
    {
        $this->authorize('people.admission.enroll');

        if ($this->enrollingId === null) {
            return;
        }

        $this->validate([
            'enrollRollNo' => ['nullable', 'integer', 'min:1'],
        ]);

        $admission = Admission::findOrFail($this->enrollingId);

        try {
            $student = $admissions->enroll(
                $admission,
                $this->enrollRollNo === '' ? null : (int) $this->enrollRollNo,
            );
        } catch (RuntimeException $e) {
            session()->flash('error', $e->getMessage());
            $this->cancelEnroll();

            return;
        }

        // অনুমোদিত তালিকা থেকে সারিটা "ভর্তি সম্পন্ন"-এ চলে গেল।
        if ($this->filterStatus === Admission::STATUS_APPROVED) {
            $this->filterStatus = Admission::STATUS_ENROLLED;
            $this->resetPage();
        }

        session()->flash('status', "{$student->name} — ভর্তি সম্পন্ন। ছাত্র আইডি: {$student->student_uid}");

        $this->cancelEnroll();
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function blankToNull(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    /**
     * চলতি শিক্ষাবর্ষ।
     *
     * Livewire's update endpoint does not run the panel middleware, so the
     * container holder can be empty even when a current session exists.
     */
    private function resolveSession(CurrentSession $currentSession): ?AcademicSession
    {
        return $currentSession->get()
            ?? AcademicSession::query()->current()->first();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->nameAr = '';
        $this->fatherName = '';
        $this->motherName = '';
        $this->dateOfBirth = '';
        $this->birthCertificateNo = '';
        $this->mobile = '';
        $this->village = '';
        $this->postOffice = '';
        $this->union = '';
        $this->upazila = '';
        $this->district = '';
        $this->previousMadrasa = '';
        $this->previousJamaat = '';
        $this->residencyType = Student::RESIDENCY_NON_RESIDENTIAL;
        $this->isOrphan = false;
        $this->isPoor = false;
        $this->remarks = '';
        $this->guardianName = '';
        $this->guardianRelation = '';
        $this->guardianMobile = '';
        $this->jamaatId = '';
        $this->resetValidation();
    }

    /**
     * @return LengthAwarePaginator<int, Admission>
     */
    private function admissions(?AcademicSession $session): LengthAwarePaginator
    {
        return Admission::query()
            ->with(['jamaat.marhala', 'student'])
            ->when($session !== null, fn ($query) => $query->where('academic_session_id', $session->id))
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('application_no', 'like', $term)
                        ->orWhere('father_name', 'like', $term)
                        ->orWhere('mobile', 'like', $term);
                });
            })
            ->when($this->filterStatus !== '', fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->filterJamaat !== '', fn ($query) => $query->where('jamaat_id', (int) $this->filterJamaat))
            ->latest('id')
            ->paginate(20);
    }

    public function render(CurrentSession $currentSession): View
    {
        $this->authorize('people.admission.view');

        $session = $this->resolveSession($currentSession);

        return view('livewire.tenant.people.admission-list', [
            'admissions' => $this->admissions($session),
            'jamaatOptions' => Jamaat::with('marhala')->orderBy('marhala_id')->orderBy('sort_order')->get(),
            'residencyLabels' => StudentList::residencyTypes(),
            'relationLabels' => StudentList::relations(),
            'statusLabels' => self::statuses(),
            'currentSession' => $session,
            'pendingCount' => $session === null ? 0 : Admission::query()
                ->where('academic_session_id', $session->id)
                ->pending()
                ->count(),
        ]);
    }
}
