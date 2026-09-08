<div class="space-y-4">
    <div class="flex gap-2 text-sm">
        @foreach (['all' => 'সব', 'expiring' => '৩০ দিনে মেয়াদ শেষ', 'expired' => 'মেয়াদোত্তীর্ণ'] as $key => $label)
            <button wire:click="$set('filter', '{{ $key }}')"
                    @class([
                        'rounded-lg px-3 py-1.5',
                        'bg-brand-600 text-white' => $filter === $key,
                        'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50' => $filter !== $key,
                    ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
        <table class="w-full text-sm">
            <thead class="border-b border-gray-200 bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3 font-medium">মাদরাসা</th>
                    <th class="px-4 py-3 font-medium">প্ল্যান</th>
                    <th class="px-4 py-3 font-medium">শুরু</th>
                    <th class="px-4 py-3 font-medium">মেয়াদ শেষ</th>
                    <th class="px-4 py-3 font-medium">অবস্থা</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($subscriptions as $sub)
                    <tr wire:key="sub-{{ $sub->id }}">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $sub->tenant?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $sub->plan?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ \App\Support\Bn::num($sub->starts_at->format('d/m/Y')) }}</td>
                        <td class="px-4 py-3">
                            {{ \App\Support\Bn::num($sub->ends_at->format('d/m/Y')) }}
                            @if ($sub->ends_at->isPast())
                                <span class="ml-1 text-xs text-red-600">শেষ</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $sub->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">কোনো সাবস্ক্রিপশন নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $subscriptions->links() }}
</div>
