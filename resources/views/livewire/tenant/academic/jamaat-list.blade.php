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

    @if ($marhalaOptions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">আগে একটি বিভাগ তৈরি করুন।</p>
            <p class="mt-1 text-sm text-gray-600">ক্লাস সবসময় কোনো একটি বিভাগের অধীনে থাকে।</p>
            <a href="{{ route('tenant.academic.marhalas') }}"
               class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                বিভাগ তৈরি করুন
            </a>
        </div>
    @else
        @if ($showForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">
                    {{ $editingId ? 'ক্লাস সম্পাদনা' : 'নতুন ক্লাস' }}
                </h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-3">
                        <x-form.field label="বিভাগ" name="marhalaId" :required="true">
                            <select id="marhalaId" wire:model="marhalaId"
                                    class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($marhalaOptions as $option)
                                    <option value="{{ $option->id }}">{{ $option->name }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="নাম" name="name" :required="true" hint="যেমন — ১০ পারা, কায়দা, প্রথম বর্ষ">
                            <input type="text" id="name" wire:model="name"
                                   class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </x-form.field>

                        <x-form.field label="আরবি নাম" name="nameAr">
                            <input type="text" id="nameAr" wire:model="nameAr" dir="rtl"
                                   class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </x-form.field>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                            সংরক্ষণ করুন
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
                <div>
                    <label for="filterMarhala" class="block text-sm font-medium text-gray-700">বিভাগ</label>
                    <select id="filterMarhala" wire:model.live="filterMarhala"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সব বিভাগ</option>
                        @foreach ($marhalaOptions as $option)
                            <option value="{{ $option->id }}">{{ $option->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button wire:click="create"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    নতুন ক্লাস
                </button>
            </div>
        @endif

        @if ($jamaats->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-900">এখনো কোনো ক্লাস নেই।</p>
                <p class="mt-1 text-sm text-gray-600">
                    হিফজে "১০ পারা", মক্তবে "কায়দা", কিতাবে "প্রথম বর্ষ" — যেভাবে আপনার মাদরাসায় ডাকা হয়।
                </p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">ক্লাস</th>
                            <th class="px-4 py-3 font-medium">বিভাগ</th>
                            <th class="px-4 py-3 font-medium">শাখা</th>
                            <th class="px-4 py-3 font-medium">অবস্থা</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($jamaats as $jamaat)
                            <tr wire:key="jamaat-{{ $jamaat->id }}">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">{{ $jamaat->name }}</div>
                                    @if ($jamaat->name_ar)
                                        <div class="text-xs text-gray-500" dir="rtl">{{ $jamaat->name_ar }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $jamaat->marhala?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ \App\Support\Bn::num($jamaat->sections_count) }}</td>
                                <td class="px-4 py-3">
                                    @if ($jamaat->is_active)
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">চালু</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">বন্ধ</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1 whitespace-nowrap">
                                        <button wire:click="edit({{ $jamaat->id }})"
                                                class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                            সম্পাদনা
                                        </button>
                                        <button wire:click="toggleActive({{ $jamaat->id }})"
                                                class="rounded px-2 py-1 text-brand-700 hover:bg-brand-50">
                                            {{ $jamaat->is_active ? 'বন্ধ করুন' : 'চালু করুন' }}
                                        </button>
                                        <button wire:click="delete({{ $jamaat->id }})"
                                                wire:confirm="এই ক্লাসটি মুছে ফেলা হবে। নিশ্চিত?"
                                                class="rounded px-2 py-1 text-red-700 hover:bg-red-50">
                                            মুছুন
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif
</div>
