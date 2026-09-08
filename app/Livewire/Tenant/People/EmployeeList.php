<?php

declare(strict_types=1);

namespace App\Livewire\Tenant\People;

use App\Models\People\Employee;
use App\Services\People\EmployeeRegistrar;
use App\Support\PermissionRegistry;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * শিক্ষক ও কর্মচারী ব্যবস্থাপনা।
 *
 * A teacher who enters marks needs a login; a cook does not. The account
 * fields are therefore optional and only shown when the user asks for one.
 */
class EmployeeList extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterStatus = Employee::STATUS_ACTIVE;

    public bool $showForm = false;

    public ?int $editingId = null;

    // ---- পরিচয় ----
    public string $name = '';

    public string $nameAr = '';

    public string $fatherName = '';

    public string $dateOfBirth = '';

    public string $nidNo = '';

    public string $mobile = '';

    public string $email = '';

    // ---- চাকরি ----
    public string $type = Employee::TYPE_TEACHER;

    public string $designation = '';

    public string $qualification = '';

    public string $monthlySalary = '';

    public string $joinedOn = '';

    public string $status = Employee::STATUS_ACTIVE;

    // ---- ঠিকানা ----
    public string $village = '';

    public string $postOffice = '';

    public string $union = '';

    public string $upazila = '';

    public string $district = '';

    public string $notes = '';

    // ---- লগইন অ্যাকাউন্ট (ঐচ্ছিক) ----
    public bool $wantsAccount = false;

    public string $accountEmail = '';

    public string $accountPassword = '';

    public string $accountRole = '';

    /**
     * @return array<string, string>
     */
    public static function types(): array
    {
        return [
            Employee::TYPE_TEACHER => 'শিক্ষক',
            Employee::TYPE_STAFF => 'কর্মচারী',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            Employee::STATUS_ACTIVE => 'কর্মরত',
            Employee::STATUS_ON_LEAVE => 'ছুটিতে',
            Employee::STATUS_RETIRED => 'অবসরপ্রাপ্ত',
            Employee::STATUS_TERMINATED => 'চাকরিচ্যুত',
        ];
    }

    /**
     * পদবি — মাদরাসার প্রচলিত পদগুলো।
     *
     * @return array<string, string>
     */
    public static function designations(): array
    {
        return [
            'muhtamim' => 'মুহতামিম',
            'nayeb_muhtamim' => 'নায়েবে মুহতামিম',
            'shikkha_sochib' => 'শিক্ষা সচিব',
            'nazeme_talimat' => 'নাযিমে তালিমাত',
            'nazeme_darul_iqama' => 'নাযেমে দারুল ইকামা',
            'ustad' => 'উস্তাদ',
            'qari' => 'কারী',
            'hafez' => 'হাফেজ',
            'muhasib' => 'হিসাবরক্ষক',
            'kerani' => 'কেরানি',
            'baburchi' => 'বাবুর্চি',
            'daroan' => 'দারোয়ান',
            'other' => 'অন্যান্য',
        ];
    }

    /**
     * লগইন অ্যাকাউন্টে দেওয়া যাবে এমন রোল।
     *
     * madrasa_admin is left out on purpose: handing out full admin from the
     * staff screen is a system.user job, not a hiring one.
     *
     * @return array<string, string>
     */
    public static function assignableRoles(): array
    {
        $roles = [];

        foreach (PermissionRegistry::roles() as $key => $role) {
            if ($key === 'madrasa_admin') {
                continue;
            }

            $roles[$key] = $role['name'];
        }

        return $roles;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
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
            'fatherName' => ['nullable', 'string', 'max:255'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'nidNo' => ['nullable', 'string', 'max:50'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],

            'type' => ['required', Rule::in(array_keys(self::types()))],
            'designation' => ['nullable', Rule::in(array_keys(self::designations()))],
            'qualification' => ['nullable', 'string', 'max:255'],
            'monthlySalary' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'joinedOn' => ['nullable', 'date'],
            'status' => ['required', Rule::in(array_keys(self::statuses()))],

            'village' => ['nullable', 'string', 'max:255'],
            'postOffice' => ['nullable', 'string', 'max:255'],
            'union' => ['nullable', 'string', 'max:255'],
            'upazila' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],

            // ইমেইল মাদরাসার ভেতরে অনন্য — global scope users-এ নেই,
            // তাই tenant_id নিজে হাতে যোগ করতে হয়।
            'accountEmail' => [
                $this->wantsAccount ? 'required' : 'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->where('tenant_id', tenant('id'))
                    ->ignore($this->existingUserId()),
            ],
            'accountPassword' => [
                $this->wantsAccount && $this->existingUserId() === null ? 'required' : 'nullable',
                'string',
                'min:8',
            ],
            'accountRole' => [
                $this->wantsAccount ? 'required' : 'nullable',
                Rule::in(array_keys(self::assignableRoles())),
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
            'dateOfBirth' => 'জন্মতারিখ',
            'nidNo' => 'জাতীয় পরিচয়পত্র নম্বর',
            'mobile' => 'মোবাইল',
            'email' => 'ইমেইল',
            'type' => 'ধরন',
            'designation' => 'পদবি',
            'qualification' => 'শিক্ষাগত যোগ্যতা',
            'monthlySalary' => 'মাসিক বেতন',
            'joinedOn' => 'যোগদানের তারিখ',
            'status' => 'অবস্থা',
            'village' => 'গ্রাম',
            'postOffice' => 'ডাকঘর',
            'union' => 'ইউনিয়ন',
            'upazila' => 'উপজেলা',
            'district' => 'জেলা',
            'accountEmail' => 'লগইন ইমেইল',
            'accountPassword' => 'পাসওয়ার্ড',
            'accountRole' => 'রোল',
        ];
    }

    public function create(): void
    {
        $this->authorize('people.employee.create');

        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->authorize('people.employee.update');

        $employee = Employee::with('user')->findOrFail($id);

        $this->editingId = $employee->id;
        $this->name = $employee->name;
        $this->nameAr = (string) $employee->name_ar;
        $this->fatherName = (string) $employee->father_name;
        $this->dateOfBirth = $employee->date_of_birth?->format('Y-m-d') ?? '';
        $this->nidNo = (string) $employee->nid_no;
        $this->mobile = (string) $employee->mobile;
        $this->email = (string) $employee->email;
        $this->type = $employee->type;
        $this->designation = (string) $employee->designation;
        $this->qualification = (string) $employee->qualification;
        $this->monthlySalary = (string) $employee->monthly_salary;
        $this->joinedOn = $employee->joined_on?->format('Y-m-d') ?? '';
        $this->status = $employee->status;
        $this->village = (string) $employee->village;
        $this->postOffice = (string) $employee->post_office;
        $this->union = (string) $employee->union;
        $this->upazila = (string) $employee->upazila;
        $this->district = (string) $employee->district;
        $this->notes = (string) $employee->notes;

        $user = $employee->user;
        $this->wantsAccount = $user !== null;
        $this->accountEmail = (string) $user?->email;
        $this->accountPassword = '';
        // getRoleNames() returns role names as strings; reading ->name off
        // the pivot models loses the type through spatie's untyped relation.
        $this->accountRole = (string) $user?->getRoleNames()->first();

        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(EmployeeRegistrar $registrar): void
    {
        $this->authorize($this->editingId === null
            ? 'people.employee.create'
            : 'people.employee.update');

        $this->validate();

        $attributes = [
            'name' => $this->name,
            'name_ar' => $this->blankToNull($this->nameAr),
            'father_name' => $this->blankToNull($this->fatherName),
            'date_of_birth' => $this->blankToNull($this->dateOfBirth),
            'nid_no' => $this->blankToNull($this->nidNo),
            'mobile' => $this->blankToNull($this->mobile),
            'email' => $this->blankToNull($this->email),
            'type' => $this->type,
            'designation' => $this->blankToNull($this->designation),
            'qualification' => $this->blankToNull($this->qualification),
            'monthly_salary' => $this->monthlySalary === '' ? 0 : $this->monthlySalary,
            'joined_on' => $this->blankToNull($this->joinedOn),
            'status' => $this->status,
            'village' => $this->blankToNull($this->village),
            'post_office' => $this->blankToNull($this->postOffice),
            'union' => $this->blankToNull($this->union),
            'upazila' => $this->blankToNull($this->upazila),
            'district' => $this->blankToNull($this->district),
            'notes' => $this->blankToNull($this->notes),
        ];

        $account = $this->wantsAccount ? array_filter([
            'email' => $this->accountEmail,
            'password' => $this->accountPassword,
            'role' => $this->accountRole,
        ], static fn (string $value): bool => $value !== '') : null;

        if ($this->editingId === null) {
            $attributes['joined_on'] ??= now()->toDateString();

            $employee = $registrar->register($attributes, $account);

            session()->flash('status', "{$employee->name} — যুক্ত করা হয়েছে। আইডি: {$employee->employee_uid}");
        } else {
            $employee = Employee::findOrFail($this->editingId);
            $employee->update($attributes);

            if ($account !== null) {
                // A blank password here means "unchanged" — the registrar
                // leaves the stored hash alone.
                $registrar->attachAccount($employee, $account);
            } elseif ($employee->user !== null) {
                $registrar->detachAccount($employee);
            }

            session()->flash('status', "{$employee->name} — সংরক্ষণ করা হয়েছে।");
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    /**
     * সম্পাদনার সময় নিজের ইউজার আইডি, যাতে unique নিয়ম নিজেকেই আটকে না দেয়।
     */
    private function existingUserId(): ?int
    {
        if ($this->editingId === null) {
            return null;
        }

        return Employee::query()->whereKey($this->editingId)->value('user_id');
    }

    private function blankToNull(string $value): ?string
    {
        return $value !== '' ? $value : null;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->nameAr = '';
        $this->fatherName = '';
        $this->dateOfBirth = '';
        $this->nidNo = '';
        $this->mobile = '';
        $this->email = '';
        $this->type = Employee::TYPE_TEACHER;
        $this->designation = '';
        $this->qualification = '';
        $this->monthlySalary = '';
        $this->joinedOn = '';
        $this->status = Employee::STATUS_ACTIVE;
        $this->village = '';
        $this->postOffice = '';
        $this->union = '';
        $this->upazila = '';
        $this->district = '';
        $this->notes = '';
        $this->wantsAccount = false;
        $this->accountEmail = '';
        $this->accountPassword = '';
        $this->accountRole = '';
        $this->resetValidation();
    }

    /**
     * @return LengthAwarePaginator<int, Employee>
     */
    private function employees(): LengthAwarePaginator
    {
        return Employee::query()
            ->with('user')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->where('name', 'like', $term)
                        ->orWhere('employee_uid', 'like', $term)
                        ->orWhere('father_name', 'like', $term)
                        ->orWhere('mobile', 'like', $term);
                });
            })
            ->when($this->filterType !== '', fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterStatus !== '', fn ($query) => $query->where('status', $this->filterStatus))
            ->orderBy('name')
            ->paginate(20);
    }

    public function render(): View
    {
        $this->authorize('people.employee.view');

        return view('livewire.tenant.people.employee-list', [
            'employees' => $this->employees(),
            'typeLabels' => self::types(),
            'statusLabels' => self::statuses(),
            'designationLabels' => self::designations(),
            'roleOptions' => self::assignableRoles(),
        ]);
    }
}
