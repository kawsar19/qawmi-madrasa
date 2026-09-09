@php($inputClass = 'mt-1 w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500')

<div class="space-y-5">
    @if (session('status'))
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if ($showForm)
        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">
                {{ $editingId ? 'নোটিশ সম্পাদনা' : 'নতুন নোটিশ' }}
            </h2>

            <form wire:submit="save" class="mt-4 space-y-4">
                <x-form.field label="শিরোনাম" name="title" :required="true">
                    <input type="text" id="title" wire:model="title" class="{{ $inputClass }}">
                </x-form.field>

                <x-form.field label="বিবরণ" name="body"
                              hint="অনুচ্ছেদ আলাদা করতে দুই লাইন ফাঁকা দিন">
                    <textarea id="body" wire:model="body" rows="5" class="{{ $inputClass }}"></textarea>
                </x-form.field>

                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.field label="ধরন" name="category" :required="true">
                        <select id="category" wire:model="category" class="{{ $inputClass }}">
                            @foreach ($categoryLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </x-form.field>

                    <x-form.field label="প্রকাশের তারিখ" name="publishedOn">
                        <input type="date" id="publishedOn" wire:model="publishedOn" class="{{ $inputClass }}">
                    </x-form.field>

                    <x-form.field label="মেয়াদ শেষ" name="expiresOn"
                                  hint="খালি রাখলে চিরস্থায়ী">
                        <input type="date" id="expiresOn" wire:model="expiresOn" class="{{ $inputClass }}">
                    </x-form.field>
                </div>

                <x-form.field label="সংযুক্তি" name="attachment"
                              hint="রুটিন বা ফলাফলের শিট — PDF বা ছবি, সর্বোচ্চ ৫ MB">
                    <input type="file" id="attachment" wire:model="attachment" accept=".pdf,image/*"
                           class="mt-1 w-full text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200">
                </x-form.field>
                <div wire:loading wire:target="attachment" class="text-sm text-gray-500">আপলোড হচ্ছে…</div>

                <div class="flex flex-wrap gap-5">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isPublished"
                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        সাইটে দেখাবে
                    </label>
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" wire:model="isPinned"
                               class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                        উপরে আটকে রাখুন
                    </label>
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                        {{ $editingId ? 'সংরক্ষণ করুন' : 'যোগ করুন' }}
                    </button>
                    <button type="button" wire:click="cancel"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-50">
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
                           placeholder="শিরোনাম বা বিবরণ"
                           class="mt-1 w-64 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                </div>

                <div>
                    <label for="filterCategory" class="block text-sm font-medium text-gray-700">ধরন</label>
                    <select id="filterCategory" wire:model.live="filterCategory"
                            class="mt-1 rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
                        <option value="">সব</option>
                        @foreach ($categoryLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button wire:click="create"
                    class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white transition-colors duration-150 hover:bg-brand-700">
                নতুন নোটিশ
            </button>
        </div>
    @endif

    @if ($notices->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="font-medium text-gray-900">
                {{ $search !== '' || $filterCategory !== '' ? 'কোনো নোটিশ পাওয়া যায়নি।' : 'এখনো কোনো নোটিশ নেই।' }}
            </p>
            @if ($search === '' && $filterCategory === '')
                <p class="mt-1 text-sm text-gray-600">"নতুন নোটিশ" দিয়ে শুরু করুন।</p>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
            <table class="w-full text-sm">
                <thead class="border-b border-gray-200 bg-gray-50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">শিরোনাম</th>
                        <th class="px-4 py-3 font-medium">ধরন</th>
                        <th class="px-4 py-3 font-medium">তারিখ</th>
                        <th class="px-4 py-3 font-medium">অবস্থা</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($notices as $notice)
                        <tr wire:key="notice-{{ $notice->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($notice->is_pinned)
                                        <span class="text-amber-600" title="উপরে আটকানো">📌</span>
                                    @endif
                                    <span class="font-medium text-gray-900">{{ $notice->title }}</span>
                                </div>
                                @if ($notice->attachment_path)
                                    <span class="text-xs text-gray-500">সংযুক্তি আছে</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $categoryLabels[$notice->category] ?? '—' }}</td>
                            <td class="px-4 py-3 tabular-nums text-gray-600">
                                @bn($notice->published_on?->format('d/m/Y') ?? '—')
                                @if ($notice->expires_on)
                                    <div class="text-xs text-gray-400">
                                        মেয়াদ: @bn($notice->expires_on->format('d/m/Y'))
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="togglePublished({{ $notice->id }})"
                                        @class([
                                            'rounded-full px-2 py-0.5 text-xs transition-colors duration-150',
                                            'bg-green-50 text-green-700 hover:bg-green-100' => $notice->is_published,
                                            'bg-gray-100 text-gray-600 hover:bg-gray-200' => ! $notice->is_published,
                                        ])>
                                    {{ $notice->is_published ? 'প্রকাশিত' : 'খসড়া' }}
                                </button>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button wire:click="edit({{ $notice->id }})"
                                        class="rounded px-2 py-1 text-gray-700 transition-colors hover:bg-gray-100">
                                    সম্পাদনা
                                </button>
                                <button wire:click="delete({{ $notice->id }})"
                                        wire:confirm="এই নোটিশ মুছে ফেলবেন?"
                                        class="rounded px-2 py-1 text-red-600 transition-colors hover:bg-red-50">
                                    মুছুন
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>{{ $notices->links() }}</div>
    @endif
</div>
