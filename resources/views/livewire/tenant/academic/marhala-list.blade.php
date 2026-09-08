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

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'বিভাগ সম্পাদনা' : 'নতুন বিভাগ' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field label="নাম" name="name" :required="true" hint="যেমন — মক্তব, হিফজ, কিতাব">
                        <input type="text" id="name" wire:model="name"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </x-form.field>

                    <x-form.field label="আরবি নাম" name="nameAr">
                        <input type="text" id="nameAr" wire:model="nameAr" dir="rtl"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </x-form.field>

                    <x-form.field label="ধরন" name="track" :required="true"
                                  hint="ফি, বোর্ডিং ও পরীক্ষার নিয়ম এর উপর নির্ভর করে">
                        <select id="track" wire:model="track"
                                class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                            @foreach ($trackLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-form.field>

                    <x-form.field label="সময়কাল (বছর)" name="durationYears" hint="কত বছরে এই বিভাগ শেষ হয়">
                        <input type="number" id="durationYears" wire:model="durationYears" min="1" max="15"
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
        <div class="flex justify-end">
            <button wire:click="create"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                নতুন বিভাগ
            </button>
        </div>
    @endif

    @if ($marhalas->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">এখনো কোনো বিভাগ নেই।</p>
            <p class="mt-1 text-sm text-gray-600">
                মক্তব, হিফজ বা কিতাব — যে বিভাগগুলো আপনার মাদরাসায় আছে সেগুলো যোগ করুন।
            </p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">বিভাগ</th>
                        <th class="px-4 py-3 font-medium">ধরন</th>
                        <th class="px-4 py-3 font-medium">সময়কাল</th>
                        <th class="px-4 py-3 font-medium">ক্লাস</th>
                        <th class="px-4 py-3 font-medium">অবস্থা</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($marhalas as $marhala)
                        <tr wire:key="marhala-{{ $marhala->id }}">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $marhala->name }}</div>
                                @if ($marhala->name_ar)
                                    <div class="text-xs text-gray-500" dir="rtl">{{ $marhala->name_ar }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $trackLabels[$marhala->track] ?? $marhala->track }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $marhala->duration_years ? \App\Support\Bn::num($marhala->duration_years).' বছর' : '—' }}
                            </td>
                            <td class="px-4 py-3">{{ \App\Support\Bn::num($marhala->jamaats_count) }}</td>
                            <td class="px-4 py-3">
                                @if ($marhala->is_active)
                                    <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">চালু</span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">বন্ধ</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1 whitespace-nowrap">
                                    <button wire:click="edit({{ $marhala->id }})"
                                            class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                        সম্পাদনা
                                    </button>
                                    <button wire:click="toggleActive({{ $marhala->id }})"
                                            class="rounded px-2 py-1 text-brand-700 hover:bg-brand-50">
                                        {{ $marhala->is_active ? 'বন্ধ করুন' : 'চালু করুন' }}
                                    </button>
                                    <button wire:click="delete({{ $marhala->id }})"
                                            wire:confirm="এই বিভাগটি মুছে ফেলা হবে। নিশ্চিত?"
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
</div>
