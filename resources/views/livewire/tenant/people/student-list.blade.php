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
            চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই — ভর্তি করার আগে
            <a href="{{ route('tenant.academic.sessions') }}" class="font-medium underline">একটি বর্ষ চলতি করুন</a>।
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'ছাত্রের তথ্য সম্পাদনা' : 'নতুন ছাত্র ভর্তি' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-6">
                {{-- ছাত্রের তথ্য --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">ছাত্রের তথ্য</h3>
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
                        <x-form.field label="নাম" name="guardianName" hint="একই মোবাইল নম্বর থাকলে ভাইবোনের অভিভাবক নতুন করে তৈরি হবে না">
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

                {{-- ভর্তি ও অন্যান্য --}}
                <div>
                    <h3 class="text-sm font-medium text-gray-500">
                        {{ $editingId ? 'অন্যান্য' : 'ভর্তির তথ্য' }}
                    </h3>
                    <div class="mt-3 grid gap-4 sm:grid-cols-3">
                        @unless ($editingId)
                            <x-form.field label="ক্লাস" name="jamaatId" :required="true">
                                <select id="jamaatId" wire:model="jamaatId" class="{{ $inputClass }}">
                                    <option value="">— বাছাই করুন —</option>
                                    @foreach ($jamaatOptions as $option)
                                        <option value="{{ $option->id }}">
                                            {{ $option->marhala?->name }} — {{ $option->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </x-form.field>

                            <x-form.field label="রোল" name="rollNo" hint="খালি রাখলে ক্রমিক নম্বর বসবে">
                                <input type="number" id="rollNo" wire:model="rollNo" min="1" class="{{ $inputClass }}">
                            </x-form.field>
                        @endunless

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
                            <x-form.field label="মন্তব্য" name="notes">
                                <textarea id="notes" wire:model="notes" rows="2" class="{{ $inputClass }}"></textarea>
                            </x-form.field>
                        </div>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        {{ $editingId ? 'সংরক্ষণ করুন' : 'ভর্তি করুন' }}
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
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                নতুন ছাত্র
            </button>
        </div>
    @endif

    @if ($students->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">
                {{ $search !== '' || $filterJamaat !== '' ? 'কোনো ছাত্র পাওয়া যায়নি।' : 'এখনো কোনো ছাত্র নেই।' }}
            </p>
            @if ($search === '' && $filterJamaat === '')
                <p class="mt-1 text-sm text-gray-600">"নতুন ছাত্র" দিয়ে ভর্তি শুরু করুন।</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">আইডি</th>
                        <th class="px-4 py-3 font-medium">নাম</th>
                        <th class="px-4 py-3 font-medium">পিতা</th>
                        <th class="px-4 py-3 font-medium">ক্লাস</th>
                        <th class="px-4 py-3 font-medium">রোল</th>
                        <th class="px-4 py-3 font-medium">ধরন</th>
                        <th class="px-4 py-3 font-medium">মোবাইল</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($students as $student)
                        @php($enrollment = $student->enrollments->first())
                        <tr wire:key="student-{{ $student->id }}">
                            <td class="px-4 py-3 font-mono text-xs text-gray-600">{{ \App\Support\Bn::num($student->student_uid) }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $student->name }}</div>
                                <div class="flex gap-1">
                                    @if ($student->is_orphan)
                                        <span class="text-xs text-amber-700">এতিম</span>
                                    @endif
                                    @if ($student->is_poor)
                                        <span class="text-xs text-amber-700">গরিব</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $student->father_name }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $enrollment?->jamaat?->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $enrollment?->roll_no ? \App\Support\Bn::num($enrollment->roll_no) : '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $residencyLabels[$student->residency_type] ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $student->mobile ? \App\Support\Bn::num($student->mobile) : '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="edit({{ $student->id }})"
                                        class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                    সম্পাদনা
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $students->links() }}</div>
    @endif
</div>
