<section class="border-t border-stone-200 bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        @include('public.templates.modern.partials.heading', [
            'label' => 'পাঠ্যক্রম',
            'title' => 'শিক্ষা বিভাগ',
            'subtitle' => 'বেফাকুল মাদারিসিল আরাবিয়া বাংলাদেশের পাঠ্যক্রম অনুসারে',
            'link' => null,
        ])

        @if ($departments->isEmpty())
            <p class="mt-8 text-stone-500">বিভাগের তথ্য এখনো যোগ করা হয়নি।</p>
        @else
            <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($departments as $department)
                    <div class="flex items-center gap-3 rounded-2xl border border-stone-200 bg-white p-4 transition-shadow duration-150 hover:shadow-md">
                        <span class="grid size-10 shrink-0 place-items-center rounded-xl"
                              style="background: color-mix(in oklab, var(--site-brand) 10%, white); color: var(--site-brand)">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6"
                                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M5 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm12 3h2v13h-2"/>
                            </svg>
                        </span>

                        <div class="min-w-0">
                            <h3 class="truncate font-semibold text-stone-900">{{ $department->name }}</h3>
                            @if ($department->jamaats_count)
                                <p class="text-sm text-stone-500">@bn($department->jamaats_count)টি ক্লাস</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
