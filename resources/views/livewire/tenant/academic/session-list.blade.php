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

    {{-- ফর্ম --}}
    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'শিক্ষাবর্ষ সম্পাদনা' : 'নতুন শিক্ষাবর্ষ' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.field label="নাম" name="name" :required="true" hint="যেমন — ১৪৪৬-১৪৪৭ হিজরি">
                        <input type="text" id="name" wire:model="name"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </x-form.field>

                    <div class="grid grid-cols-2 gap-3">
                        <x-form.field label="হিজরি বর্ষ" name="hijriYear">
                            <input type="text" id="hijriYear" wire:model="hijriYear"
                                   class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </x-form.field>

                        <x-form.field label="ইংরেজি বর্ষ" name="gregorianYear">
                            <input type="text" id="gregorianYear" wire:model="gregorianYear"
                                   class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        </x-form.field>
                    </div>

                    <x-form.field label="শুরুর তারিখ" name="startsOn">
                        <input type="date" id="startsOn" wire:model="startsOn"
                               class="mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    </x-form.field>

                    <x-form.field label="শেষের তারিখ" name="endsOn">
                        <input type="date" id="endsOn" wire:model="endsOn"
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
                নতুন শিক্ষাবর্ষ
            </button>
        </div>
    @endif

    {{-- তালিকা --}}
    @if ($sessions->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">এখনো কোনো শিক্ষাবর্ষ নেই।</p>
            <p class="mt-1 text-sm text-gray-600">
                হাজিরা, পরীক্ষা ও ফি-র কাজ শুরু করার আগে একটি শিক্ষাবর্ষ তৈরি করুন।
            </p>
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">নাম</th>
                        <th class="px-4 py-3 font-medium">হিজরি</th>
                        <th class="px-4 py-3 font-medium">ইংরেজি</th>
                        <th class="px-4 py-3 font-medium">সময়কাল</th>
                        <th class="px-4 py-3 font-medium">অবস্থা</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($sessions as $session)
                        <tr wire:key="session-{{ $session->id }}">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $session->name }}</td>
                            <td class="px-4 py-3">{{ $session->hijri_year ? \App\Support\Bn::num($session->hijri_year) : '—' }}</td>
                            <td class="px-4 py-3">{{ $session->gregorian_year ? \App\Support\Bn::num($session->gregorian_year) : '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">
                                @if ($session->starts_on && $session->ends_on)
                                    @bn($session->starts_on->format('d/m/Y')) — @bn($session->ends_on->format('d/m/Y'))
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1">
                                    @if ($session->is_current)
                                        <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">চলতি</span>
                                    @endif
                                    @if ($session->is_locked)
                                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">লক</span>
                                    @endif
                                    @if (! $session->is_current && ! $session->is_locked)
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1 whitespace-nowrap">
                                    @unless ($session->is_current)
                                        <button wire:click="makeCurrent({{ $session->id }})"
                                                class="rounded px-2 py-1 text-brand-700 hover:bg-brand-50">
                                            চলতি করুন
                                        </button>
                                    @endunless

                                    @unless ($session->is_locked)
                                        <button wire:click="edit({{ $session->id }})"
                                                class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                            সম্পাদনা
                                        </button>
                                    @endunless

                                    <button wire:click="toggleLock({{ $session->id }})"
                                            class="rounded px-2 py-1 text-gray-700 hover:bg-gray-100">
                                        {{ $session->is_locked ? 'আনলক' : 'লক' }}
                                    </button>

                                    @unless ($session->is_current || $session->is_locked)
                                        <button wire:click="delete({{ $session->id }})"
                                                wire:confirm="এই শিক্ষাবর্ষটি মুছে ফেলা হবে। নিশ্চিত?"
                                                class="rounded px-2 py-1 text-red-700 hover:bg-red-50">
                                            মুছুন
                                        </button>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
