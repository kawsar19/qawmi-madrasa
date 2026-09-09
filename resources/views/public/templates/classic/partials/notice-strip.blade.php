<section class="bg-stone-100 py-16 sm:py-20">
    <div class="mx-auto max-w-4xl px-4">
        <x-site.heading title="সাম্প্রতিক নোটিশ" />

        <div class="mt-10 divide-y divide-stone-200 overflow-hidden rounded-lg border border-stone-200 bg-white">
            @foreach ($notices as $notice)
                <x-site.notice-row :notice="$notice" />
            @endforeach
        </div>

        <div class="mt-6 text-center">
            <a href="{{ route('public.notices') }}"
               class="inline-flex items-center gap-1.5 rounded border border-[var(--site-brand)] px-5 py-2.5 text-sm font-medium text-[var(--site-brand)] transition-colors duration-150 hover:bg-[var(--site-brand)] hover:text-white">
                সব নোটিশ দেখুন
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
    </div>
</section>
