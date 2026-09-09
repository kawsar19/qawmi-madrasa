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
            চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই। আগে শিক্ষাবর্ষ ঠিক করুন, তারপর ফি বসানো যাবে।
        </div>
    @elseif ($jamaatOptions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">এখনো কোনো ক্লাস নেই।</p>
            <p class="mt-1 text-sm text-gray-600">ফি বসানোর আগে ক্লাস তৈরি করতে হবে।</p>
            <a href="{{ route('tenant.academic.jamaats') }}"
               class="mt-3 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                ক্লাস তৈরি করুন
            </a>
        </div>
    @else
        @if ($showForm)
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">
                    {{ $editingId ? 'রেট সম্পাদনা' : 'নতুন রেট' }}
                </h2>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-4">
                        <x-form.field label="ক্লাস" name="jamaatId" :required="true">
                            <select id="jamaatId" wire:model="jamaatId" class="{{ $inputClass }}">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($jamaatOptions as $jamaat)
                                    <option value="{{ $jamaat->id }}">{{ $jamaat->name }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="ফি খাত" name="feeHeadId" :required="true">
                            <select id="feeHeadId" wire:model="feeHeadId" class="{{ $inputClass }}">
                                <option value="">— বাছাই করুন —</option>
                                @foreach ($feeHeadOptions as $feeHead)
                                    <option value="{{ $feeHead->id }}">{{ $feeHead->name }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="আবাসিক ধরন" name="residencyType"
                                      hint="খালি রাখলে সব ছাত্রের জন্য একই রেট">
                            <select id="residencyType" wire:model="residencyType" class="{{ $inputClass }}">
                                <option value="">সবার জন্য</option>
                                @foreach ($residencyLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="টাকা" name="amount" :required="true">
                            <input type="number" id="amount" wire:model="amount"
                                   step="0.01" min="0" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isActive"
                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        চালু আছে
                    </label>

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
                    <label for="filterJamaat" class="block text-sm font-medium text-gray-700">ক্লাস</label>
                    <select id="filterJamaat" wire:model.live="filterJamaat"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সব ক্লাস</option>
                        @foreach ($jamaatOptions as $jamaat)
                            <option value="{{ $jamaat->id }}">{{ $jamaat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button wire:click="create"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    নতুন রেট
                </button>
            </div>
        @endif

        @if ($structures->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-900">এখনো কোনো রেট বসানো হয়নি।</p>
                <p class="mt-1 text-sm text-gray-600">
                    "নতুন রেট" দিয়ে ক্লাসভিত্তিক মাসিক বেতন ঠিক করুন।
                </p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">ক্লাস</th>
                            <th class="px-4 py-3 font-medium">খাত</th>
                            <th class="px-4 py-3 font-medium">আবাসিক ধরন</th>
                            <th class="px-4 py-3 font-medium">টাকা</th>
                            <th class="px-4 py-3 font-medium">অবস্থা</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($structures as $structure)
                            <tr wire:key="structure-{{ $structure->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $structure->jamaat?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $structure->feeHead?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $structure->residency_type
                                        ? ($residencyLabels[$structure->residency_type] ?? '—')
                                        : 'সবার জন্য' }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ \App\Support\Bn::taka($structure->amount, 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($structure->is_active)
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">চালু</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">বন্ধ</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex justify-end gap-1 whitespace-nowrap">
                                        <button wire:click="edit({{ $structure->id }})"
                                                class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                            সম্পাদনা
                                        </button>
                                        <button wire:click="delete({{ $structure->id }})"
                                                wire:confirm="এই রেটটি মুছে ফেলা হবে। নিশ্চিত?"
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
