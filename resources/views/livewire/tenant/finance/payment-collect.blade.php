@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}

            @if ($lastPayment)
                <a href="{{ route('tenant.finance.receipt', $lastPayment) }}"
                   class="ml-2 font-medium underline">রসিদ ডাউনলোড</a>
            @endif
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    @if (! $student)
        {{-- ছাত্র খোঁজা --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <label for="search" class="block text-sm font-medium text-gray-700">ছাত্র খুঁজুন</label>
            <input type="text" id="search" wire:model.live.debounce.300ms="search"
                   placeholder="নাম, আইডি, পিতা বা মোবাইল"
                   class="mt-1 w-full max-w-md rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">

            @if (mb_strlen($search) >= 2)
                @if ($results->isEmpty())
                    <p class="mt-3 text-sm text-gray-600">কোনো ছাত্র পাওয়া যায়নি।</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100 rounded-lg border border-gray-200">
                        @foreach ($results as $result)
                            <li wire:key="result-{{ $result->id }}">
                                <button wire:click="selectStudent({{ $result->id }})"
                                        class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50">
                                    <span>
                                        <span class="font-medium text-gray-900">{{ $result->name }}</span>
                                        <span class="ml-2 text-sm text-gray-500">
                                            পিতা: {{ $result->father_name }}
                                        </span>
                                    </span>
                                    <span class="font-mono text-xs text-gray-500">
                                        {{ \App\Support\Bn::num($result->student_uid) }}
                                    </span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    @else
        {{-- বাছা ছাত্র --}}
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-5">
            <div>
                <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                <p class="text-sm text-gray-600">
                    পিতা: {{ $student->father_name }} ·
                    আইডি: {{ \App\Support\Bn::num($student->student_uid) }}
                </p>
            </div>

            <div class="text-right">
                <p class="text-xs text-gray-500">মোট বকেয়া</p>
                <p class="text-lg font-semibold {{ $outstanding > 0 ? 'text-red-700' : 'text-green-700' }}">
                    {{ \App\Support\Bn::taka($outstanding) }}
                </p>
            </div>

            <button wire:click="clearStudent"
                    class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                অন্য ছাত্র
            </button>
        </div>

        {{-- বকেয়া বিলগুলো --}}
        @if ($invoices->isEmpty())
            <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
                <p class="font-medium text-gray-900">কোনো বকেয়া নেই।</p>
                <p class="mt-1 text-sm text-gray-600">এই ছাত্রের সব বিল পরিশোধিত।</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-left">
                        <tr>
                            <th class="px-4 py-3 font-medium">মাস</th>
                            <th class="px-4 py-3 font-medium">বিল নম্বর</th>
                            <th class="px-4 py-3 font-medium">মোট</th>
                            <th class="px-4 py-3 font-medium">আদায়</th>
                            <th class="px-4 py-3 font-medium">বকেয়া</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}">
                                <td class="px-4 py-3 font-medium text-gray-900">
                                    {{ \App\Support\Bn::num($invoice->billing_month) }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-600">
                                    {{ \App\Support\Bn::num($invoice->invoice_no) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \App\Support\Bn::taka($invoice->net_amount, 0) }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ \App\Support\Bn::taka($invoice->paid_amount, 0) }}
                                </td>
                                <td class="px-4 py-3 font-medium text-red-700">
                                    {{ \App\Support\Bn::taka($invoice->dueAmount(), 0) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- আদায় ফর্ম --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-gray-900">টাকা গ্রহণ</h2>
                <p class="mt-1 text-sm text-gray-600">
                    পুরনো বকেয়া আগে শোধ হবে। কয়েক মাসের টাকা একসাথে দিলেও রসিদ একটাই।
                </p>

                <form wire:submit="save" class="mt-4 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-4">
                        <x-form.field label="টাকা" name="amount" :required="true"
                                      hint="বকেয়ার বেশি দিলে বাকিটা অগ্রিম জমা থাকবে">
                            <input type="number" id="amount" wire:model="amount"
                                   step="0.01" min="0" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="পদ্ধতি" name="method" :required="true">
                            <select id="method" wire:model="method" class="{{ $inputClass }}">
                                @foreach ($methodLabels as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </x-form.field>

                        <x-form.field label="তারিখ" name="paidOn" :required="true">
                            <input type="date" id="paidOn" wire:model="paidOn" class="{{ $inputClass }}">
                        </x-form.field>

                        <x-form.field label="রেফারেন্স" name="reference"
                                      hint="চেক বা ট্রানজেকশন নম্বর">
                            <input type="text" id="reference" wire:model="reference" class="{{ $inputClass }}">
                        </x-form.field>
                    </div>

                    <x-form.field label="মন্তব্য" name="notes">
                        <textarea id="notes" wire:model="notes" rows="2" class="{{ $inputClass }}"></textarea>
                    </x-form.field>

                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
                        আদায় করুন
                    </button>
                </form>
            </div>
        @endif
    @endif
</div>
