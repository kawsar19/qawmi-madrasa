<x-layouts.central heading="ড্যাশবোর্ড">
    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-panel.stat label="মোট মাদরাসা" :value="$stats['tenants']" />
            <x-panel.stat label="সক্রিয়" :value="$stats['active']" />
            <x-panel.stat label="স্থগিত" :value="$stats['suspended']" />
            <x-panel.stat label="মোট ব্যবহারকারী" :value="$stats['users']" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="font-semibold text-gray-900">৩০ দিনের মধ্যে মেয়াদ শেষ</h2>
            </div>

            @if ($expiringSoon->isEmpty())
                <p class="px-5 py-8 text-center text-sm text-gray-500">
                    আগামী ৩০ দিনে কোনো সাবস্ক্রিপশনের মেয়াদ শেষ হচ্ছে না।
                </p>
            @else
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($expiringSoon as $sub)
                            <tr>
                                <td class="px-5 py-3 font-medium text-gray-900">{{ $sub->tenant?->name }}</td>
                                <td class="px-5 py-3 text-gray-600">{{ $sub->plan?->name }}</td>
                                <td class="px-5 py-3 text-right text-gray-600">
                                    {{ \App\Support\Bn::num($sub->ends_at->format('d/m/Y')) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.central>
