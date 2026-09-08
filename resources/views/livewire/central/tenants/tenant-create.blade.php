<form wire:submit="save" class="max-w-3xl space-y-6">

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-gray-900">মাদরাসার তথ্য</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field label="মাদরাসার নাম" name="name" required>
                <input wire:model.live.debounce.400ms="name" id="name" type="text"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="Slug" name="slug" required hint="ইংরেজি অক্ষর, সংখ্যা ও হাইফেন">
                <input wire:model.live.debounce.400ms="slug" id="slug" type="text" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="ডোমেইন" name="domain" required hint="সাবডোমেইন অথবা নিজস্ব ডোমেইন">
                <input wire:model="domain" id="domain" type="text" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="EIIN" name="eiin">
                <input wire:model="eiin" id="eiin" type="text" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="ধরন" name="madrasaType" required>
                <select wire:model="madrasaType" id="madrasaType"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="kitab">কিতাব বিভাগ</option>
                    <option value="hifz">হিফজ বিভাগ</option>
                    <option value="mixed">উভয়</option>
                </select>
            </x-form.field>

            <x-form.field label="সিড ডেটা" name="preset" required
                          hint="হিফজ প্রিসেটে শুধু হিফজ/নাযেরা/কিরাআত মারহালা যুক্ত হবে">
                <select wire:model="preset" id="preset"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="kitab_madrasa">পূর্ণ কিতাব কারিকুলাম</option>
                    <option value="hifz_madrasa">শুধু হিফজ</option>
                </select>
            </x-form.field>

            <div class="sm:col-span-2">
                <x-form.field label="ঠিকানা" name="address">
                    <textarea wire:model="address" id="address" rows="2"
                              class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"></textarea>
                </x-form.field>
            </div>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-gray-900">মাদরাসা অ্যাডমিন</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field label="নাম" name="adminName" required>
                <input wire:model="adminName" id="adminName" type="text"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="ইমেইল" name="adminEmail" required>
                <input wire:model="adminEmail" id="adminEmail" type="email" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="পাসওয়ার্ড" name="adminPassword" required hint="কমপক্ষে ৮ অক্ষর">
                <input wire:model="adminPassword" id="adminPassword" type="text" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>

            <x-form.field label="মোবাইল" name="adminMobile" hint="০১৭xxxxxxxx">
                <input wire:model="adminMobile" id="adminMobile" type="text" dir="ltr"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>
        </div>
    </section>

    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-gray-900">সাবস্ক্রিপশন</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field label="প্ল্যান" name="planId">
                <select wire:model="planId" id="planId"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="">সাবস্ক্রিপশন ছাড়া</option>
                    @foreach ($plans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->name }} — {{ \App\Support\Bn::taka($plan->price, 0) }}</option>
                    @endforeach
                </select>
            </x-form.field>

            <x-form.field label="মেয়াদ (মাস)" name="subscriptionMonths" required>
                <input wire:model="subscriptionMonths" id="subscriptionMonths" type="number" min="1" max="60"
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
            </x-form.field>
        </div>
    </section>

    <div class="flex items-center gap-3">
        <button type="submit" wire:loading.attr="disabled"
                class="rounded-lg bg-brand-600 px-5 py-2.5 font-medium text-white hover:bg-brand-700 disabled:opacity-60">
            <span wire:loading.remove wire:target="save">তৈরি করুন</span>
            <span wire:loading wire:target="save">তৈরি হচ্ছে…</span>
        </button>
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-gray-600 hover:text-gray-900">বাতিল</a>
    </div>
</form>
