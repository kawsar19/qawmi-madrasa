<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\People;

use App\Models\Academic\AcademicSession;
use App\Models\Academic\Jamaat;
use App\Models\People\Enrollment;
use App\Models\People\Student;
use App\Services\Academic\CurrentSession;
use App\Services\People\StudentRegistrar;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * ছাত্র ব্যবস্থাপনা — তালিকা, খোঁজ ও ভর্তি।
 *
 * Existing madrasas start with hundreds of students already enrolled, so this
 * screen enters them directly; the application → exam → approval flow lands in
 * a later block and reuses StudentRegistrar.
 */
class StudentList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $filterJamaat = '';

    public bool $showForm = false;

    public ?int $editingId = null;

    // ---- ছাত্র ----
    public string $name = '';

    public string $nameAr = '';

    public string $fatherName = '';

    public string $motherName = '';

    public string $dateOfBirth = '';

    public string $birthCertificateNo = '';

    public string $mobile = '';

    // ---- স্থায়ী ঠিকানা ----
    public string $village = '';

    public string $postOffice = '';

    public string $union = '';

    public string $upazila = '';

    public string $district = '';

    // ---- অন্যান্য ----
    public string $residencyType = Student::RESIDENCY_NON_RESIDENTIAL;

    public bool $isOrphan = false;

    public bool $isPoor = false;

    public string $notes = '';

    // ---- অভিভাবক ----
    public string $guardianName = '';

    public string $guardianRelation = '';

    public string $guardianMobile = '';

    // ---- এনরোলমেন্ট ----
    public string $jamaatId = '';

    public string $rollNo = '';

    /**
     * @return array<string, string>
     */
    public static function residencyTypes(): array
    {
        return [
            Student::RESIDENCY_RESIDENTIAL => 'আবাসিক',
            Student::RESIDENCY_NON_RESIDENTIAL => 'অনাবাসিক',
            Student::RESIDENCY_DAY_CARE => 'ডে-কেয়ার',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function relations(): array
    {
        return [
            'father' => 'পিতা',
            'mother' => 'মাতা',
            'uncle' => 'চাচা',
            'brother' => 'ভাই',
            'other' => 'অন্যান্য',
        ];
    }

    public function updatedSearch(): void
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
            'residencyType' => ['required', Rule::in(array_keys(self::residencyTypes()))],
            'notes' => ['nullable', 'string', 'max:1000'],

            'guardianName' => ['nullable', 'string', 'max:255'],
            'guardianRelation' => ['nullable', Rule::in(array_keys(self::relations()))],
            'guardianMobile' => ['nullable', 'string', 'max:20'],

            // ক্লাস শুধু নতুন ভর্তির সময়; সম্পাদনায় ক্লাস বদলানো আলাদা কাজ।
            'jamaatId' => [
                $this->editingId === null ? 'required' : 'nullable',
                Rule::exists('jamaats', 'id')->where('tenant_id', tenant('id')),
            ],
            'rollNo' => ['nullable', 'integer', 'min:1'],
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
            'residencyType' => 'আবাসিক ধরন',
            'guardianName' => 'অভিভাবকের নাম',
            'jamaatId' => 'ক্লাস',
            'rollNo' => 'রোল',
        ];
    }

    public function create(): void
    {
        $this->authorize('people.student.create');

        $this->resetForm();
        $this->jamaatId = $this->filterJamaat;
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('people.student.update');

        $student = Student::findOrFail($id);

        $this->editingId = $student->id;
        $this->name = $student->name;
        $this->nameAr = (string) $student->name_ar;
        $this->fatherName = $student->father_name;
        $this->motherName = (string) $student->mother_name;
        $this->dateOfBirth = $student->date_of_birth?->format('Y-m-d') ?? '';
        $this->birthCertificateNo = (string) $student->birth_certificate_no;
        $this->mobile = (string) $student->mobile;
        $this->village = (string) $student->village;
        $this->postOffice = (string) $student->post_office;
        $this->union = (string) $student->union;
        $this->upazila = (string) $student->upazila;
        $this->district = (string) $student->district;
        $this->residencyType = $student->residency_type;
        $this->isOrphan = $student->is_orphan;
        $this->isPoor = $student->is_poor;
        $this->notes = (string) $student->notes;

        $guardian = $student->guardians()->first();
        $this->guardianName = (string) $guardian?->name;
        $this->guardianRelation = (string) $guardian?->relation;
        $this->guardianMobile = (string) $guardian?->mobile;

        $this->jamaatId = '';
        $this->rollNo = '';

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(StudentRegistrar $registrar, CurrentSession $currentSession): void
    {
        $this->authorize($this->editingId === null
            ? 'people.student.create'
            : 'people.student.update');

        $session = $this->resolveSession($currentSession);

        // ভর্তি করতে হলে চলতি বর্ষ লাগবেই — রোল ও ফলাফল বর্ষের সাথে বাঁধা।
        if ($this->editingId === null && $session === null) {
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
            'residency_type' => $this->residencyType,
            'is_orphan' => $this->isOrphan,
            'is_poor' => $this->isPoor,
            'notes' => $this->blankToNull($this->notes),
        ];

        $guardian = $this->guardianName !== '' ? [
            'name' => $this->guardianName,
            'relation' => $this->blankToNull($this->guardianRelation),
            'mobile' => $this->blankToNull($this->guardianMobile),
        ] : null;

        if ($this->editingId === null) {
            $attributes['admitted_on'] = now()->toDateString();

            $student = $registrar->register($attributes, $guardian, [
                'academic_session_id' => $session->id,
                'jamaat_id' => (int) $this->jamaatId,
                'roll_no' => $this->blankToNull($this->rollNo),
            ]);

            session()->flash('status', "{$student->name} — ভর্তি সম্পন্ন। আইডি: {$student->student_uid}");
        } else {
            $student = Student::findOrFail($this->editingId);
            $student->update($attributes);

            if ($guardian !== null) {
                $registrar->attachGuardian($student, $guardian);
            }

            session()->flash('status', "{$student->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
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
     * SetCurrentAcademicSession fills the container holder on a normal
     * request, but Livewire's own update endpoint does not run the panel
     * middleware group, so fall back to reading it directly rather than
     * silently treating "no session" as a validation failure.
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
        $this->residencyType = Student::RESIDENCY_NON_RESIDENTIAL;
        $this->isOrphan = false;
        $this->isPoor = false;
        $this->notes = '';
        $this->guardianName = '';
        $this->guardianRelation = '';
        $this->guardianMobile = '';
        $this->jamaatId = '';
        $this->rollNo = '';
        $this->resetValidation();
    }

    /**
     * @return LengthAwarePaginator<int, Student>
     */
    private function students(?AcademicSession $session): LengthAwarePaginator
    {
        $sessionId = $session?->id;

        return Student::query()
            ->with(['enrollments' => function ($query) use ($sessionId): void {
                $query->where('academic_session_id', $sessionId)->with(['jamaat', 'section']);
            }])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('student_uid', 'like', $term)
                        ->orWhere('father_name', 'like', $term)
                        ->orWhere('mobile', 'like', $term);
                });
            })
            ->when($this->filterJamaat !== '', function ($query) use ($sessionId): void {
                $query->whereHas('enrollments', function ($inner) use ($sessionId): void {
                    $inner->where('academic_session_id', $sessionId)
                        ->where('jamaat_id', (int) $this->filterJamaat);
                });
            })
            ->orderBy('name')
            ->paginate(20);
    }

    public function render(CurrentSession $currentSession): View
    {
        $this->authorize('people.student.view');

        $session = $this->resolveSession($currentSession);

        return view('livewire.tenant.people.student-list', [
            'students' => $this->students($session),
            'jamaatOptions' => Jamaat::with('marhala')->orderBy('marhala_id')->orderBy('sort_order')->get(),
            'residencyLabels' => self::residencyTypes(),
            'relationLabels' => self::relations(),
            'currentSession' => $session,
            'statusLabels' => [
                Enrollment::STATUS_STUDYING => 'চলমান',
                Enrollment::STATUS_PASSED => 'উত্তীর্ণ',
                Enrollment::STATUS_FAILED => 'অকৃতকার্য',
                Enrollment::STATUS_LEFT => 'ছেড়ে গেছে',
            ],
        ]);
    }
}
