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
            চলতি শিক্ষাবর্ষ নির্ধারণ করা নেই। আগে শিক্ষাবর্ষ ঠিক করুন।
        </div>
    @else
        {{-- বিল রান --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">মাসিক বিল তৈরি</h2>
            <p class="mt-1 text-sm text-gray-600">
                চলতি বর্ষে পড়ছে এমন সব ছাত্রের বিল একসাথে তৈরি হবে।
                একই মাসে দুবার চালালেও দ্বিগুণ বিল হবে না।
            </p>

            <form wire:submit="generate" class="mt-4 space-y-4">
                <div>
                    <label for="billingMonth" class="block text-sm font-medium text-gray-700">
                        মাস <span class="text-red-600">*</span>
                    </label>
                    <input type="month" id="billingMonth" wire:model="billingMonth"
                           class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                    @error('billingMonth')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700">কোন খাতগুলো বিলে বসবে</p>
                    <p class="mt-0.5 text-xs text-gray-500">
                        মাসিক বেতন ও সিট ভাড়া প্রতি মাসেই আসে। ভর্তি বা পরীক্ষার ফি
                        বছরে একবার — যে মাসে নেবেন সেই মাসে টিক দিন।
                    </p>

                    <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2">
                        @foreach ($feeHeadOptions as $head)
                            <label class="flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" wire:model="selectedHeads" value="{{ $head->id }}"
                                       class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                {{ $head->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit"
                        class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                    <span wire:loading.remove wire:target="generate">বিল তৈরি করুন</span>
                    <span wire:loading wire:target="generate">তৈরি হচ্ছে…</span>
                </button>
            </form>
        </div>

        {{-- ফিল্টার --}}
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="filterMonth" class="block text-sm font-medium text-gray-700">মাস</label>
                <input type="month" id="filterMonth" wire:model.live="filterMonth"
                       class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
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

        @if ($invoices->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-900">এই মাসে কোনো বিল নেই।</p>
                <p class="mt-1 text-sm text-gray-600">উপরে মাস বেছে "বিল তৈরি করুন" চাপুন।</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">বিল নম্বর</th>
                            <th class="px-4 py-3 font-medium">ছাত্র</th>
                            <th class="px-4 py-3 font-medium">ক্লাস</th>
                            <th class="px-4 py-3 font-medium">মাস</th>
                            <th class="px-4 py-3 font-medium">মোট</th>
                            <th class="px-4 py-3 font-medium">আদায়</th>
                            <th class="px-4 py-3 font-medium">বকেয়া</th>
                            <th class="px-4 py-3 font-medium">অবস্থা</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}">
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                    {{ \App\Support\Bn::num($invoice->invoice_no) }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ $invoice->student?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $invoice->jamaat?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \App\Support\Bn::num($invoice->billing_month) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \App\Support\Bn::taka($invoice->net_amount, 0) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \App\Support\Bn::taka($invoice->paid_amount, 0) }}
                                </td>
                                <td class="px-4 py-3 font-medium {{ $invoice->dueAmount() > 0 ? 'text-red-700' : 'text-gray-400' }}">
                                    {{ \App\Support\Bn::taka($invoice->dueAmount(), 0) }}
                                </td>
                                <td class="px-4 py-3">
                                    <span @class([
                                        'rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset',
                                        'bg-green-50 text-green-700 ring-green-600/20' => $invoice->status === \App\Models\Finance\Invoice::STATUS_PAID,
                                        'bg-amber-50 text-amber-700 ring-amber-600/20' => $invoice->status === \App\Models\Finance\Invoice::STATUS_PARTIAL,
                                        'bg-red-50 text-red-700 ring-red-600/20' => $invoice->status === \App\Models\Finance\Invoice::STATUS_UNPAID,
                                    ])>
                                        {{ $statusLabels[$invoice->status] ?? $invoice->status }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $invoices->links() }}</div>
        @endif
    @endif
</div>
