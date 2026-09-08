<x-layouts.panel heading="ড্যাশবোর্ড">
    <div class="space-y-6">
        {{-- চলতি শিক্ষাবর্ষ না থাকলে সতর্কবার্তা — কাজের লিংক সহ, নইলে
             ব্যবহারকারী জানেন না পরের ধাপ কোথায়। --}}
        @if (! $currentSession)
            <div class="flex gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <svg class="mt-0.5 size-5 shrink-0 text-amber-600" fill="none" stroke="currentColor"
                     stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/>
                </svg>
                <div class="min-w-0">
                    <p class="font-medium text-pretty">কোনো চলতি শিক্ষাবর্ষ নির্ধারণ করা হয়নি।</p>
                    <p class="mt-1 text-pretty">হাজিরা, পরীক্ষা ও ফি-র কাজ শুরু করার আগে একটি শিক্ষাবর্ষ তৈরি করুন।</p>
                    <a href="{{ route('tenant.academic.sessions') }}"
                       class="mt-2 inline-flex min-h-9 items-center gap-1.5 rounded-lg bg-amber-600 px-3 py-1.5 font-medium text-white transition-colors duration-150 hover:bg-amber-700">
                        শিক্ষাবর্ষ নির্ধারণ করুন
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-panel.stat label="মোট ছাত্র" :value="$stats['students']"
                          icon="users" :href="route('tenant.people.students')" />
            <x-panel.stat label="শিক্ষক ও কর্মচারী" :value="$stats['employees']"
                          icon="badge" :href="route('tenant.people.employees')" />
            <x-panel.stat label="মারহালা" :value="$stats['marhalas']"
                          icon="layers" :href="route('tenant.academic.marhalas')" />
            <x-panel.stat label="কিতাব" :value="$stats['kitabs']" icon="book" />
        </div>

        <div class="grid gap-4 lg:grid-cols-3">
            {{-- মাদরাসার তথ্য --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5 lg:col-span-2">
                <h2 class="font-semibold text-balance text-gray-900">স্বাগতম</h2>

                <dl class="mt-4 grid gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                    <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                        <dt class="text-gray-600">মাদরাসা</dt>
                        <dd class="truncate font-medium">{{ tenant('name') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                        <dt class="text-gray-600">চলতি শিক্ষাবর্ষ</dt>
                        <dd class="font-medium">
                            @if ($currentSession)
                                {{ $currentSession->name }}
                            @else
                                <span class="text-gray-400">নির্ধারিত নয়</span>
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                        <dt class="text-gray-600">আজকের তারিখ</dt>
                        <dd class="font-medium tabular-nums">@bn(now()->format('d/m/Y'))</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 py-2">
                        <dt class="text-gray-600">হিজরি</dt>
                        <dd class="font-medium tabular-nums">@hijri(now())</dd>
                    </div>
                </dl>
            </div>

            {{-- দ্রুত কাজ --}}
            <div class="rounded-xl border border-gray-200 bg-white p-5">
                <h2 class="font-semibold text-balance text-gray-900">দ্রুত কাজ</h2>

                <div class="mt-4 space-y-1.5 text-sm">
                    @foreach ([
                        ['নতুন ছাত্র ভর্তি', route('tenant.people.students')],
                        ['শিক্ষক / কর্মচারী যোগ', route('tenant.people.employees')],
                        ['ক্লাস ব্যবস্থাপনা', route('tenant.academic.jamaats')],
                        ['শিক্ষাবর্ষ', route('tenant.academic.sessions')],
                    ] as [$label, $url])
                        <a href="{{ $url }}"
                           class="flex min-h-10 items-center justify-between gap-2 rounded-lg px-3 py-2 text-gray-700 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-900">
                            <span class="truncate">{{ $label }}</span>
                            <svg class="size-4 shrink-0 text-gray-400" fill="none" stroke="currentColor"
                                 stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                 viewBox="0 0 24 24" aria-hidden="true">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.panel>
