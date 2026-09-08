<div class="overflow-x-auto rounded-xl border border-gray-200 bg-white">
    <table class="w-full text-sm">
        <thead class="border-b border-gray-200 bg-gray-50 text-left">
            <tr>
                <th class="px-4 py-3 font-medium">প্ল্যান</th>
                <th class="px-4 py-3 font-medium">মূল্য</th>
                <th class="px-4 py-3 font-medium">ছাত্র সীমা</th>
                <th class="px-4 py-3 font-medium">শিক্ষক সীমা</th>
                <th class="px-4 py-3 font-medium">গ্রাহক</th>
                <th class="px-4 py-3 font-medium">অবস্থা</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($plans as $plan)
                <tr wire:key="plan-{{ $plan->id }}">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $plan->name }}</td>
                    <td class="px-4 py-3">{{ \App\Support\Bn::taka($plan->price, 0) }}</td>
                    <td class="px-4 py-3">{{ $plan->max_students ? \App\Support\Bn::num($plan->max_students) : 'সীমাহীন' }}</td>
                    <td class="px-4 py-3">{{ $plan->max_teachers ? \App\Support\Bn::num($plan->max_teachers) : 'সীমাহীন' }}</td>
                    <td class="px-4 py-3">{{ \App\Support\Bn::num($plan->subscriptions_count) }}</td>
                    <td class="px-4 py-3">
                        @if ($plan->is_active)
                            <span class="rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">সক্রিয়</span>
                        @else
                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/20">নিষ্ক্রিয়</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <button wire:click="toggleActive({{ $plan->id }})"
                                class="rounded px-2 py-1 text-brand-700 hover:bg-brand-50">
                            {{ $plan->is_active ? 'নিষ্ক্রিয় করুন' : 'সক্রিয় করুন' }}
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
