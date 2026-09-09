<section class="bg-stone-100 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <x-site.heading title="শিক্ষকমণ্ডলী" />

        <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($teachers as $teacher)
                <x-site.teacher-card :teacher="$teacher" :labels="$designationLabels" />
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('public.teachers') }}"
               class="inline-flex items-center gap-1.5 rounded border border-[var(--site-brand)] px-5 py-2.5 text-sm font-medium text-[var(--site-brand)] transition-colors duration-150 hover:bg-[var(--site-brand)] hover:text-white">
                সব শিক্ষক দেখুন
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
