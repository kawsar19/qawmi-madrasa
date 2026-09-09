<section id="bibhag" class="scroll-mt-24 bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <x-site.heading title="শিক্ষা বিভাগ"
                        subtitle="বেফাকুল মাদারিসিল আরাবিয়া বাংলাদেশের পাঠ্যক্রম অনুসারে" />

        @if ($departments->isEmpty())
            <p class="mt-10 text-center text-stone-500">বিভাগের তথ্য এখনো যোগ করা হয়নি।</p>
        @else
            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($departments as $department)
                    <div class="group rounded-lg border border-stone-200 bg-white p-5 transition-shadow duration-150 hover:shadow-md">
                        <div class="flex items-start gap-3">
                            <span class="grid size-10 shrink-0 place-items-center rounded border border-[var(--site-accent)]/30 bg-[var(--site-accent)]/10 text-[var(--site-accent)]">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6"
                                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M5 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm12 3h2v13h-2"/>
                                </svg>
                            </span>

                            <div class="min-w-0">
                                <h3 class="font-semibold text-pretty text-stone-900">{{ $department->name }}</h3>
                                @if ($department->jamaats_count)
                                    <p class="mt-0.5 text-sm text-stone-600">
                                        @bn($department->jamaats_count)টি ক্লাস
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
