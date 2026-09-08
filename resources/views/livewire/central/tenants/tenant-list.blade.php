<div class="space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="নাম, slug বা EIIN দিয়ে খুঁজুন"
            class="w-full max-w-xs rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500"
        >

        <select wire:model.live="status" class="rounded-lg border-gray-300 text-sm shadow-sm focus:border-brand-500 focus:ring-brand-500">
            <option value="">সব অবস্থা</option>
            <option value="active">সক্রিয়</option>
            <option value="pending">অপেক্ষমাণ</option>
            <option value="suspended">স্থগিত</option>
            <option value="expired">মেয়াদোত্তীর্ণ</option>
        </select>

        <a href="{{ route('central.tenants.create') }}"
           class="ml-auto rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">
            নতুন মাদরাসা
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">মাদরাসা</th>
                    <th class="px-4 py-3 font-medium">ডোমেইন</th>
                    <th class="px-4 py-3 font-medium">অবস্থা</th>
                    <th class="px-4 py-3 font-medium">মেয়াদ</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($tenants as $tenant)
                    <tr wire:key="tenant-{{ $tenant->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-gray-900">{{ $tenant->name }}</div>
                            <div class="text-xs text-gray-500">{{ $tenant->slug }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $tenant->domains->firstWhere('is_primary', true)?->domain ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <x-central.status-badge :status="$tenant->status" />
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @php($sub = $tenant->subscriptions->first())
                            {{ $sub ? \App\Support\Bn::num($sub->ends_at->format('d/m/Y')) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('central.tenants.edit', $tenant) }}"
                               class="rounded px-2 py-1 text-brand-700 hover:bg-brand-50">সম্পাদনা</a>

                            @if ($tenant->status === \App\Models\Central\Tenant::STATUS_SUSPENDED)
                                <button wire:click="activate({{ $tenant->id }})"
                                        wire:confirm="এই মাদরাসা সক্রিয় করবেন?"
                                        class="rounded px-2 py-1 text-green-700 hover:bg-green-50">সক্রিয় করুন</button>
                            @else
                                <button wire:click="suspend({{ $tenant->id }})"
                                        wire:confirm="এই মাদরাসা স্থগিত করবেন? তারা প্যানেলে ঢুকতে পারবে না।"
                                        class="rounded px-2 py-1 text-red-700 hover:bg-red-50">স্থগিত</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                            কোনো মাদরাসা পাওয়া যায়নি।
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $tenants->links() }}
</div>
