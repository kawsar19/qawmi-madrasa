<x-layouts.panel heading="ড্যাশবোর্ড">
    <div class="space-y-6">
        {{-- চলতি শিক্ষাবর্ষ না থাকলে সতর্কবার্তা --}}
        @if (! $currentSession)
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <p class="font-medium">কোনো চলতি শিক্ষাবর্ষ নির্ধারণ করা হয়নি।</p>
                <p class="mt-1">হাজিরা, পরীক্ষা ও ফি-র কাজ শুরু করার আগে একটি শিক্ষাবর্ষ তৈরি করুন।</p>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-panel.stat label="মোট ছাত্র" :value="$stats['students']" />
            <x-panel.stat label="শিক্ষক ও কর্মচারী" :value="$stats['employees']" />
            <x-panel.stat label="মারহালা" :value="$stats['marhalas']" />
            <x-panel.stat label="কিতাব" :value="$stats['kitabs']" />
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-5">
            <h2 class="font-semibold text-gray-900">স্বাগতম</h2>
            <dl class="mt-3 grid gap-x-6 gap-y-2 text-sm sm:grid-cols-2">
                <div class="flex justify-between gap-4 border-b border-gray-100 py-1.5">
                    <dt class="text-gray-600">মাদরাসা</dt>
                    <dd class="font-medium">{{ tenant('name') }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 py-1.5">
                    <dt class="text-gray-600">চলতি শিক্ষাবর্ষ</dt>
                    <dd class="font-medium">{{ $currentSession?->name ?? 'নির্ধারিত নয়' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 py-1.5">
                    <dt class="text-gray-600">আজকের তারিখ</dt>
                    <dd class="font-medium">@bn(now()->format('d/m/Y'))</dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 py-1.5">
                    <dt class="text-gray-600">হিজরি</dt>
                    <dd class="font-medium">@hijri(now())</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.panel>
