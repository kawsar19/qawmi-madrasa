<section class="bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <x-site.heading title="আমাদের সম্পর্কে" />

        <div class="mt-10 grid gap-10 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <p class="leading-relaxed text-pretty text-stone-700">
                    {{ $settings->about_short ?: Str::limit(strip_tags((string) $settings->about_full), 400) }}
                </p>

                <a href="{{ route('public.about') }}"
                   class="mt-6 inline-flex items-center gap-1.5 rounded border border-[var(--site-brand)] px-5 py-2.5 text-sm font-medium text-[var(--site-brand)] transition-colors duration-150 hover:bg-[var(--site-brand)] hover:text-white">
                    বিস্তারিত পড়ুন
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>

            @if ($settings->principal_message)
                <aside class="rounded-lg border-l-4 border-[var(--site-accent)] bg-stone-50 p-6">
                    <p class="text-sm font-semibold text-stone-900">মুহতামিমের বাণী</p>
                    <blockquote class="mt-3 text-sm leading-relaxed text-pretty text-stone-700">
                        {{ Str::limit($settings->principal_message, 220) }}
                    </blockquote>
                    @if ($settings->principal_name)
                        <p class="mt-4 border-t border-stone-200 pt-3 text-sm font-medium text-stone-900">
                            — {{ $settings->principal_name }}
                        </p>
                    @endif
                </aside>
            @endif
        </div>
    </div>
</section>
