<form wire:submit="save" class="max-w-3xl space-y-6">
    <section class="rounded-xl border border-gray-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-gray-900">মাদরাসার তথ্য</h2>

        <div class="grid gap-4 sm:grid-cols-2">
            <x-form.field label="মাদরাসার নাম" name="name" required>
                <input wire:model="name" id="name" type="text"
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

            <x-form.field label="অবস্থা" name="status" required>
                <select wire:model="status" id="status"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    <option value="pending">অপেক্ষমাণ</option>
                    <option value="active">সক্রিয়</option>
                    <option value="suspended">স্থগিত</option>
                    <option value="expired">মেয়াদোত্তীর্ণ</option>
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

    <section class="rounded-xl border border-gray-200 bg-white p-5 text-sm">
        <h2 class="mb-3 font-semibold text-gray-900">ডোমেইন</h2>
        <ul class="space-y-1 text-gray-700">
            @foreach ($tenant->domains as $domain)
                <li class="flex items-center gap-2">
                    <a href="http://{{ $domain->domain }}" target="_blank" rel="noopener"
                       class="text-brand-700 hover:underline" dir="ltr">{{ $domain->domain }}</a>
                    @if ($domain->is_primary)
                        <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs text-gray-600">প্রধান</span>
                    @endif
                </li>
            @endforeach
        </ul>
    </section>

    <div class="flex items-center gap-3">
        <button type="submit" wire:loading.attr="disabled"
                class="rounded-lg bg-brand-600 px-5 py-2.5 font-medium text-white hover:bg-brand-700 disabled:opacity-60">
            সংরক্ষণ করুন
        </button>
        <a href="{{ route('central.tenants.index') }}" class="text-sm text-gray-600 hover:text-gray-900">ফিরে যান</a>
    </div>
</form>
