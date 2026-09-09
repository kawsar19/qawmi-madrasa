@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    {{-- সাইট বন্ধ থাকলে সতর্কবার্তা --}}
    @if (! $isPublished)
        <div class="flex items-center justify-between gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span>ওয়েবসাইট এখন <strong>বন্ধ</strong> — দর্শক ৪০৪ দেখবেন।</span>
            <button wire:click="$set('isPublished', true)" class="shrink-0 font-medium underline">চালু করুন</button>
        </div>
    @else
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
            <span class="text-gray-600">ওয়েবসাইট চালু আছে</span>
            <a href="{{ route('public.home') }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-1.5 font-medium text-brand-700 hover:underline">
                সাইট দেখুন
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M18 13v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h6M15 3h6v6M10 14 21 3"/>
                </svg>
            </a>
        </div>
    @endif

    {{-- ট্যাব --}}
    <div class="flex flex-wrap gap-1 border-b border-gray-200">
        @foreach ([
            'appearance' => 'চেহারা',
            'identity' => 'পরিচিতি',
            'contact' => 'যোগাযোগ',
            'sections' => 'সেকশন',
        ] as $key => $label)
            <button wire:click="$set('tab', '{{ $key }}')"
                    @class([
                        '-mb-px border-b-2 px-4 py-2.5 text-sm transition-colors duration-150',
                        'border-brand-600 font-medium text-brand-700' => $tab === $key,
                        'border-transparent text-gray-600 hover:text-gray-900' => $tab !== $key,
                    ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <form wire:submit="save" class="space-y-6">
        {{-- ═══ চেহারা ═══ --}}
        <div @class(['space-y-6', 'hidden' => $tab !== 'appearance'])>
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">টেমপ্লেট</h2>
                <p class="mt-1 text-sm text-gray-600">সাইটের সামগ্রিক নকশা।</p>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($templates as $value => $label)
                        <label @class([
                            'flex cursor-pointer items-start gap-3 rounded-lg border p-4 transition-colors duration-150',
                            'border-brand-500 bg-brand-50' => $template === $value,
                            'border-gray-200 hover:bg-gray-50' => $template !== $value,
                        ])>
                            <input type="radio" wire:model.live="template" value="{{ $value }}"
                                   class="mt-0.5 border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-sm text-pretty text-gray-800">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                @error('template')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">রঙ</h2>
                <p class="mt-1 text-sm text-gray-600">
                    হেডার, বাটন ও অলঙ্কারে এই রঙ ব্যবহৃত হবে।
                </p>

                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <x-form.field label="প্রধান রঙ" name="brandColor" :required="true">
                        <div class="mt-1 flex gap-2">
                            <input type="color" wire:model.live="brandColor"
                                   class="size-10 shrink-0 cursor-pointer rounded-lg border border-gray-300 bg-white p-1">
                            <input type="text" id="brandColor" wire:model.live="brandColor"
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </x-form.field>

                    <x-form.field label="অ্যাকসেন্ট রঙ" name="accentColor" :required="true">
                        <div class="mt-1 flex gap-2">
                            <input type="color" wire:model.live="accentColor"
                                   class="size-10 shrink-0 cursor-pointer rounded-lg border border-gray-300 bg-white p-1">
                            <input type="text" id="accentColor" wire:model.live="accentColor"
                                   class="w-full rounded-lg border-gray-300 font-mono text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </x-form.field>
                </div>

                {{-- সরাসরি প্রিভিউ --}}
                <div class="mt-5 overflow-hidden rounded-lg border border-gray-200">
                    <div class="flex items-center gap-3 px-4 py-3"
                         style="background: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $brandColor) ? $brandColor : '#15803d' }}">
                        <span class="grid size-8 place-items-center rounded-full border-2 text-sm font-bold text-white"
                              style="border-color: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $accentColor) ? $accentColor : '#a16207' }}">
                            {{ mb_substr($siteTitle !== '' ? $siteTitle : (string) tenant('name'), 0, 1) }}
                        </span>
                        <span class="text-sm font-semibold text-white">
                            {{ $siteTitle !== '' ? $siteTitle : tenant('name') }}
                        </span>
                        <span class="ml-auto rounded px-3 py-1.5 text-xs font-medium text-white"
                              style="background: {{ preg_match('/^#[0-9a-fA-F]{6}$/', $accentColor) ? $accentColor : '#a16207' }}">
                            ভর্তি আবেদন
                        </span>
                    </div>
                    <p class="bg-gray-50 px-4 py-2 text-xs text-gray-500">সরাসরি প্রিভিউ</p>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">ছবি</h2>

                <div class="mt-4 grid gap-6 sm:grid-cols-2">
                    <div>
                        <x-form.field label="লোগো" name="logo" hint="বর্গাকার ছবি ভালো দেখায়। সর্বোচ্চ ১ MB।">
                            <input type="file" id="logo" wire:model="logo" accept="image/*"
                                   class="mt-1 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                        </x-form.field>

                        <div wire:loading wire:target="logo" class="mt-2 text-sm text-gray-500">আপলোড হচ্ছে…</div>

                        @if ($logo)
                            <img src="{{ $logo->temporaryUrl() }}" alt=""
                                 class="mt-3 size-20 rounded-lg border border-gray-200 object-cover">
                        @elseif ($settings->logo_path)
                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ asset('storage/'.$settings->logo_path) }}" alt=""
                                     class="size-20 rounded-lg border border-gray-200 object-cover">
                                <button type="button" wire:click="removeLogo"
                                        class="text-sm text-red-600 hover:underline">সরান</button>
                            </div>
                        @endif
                    </div>

                    <div>
                        <x-form.field label="ব্যানার ছবি" name="heroImage" hint="হোমপেজের উপরে পেছনে বসবে। সর্বোচ্চ ৩ MB।">
                            <input type="file" id="heroImage" wire:model="heroImage" accept="image/*"
                                   class="mt-1 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                        </x-form.field>

                        <div wire:loading wire:target="heroImage" class="mt-2 text-sm text-gray-500">আপলোড হচ্ছে…</div>

                        @if ($heroImage)
                            <img src="{{ $heroImage->temporaryUrl() }}" alt=""
                                 class="mt-3 h-20 w-full rounded-lg border border-gray-200 object-cover">
                        @elseif ($settings->hero_image_path)
                            <div class="mt-3 flex items-center gap-3">
                                <img src="{{ asset('storage/'.$settings->hero_image_path) }}" alt=""
                                     class="h-20 w-32 rounded-lg border border-gray-200 object-cover">
                                <button type="button" wire:click="removeHeroImage"
                                        class="text-sm text-red-600 hover:underline">সরান</button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ পরিচিতি ═══ --}}
        <div @class(['rounded-xl border border-gray-200 bg-white p-5', 'hidden' => $tab !== 'identity'])>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.field label="সাইটের নাম" name="siteTitle" hint="খালি রাখলে মাদরাসার নামই দেখাবে">
                    <input type="text" id="siteTitle" wire:model="siteTitle" class="{{ $inputClass }}"
                           placeholder="{{ tenant('name') }}">
                </x-form.field>

                <x-form.field label="আরবি নাম" name="siteTitleAr">
                    <input type="text" id="siteTitleAr" wire:model="siteTitleAr" dir="rtl" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="সংক্ষিপ্ত পরিচয়" name="tagline" hint="হিরোতে নামের নিচে বসবে">
                    <input type="text" id="tagline" wire:model="tagline" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="প্রতিষ্ঠাকাল" name="establishedYear">
                    <input type="text" id="establishedYear" wire:model="establishedYear" class="{{ $inputClass }}"
                           placeholder="1985">
                </x-form.field>

                <div class="sm:col-span-2">
                    <x-form.field label="সংক্ষিপ্ত পরিচিতি" name="aboutShort" hint="হোমপেজে দেখাবে">
                        <textarea id="aboutShort" wire:model="aboutShort" rows="3" class="{{ $inputClass }}"></textarea>
                    </x-form.field>
                </div>

                <div class="sm:col-span-2">
                    <x-form.field label="বিস্তারিত পরিচিতি" name="aboutFull"
                                  hint="পরিচিতি পেজে দেখাবে। অনুচ্ছেদ আলাদা করতে দুই লাইন ফাঁকা দিন।">
                        <textarea id="aboutFull" wire:model="aboutFull" rows="8" class="{{ $inputClass }}"></textarea>
                    </x-form.field>
                </div>

                <div class="sm:col-span-2">
                    <x-form.field label="মুহতামিমের বাণী" name="principalMessage">
                        <textarea id="principalMessage" wire:model="principalMessage" rows="4" class="{{ $inputClass }}"></textarea>
                    </x-form.field>
                </div>

                <x-form.field label="মুহতামিমের নাম" name="principalName">
                    <input type="text" id="principalName" wire:model="principalName" class="{{ $inputClass }}">
                </x-form.field>
            </div>
        </div>

        {{-- ═══ যোগাযোগ ═══ --}}
        <div @class(['rounded-xl border border-gray-200 bg-white p-5', 'hidden' => $tab !== 'contact'])>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-form.field label="ফোন" name="phone">
                    <input type="text" id="phone" wire:model="phone" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="বিকল্প ফোন" name="phoneAlt">
                    <input type="text" id="phoneAlt" wire:model="phoneAlt" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="ইমেইল" name="email">
                    <input type="email" id="email" wire:model="email" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="ফেসবুক লিংক" name="facebookUrl">
                    <input type="url" id="facebookUrl" wire:model="facebookUrl" class="{{ $inputClass }}"
                           placeholder="https://facebook.com/...">
                </x-form.field>

                <x-form.field label="ইউটিউব লিংক" name="youtubeUrl">
                    <input type="url" id="youtubeUrl" wire:model="youtubeUrl" class="{{ $inputClass }}"
                           placeholder="https://youtube.com/...">
                </x-form.field>

                <div class="sm:col-span-2">
                    <x-form.field label="ঠিকানা" name="address">
                        <textarea id="address" wire:model="address" rows="2" class="{{ $inputClass }}"></textarea>
                    </x-form.field>
                </div>
            </div>
        </div>

        {{-- ═══ সেকশন ═══ --}}
        <div @class(['rounded-xl border border-gray-200 bg-white p-5', 'hidden' => $tab !== 'sections'])>
            <h2 class="font-semibold text-gray-900">কোন সেকশন দেখাবে</h2>
            <p class="mt-1 text-sm text-gray-600">
                বন্ধ করলে সেকশনটি হোমপেজ থেকে সরে যাবে, মেনু থেকেও উধাও হবে।
            </p>

            <div class="mt-4 divide-y divide-gray-100">
                @foreach ([
                    ['showNotices', 'নোটিশ', 'নোটিশ তালিকা ও বিস্তারিত পেজ'],
                    ['showTeachers', 'শিক্ষকমণ্ডলী', 'শিক্ষকদের তালিকা'],
                    ['showGallery', 'গ্যালারি', 'ছবির অ্যালবাম (শীঘ্রই আসছে)'],
                    ['showAdmissionForm', 'ভর্তি আবেদন', 'হেডারে "ভর্তি আবেদন" বাটন'],
                ] as [$field, $label, $hint])
                    <label class="flex cursor-pointer items-start gap-3 py-3">
                        <input type="checkbox" wire:model.live="{{ $field }}"
                               class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-900">{{ $label }}</span>
                            <span class="block text-sm text-pretty text-gray-500">{{ $hint }}</span>
                        </span>
                    </label>
                @endforeach
            </div>

            <div class="mt-5 border-t border-gray-200 pt-5">
                <label class="flex cursor-pointer items-start gap-3">
                    <input type="checkbox" wire:model.live="isPublished"
                           class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    <span class="min-w-0">
                        <span class="block text-sm font-medium text-gray-900">ওয়েবসাইট চালু</span>
                        <span class="block text-sm text-pretty text-gray-500">
                            বন্ধ করলে দর্শক ৪০৪ দেখবেন — সাইট গোছানোর সময় কাজে লাগে।
                        </span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                সংরক্ষণ করুন
            </button>
            <span wire:loading wire:target="save" class="self-center text-sm text-gray-500">সংরক্ষণ হচ্ছে…</span>
        </div>
    </form>
</div>
