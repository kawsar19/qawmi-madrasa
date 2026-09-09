@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if (! $currentSession)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই — আবেদন নেওয়ার আগে
            <a href="{{ route('tenant.academic.sessions') }}" class="font-medium underline">একটি বর্ষ চলতি করুন</a>।
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'আবেদন সম্পাদনা' : 'নতুন ভর্তি আবেদন' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-6">
                {{-- আবেদনকারীর তথ্য --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">আবেদনকারীর তথ্য</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-form.field label="নাম" name="name" :required="true">
                            <input type="text" id="name" wire:model="name" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="আরবি নাম" name="nameAr">
                            <input type="text" id="nameAr" wire:model="nameAr" dir="rtl" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="পিতার নাম" name="fatherName" :required="true">
                            <input type="text" id="fatherName" wire:model="fatherName" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="মাতার নাম" name="motherName">
                            <input type="text" id="motherName" wire:model="motherName" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="জন্মতারিখ" name="dateOfBirth">
                            <input type="date" id="dateOfBirth" wire:model="dateOfBirth" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="জন্মনিবন্ধন নম্বর" name="birthCertificateNo">
                            <input type="text" id="birthCertificateNo" wire:model="birthCertificateNo" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="মোবাইল" name="mobile">
                            <input type="text" id="mobile" wire:model="mobile" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>
                </div>

                {{-- ঠিকানা --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">স্থায়ী ঠিকানা</h3>
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

                {{-- অভিভাবক --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">অভিভাবক</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-form.field label="নাম" name="guardianName">
                            <input type="text" id="guardianName" wire:model="guardianName" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="সম্পর্ক" name="guardianRelation">
                            <select id="guardianRelation" wire:model="guardianRelation" class="{{ $inputClass }}">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($relationLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="মোবাইল" name="guardianMobile">
                            <input type="text" id="guardianMobile" wire:model="guardianMobile" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>
                </div>

                {{-- ভর্তির তথ্য --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">ভর্তির তথ্য</h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        <x-form.field label="কোন ক্লাসে ভর্তি চান" name="jamaatId" :required="true">
                            <select id="jamaatId" wire:model="jamaatId" class="{{ $inputClass }}">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($jamaatOptions as $option)
                                    <option value="{{ $option->id }}">
                                        {{ $option->marhala?->name }} — {{ $option->name }}
                                    </option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="পূর্ববর্তী মাদরাসা" name="previousMadrasa">
                            <input type="text" id="previousMadrasa" wire:model="previousMadrasa" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="পূর্ববর্তী জামাত" name="previousJamaat">
                            <input type="text" id="previousJamaat" wire:model="previousJamaat" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="আবাসিক ধরন" name="residencyType" :required="true">
                            <select id="residencyType" wire:model="residencyType" class="{{ $inputClass }}">
                                @foreach ($residencyLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <div class="flex items-end gap-5 pb-1">
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="isOrphan"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                এতিম
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="isPoor"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                গরিব
                            </label>
                        </div>

                        <div class="sm:col-span-3">
                            <x-form.field label="মন্তব্য" name="remarks">
                                <textarea id="remarks" wire:model="remarks" rows="2" class="{{ $inputClass }}"></textarea>
                            </x-form.field>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                        {{ $editingId ? 'সংরক্ষণ করুন' : 'আবেদন গ্রহণ করুন' }}
                    </button>
                    <button type="button" wire:click="cancel"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-50">
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
                           placeholder="নাম, আবেদন নম্বর, পিতা বা মোবাইল"
                           class="mt-1 w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
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

                <div>
                    <label for="filterJamaat" class="block text-sm font-medium text-gray-700">ক্লাস</label>
                    <select id="filterJamaat" wire:model.live="filterJamaat"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সব ক্লাস</option>
                        @foreach ($jamaatOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->marhala?->name }} — {{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button wire:click="create"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                নতুন আবেদন
            </button>
        </div>

        @if ($pendingCount > 0 && $filterStatus !== \App\Models\People\Admission::STATUS_APPLIED)
            <div class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
                {{ \App\Support\Bn::num($pendingCount) }}টি আবেদনের সিদ্ধান্ত বাকি আছে।
                <button wire:click="$set('filterStatus', '{{ \App\Models\People\Admission::STATUS_APPLIED }}')"
                        class="font-medium underline">দেখুন</button>
            </div>
        @endif
    @endif

    @if ($admissions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">
                {{ $search !== '' || $filterJamaat !== '' ? 'কোনো আবেদন পাওয়া যায়নি।' : 'এখনো কোনো আবেদন নেই।' }}
            </p>
            @if ($search === '' && $filterJamaat === '')
                <p class="mt-1 text-sm text-gray-600">"নতুন আবেদন" দিয়ে শুরু করুন।</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">আবেদন নং</th>
                        <th class="px-4 py-3 font-medium">নাম</th>
                        <th class="px-4 py-3 font-medium">পিতা</th>
                        <th class="px-4 py-3 font-medium">ক্লাস</th>
                        <th class="px-4 py-3 font-medium">মোবাইল</th>
                        <th class="px-4 py-3 font-medium">অবস্থা</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($admissions as $admission)
                        <tr wire:key="admission-{{ $admission->id }}">
                            <td class="px-4 py-3 font-mono text-xs tabular-nums text-gray-600">
                                {{ \App\Support\Bn::num($admission->application_no) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $admission->name }}</div>
                                @if ($admission->student)
                                    <div class="text-xs text-gray-500">
                                        ছাত্র আইডি: {{ \App\Support\Bn::num($admission->student->student_uid) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $admission->father_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $admission->jamaat?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $admission->mobile ? \App\Support\Bn::num($admission->mobile) : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-amber-50 text-amber-700' => $admission->status === \App\Models\People\Admission::STATUS_APPLIED,
                                    'bg-blue-50 text-blue-700' => $admission->status === \App\Models\People\Admission::STATUS_APPROVED,
                                    'bg-green-50 text-green-700' => $admission->status === \App\Models\People\Admission::STATUS_ENROLLED,
                                    'bg-gray-100 text-gray-600' => $admission->status === \App\Models\People\Admission::STATUS_REJECTED,
                                ])>
                                    {{ $statusLabels[$admission->status] ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                {{-- ভর্তির রোল চাওয়ার ইনলাইন ফর্ম --}}
                                @if ($enrollingId === $admission->id)
                                    <div class="flex items-center justify-end gap-2">
                                        <input type="number" wire:model="enrollRollNo" min="1"
                                               placeholder="রোল (খালি = ক্রমিক)"
                                               class="w-40 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                        <button wire:click="enroll"
                                                class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                                            নিশ্চিত
                                        </button>
                                        <button wire:click="cancelEnroll"
                                                class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                                            বাতিল
                                        </button>
                                    </div>
                                @else
                                    <div class="flex items-center justify-end gap-1">
                                        @if ($admission->isPending())
                                            <button wire:click="approve({{ $admission->id }})"
                                                    class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                                                অনুমোদন
                                            </button>
                                            <button wire:click="reject({{ $admission->id }})"
                                                    wire:confirm="এই আবেদন বাতিল করবেন?"
                                                    class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-50">
                                                বাতিল
                                            </button>
                                            <button wire:click="edit({{ $admission->id }})"
                                                    class="rounded px-2 py-1.5 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100">
                                                সম্পাদনা
                                            </button>
                                        @elseif ($admission->isEnrollable())
                                            <button wire:click="startEnroll({{ $admission->id }})"
                                                    class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                                                ভর্তি করুন
                                            </button>
                                            <button wire:click="edit({{ $admission->id }})"
                                                    class="rounded px-2 py-1.5 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100">
                                                সম্পাদনা
                                            </button>
                                        @elseif ($admission->isEnrolled())
                                            <a href="{{ route('tenant.people.students') }}"
                                               class="rounded px-2 py-1.5 text-sm text-gray-700 transition-colors duration-150 hover:bg-gray-100">
                                                ছাত্র তালিকায় দেখুন
                                            </a>
                                        @else
                                            <span class="px-2 py-1.5 text-sm text-gray-400">—</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $admissions->links() }}</div>
    @endif
</div>
