<section class="border-t border-stone-200 bg-stone-50/60 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <div class="grid gap-10 lg:grid-cols-[3fr_2fr]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: var(--site-accent)">
                    পরিচিতি
                </p>
                <h2 class="mt-2 text-2xl font-bold text-balance text-stone-900 sm:text-3xl">
                    আমাদের সম্পর্কে
                </h2>

                <p class="mt-5 leading-relaxed text-pretty text-stone-600">
                    {{ $settings->about_short ?: Str::limit(strip_tags((string) $settings->about_full), 400) }}
                </p>

                <a href="{{ route('public.about') }}"
                   class="mt-6 inline-flex items-center gap-1.5 text-sm font-medium hover:underline"
                   style="color: var(--site-brand)">
                    বিস্তারিত পড়ুন
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            </div>

            @if ($settings->principal_message)
                <aside>
                    <div class="rounded-2xl border border-stone-200 bg-white p-6">
                        <svg class="size-8 opacity-20" style="color: var(--site-brand)" fill="currentColor"
                             viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 7H6a3 3 0 0 0-3 3v7h7v-7H6a1 1 0 0 1 1-1h2V7Zm9 0h-3a3 3 0 0 0-3 3v7h7v-7h-4a1 1 0 0 1 1-1h2V7Z"/>
                        </svg>

                        <blockquote class="mt-3 text-sm leading-relaxed text-pretty text-stone-700">
                            {{ Str::limit($settings->principal_message, 240) }}
                        </blockquote>

                        @if ($settings->principal_name)
                            <p class="mt-5 flex items-center gap-2 border-t border-stone-100 pt-4 text-sm">
                                <span class="size-8 shrink-0 rounded-full" style="background: color-mix(in oklab, var(--site-brand) 15%, white)"></span>
                                <span>
                                    <span class="block font-medium text-stone-900">{{ $settings->principal_name }}</span>
                                    <span class="block text-xs text-stone-500">মুহতামিম</span>
                                </span>
                            </p>
                        @endif
                    </div>
                </aside>
            @endif
        </div>
    </div>
</section>
