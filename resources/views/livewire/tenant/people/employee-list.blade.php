@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'তথ্য সম্পাদনা' : 'নতুন শিক্ষক / কর্মচারী' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-6">
                {{-- পরিচয় --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">পরিচয়</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-form.field label="নাম" name="name" :required="true">
                            <input type="text" id="name" wire:model="name" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="আরবি নাম" name="nameAr">
                            <input type="text" id="nameAr" wire:model="nameAr" dir="rtl" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="পিতার নাম" name="fatherName">
                            <input type="text" id="fatherName" wire:model="fatherName" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="জন্মতারিখ" name="dateOfBirth">
                            <input type="date" id="dateOfBirth" wire:model="dateOfBirth" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="জাতীয় পরিচয়পত্র নম্বর" name="nidNo">
                            <input type="text" id="nidNo" wire:model="nidNo" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="মোবাইল" name="mobile">
                            <input type="text" id="mobile" wire:model="mobile" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="ইমেইল" name="email">
                            <input type="email" id="email" wire:model="email" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="ছবি" name="photo"
                                      hint="সর্বোচ্চ ২ মেগাবাইট — JPG বা PNG">
                            <input type="file" id="photo" wire:model="photo" accept="image/*"
                                   class="mt-1 w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-sm file:font-medium file:text-brand-700 hover:file:bg-brand-100">

                            <div wire:loading wire:target="photo" class="mt-1 text-sm text-gray-500">
                                আপলোড হচ্ছে…
                            </div>

                            {{-- নতুন ফাইল থাকলে সেটিই দেখাই, নইলে সংরক্ষিত ছবি। --}}
                            @if ($photo)
                                <img src="{{ $photo->temporaryUrl() }}" alt=""
                                     class="mt-2 h-24 w-24 rounded-lg object-cover">
                            @elseif ($photoPath)
                                <img src="{{ \App\Support\Media::url($photoPath) }}" alt=""
                                     class="mt-2 h-24 w-24 rounded-lg object-cover">
                            @endif
                        </x-form.field>
                    </div>
                </div>

                {{-- চাকরির তথ্য --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">চাকরির তথ্য</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-form.field label="ধরন" name="type" :required="true"
                                      hint="শিক্ষক হলেই শাখা, কিতাব ও নম্বর এন্ট্রিতে আসবেন">
                            <select id="type" wire:model="type" class="{{ $inputClass }}">
                                @foreach ($typeLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="পদবি" name="designation">
                            <select id="designation" wire:model="designation" class="{{ $inputClass }}">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($designationLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="শিক্ষাগত যোগ্যতা" name="qualification">
                            <input type="text" id="qualification" wire:model="qualification"
                                   placeholder="দাওরায়ে হাদিস" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="মাসিক বেতন (টাকা)" name="monthlySalary">
                            <input type="number" id="monthlySalary" wire:model="monthlySalary"
                                   step="0.01" min="0" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="যোগদানের তারিখ" name="joinedOn">
                            <input type="date" id="joinedOn" wire:model="joinedOn" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="অবস্থা" name="status" :required="true">
                            <select id="status" wire:model="status" class="{{ $inputClass }}">
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>
                    </div>
                </div>

                {{-- ঠিকানা --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">ঠিকানা</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                        <x-form.field label="গ্রাম" name="village">
                            <input type="text" id="village" wire:model="village" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="ডাকঘর" name="postOffice">
                            <input type="text" id="postOffice" wire:model="postOffice" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="ইউনিয়ন" name="union">
                            <input type="text" id="union" wire:model="union" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="উপজেলা" name="upazila">
                            <input type="text" id="upazila" wire:model="upazila" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="জেলা" name="district">
                            <input type="text" id="district" wire:model="district" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>
                </div>

                {{-- লগইন অ্যাকাউন্ট --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">লগইন অ্যাকাউন্ট</h3>

                    <label class="mt-3 flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model.live="wantsAccount"
                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        এই ব্যক্তি প্যানেলে লগইন করতে পারবেন
                    </label>

                    @if ($wantsAccount)
                        <div class="mt-3 grid gap-4 sm:grid-cols-3">
                            <x-form.field label="লগইন ইমেইল" name="accountEmail" :required="true">
                                <input type="email" id="accountEmail" wire:model="accountEmail" class="{{ $inputClass }}">
                            </x-form.field>

                            <x-form.field label="পাসওয়ার্ড" name="accountPassword"
                                          :required="! $editingId"
                                          :hint="$editingId ? 'খালি রাখলে পুরনো পাসওয়ার্ড বহাল থাকবে' : 'কমপক্ষে ৮ অক্ষর'">
                                <input type="password" id="accountPassword" wire:model="accountPassword"
                                       autocomplete="new-password" class="{{ $inputClass }}">
                            </x-form.field>

                            <x-form.field label="রোল" name="accountRole" :required="true">
                                <select id="accountRole" wire:model="accountRole" class="{{ $inputClass }}">
                                    <option value="">— বাছাই করুন —</option>
                                    @foreach ($roleOptions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </x-form.field>
                        </div>
                    @elseif ($editingId)
                        <p class="mt-2 text-xs text-gray-500">
                            টিক তুলে নিলে অ্যাকাউন্টটি নিষ্ক্রিয় হবে — মুছে যাবে না, কারণ কার্যবিবরণীতে তার রেকর্ড আছে।
                        </p>
                    @endif
                </div>

                <div>
                    <x-form.field label="মন্তব্য" name="notes">
                        <textarea id="notes" wire:model="notes" rows="2" class="{{ $inputClass }}"></textarea>
                    </x-form.field>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        {{ $editingId ? 'সংরক্ষণ করুন' : 'যুক্ত করুন' }}
                    </button>
                    <button type="button" wire:click="cancel"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                        বাতিল
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div class="flex flex-wrap items-end gap-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700">খোঁজ</label>
                    <input type="text" id="search" wire:model.live.debounce.300ms="search"
                           placeholder="নাম, আইডি, পিতা বা মোবাইল"
                           class="mt-1 w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="filterType" class="block text-sm font-medium text-gray-700">ধরন</label>
                    <select id="filterType" wire:model.live="filterType"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সবাই</option>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filterStatus" class="block text-sm font-medium text-gray-700">অবস্থা</label>
                    <select id="filterStatus" wire:model.live="filterStatus"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সব</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button wire:click="create"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                নতুন শিক্ষক / কর্মচারী
            </button>
        </div>
    @endif

    @if ($employees->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">
                {{ $search !== '' || $filterType !== '' ? 'কেউ পাওয়া যায়নি।' : 'এখনো কোনো শিক্ষক বা কর্মচারী নেই।' }}
            </p>
            @if ($search === '' && $filterType === '')
                <p class="mt-1 text-sm text-gray-600">"নতুন শিক্ষক / কর্মচারী" দিয়ে শুরু করুন।</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">আইডি</th>
                        <th class="px-4 py-3 font-medium">নাম</th>
                        <th class="px-4 py-3 font-medium">পদবি</th>
                        <th class="px-4 py-3 font-medium">ধরন</th>
                        <th class="px-4 py-3 font-medium">মোবাইল</th>
                        <th class="px-4 py-3 font-medium">বেতন</th>
                        <th class="px-4 py-3 font-medium">অবস্থা</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($employees as $employee)
                        <tr wire:key="employee-{{ $employee->id }}">
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ \App\Support\Bn::num($employee->employee_uid) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($employee->photo_path)
                                        <img src="{{ \App\Support\Media::url($employee->photo_path) }}" alt=""
                                             loading="lazy" class="h-9 w-9 shrink-0 rounded-full object-cover">
                                    @else
                                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs text-gray-500">
                                            {{ mb_substr($employee->name, 0, 1) }}
                                        </span>
                                    @endif

                                    <div>
                                        <div class="font-medium text-gray-900">{{ $employee->name }}</div>
                                        @if ($employee->user)
                                            <div class="text-xs text-gray-500">{{ $employee->user->email }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $designationLabels[$employee->designation] ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $typeLabels[$employee->type] ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $employee->mobile ? \App\Support\Bn::num($employee->mobile) : '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ \App\Support\Bn::money($employee->monthly_salary, 0) }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-green-50 text-green-700' => $employee->status === \App\Models\People\Employee::STATUS_ACTIVE,
                                    'bg-amber-50 text-amber-700' => $employee->status === \App\Models\People\Employee::STATUS_ON_LEAVE,
                                    'bg-gray-100 text-gray-600' => ! in_array($employee->status, [\App\Models\People\Employee::STATUS_ACTIVE, \App\Models\People\Employee::STATUS_ON_LEAVE], true),
                                ])>
                                    {{ $statusLabels[$employee->status] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="edit({{ $employee->id }})"
                                        class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                    সম্পাদনা
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $employees->links() }}</div>
    @endif
</div>
