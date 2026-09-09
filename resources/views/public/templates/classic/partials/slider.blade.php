{{-- স্লাইডার — CSS scroll-snap, কোনো JavaScript নেই।
     মোবাইলে আঙুলে swipe, ডেস্কটপে নিচের নম্বরে ক্লিক। JS বন্ধ থাকলেও চলে,
     আর ছবি বদলাতে থাকে না বলে পড়ার সময় হারায় না। --}}
<section class="relative bg-[var(--site-brand)]" aria-roledescription="carousel" aria-label="মাদরাসার ছবি">
    <div class="flex snap-x snap-mandatory overflow-x-auto scroll-smooth
                [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
        @foreach ($slides as $index => $slide)
            <div id="slide-{{ $slide->id }}"
                 class="relative w-full shrink-0 snap-start snap-always"
                 role="group"
                 aria-roledescription="slide"
                 aria-label="{{ $loop->iteration }} / {{ $loop->count }}">

                <img src="{{ $slide->url() }}"
                     alt="{{ $slide->title ?? '' }}"
                     @if ($index > 0) loading="lazy" @endif
                     class="h-[clamp(20rem,58vh,34rem)] w-full object-cover">

                {{-- লেখা পড়ার মতো রাখতে নিচ থেকে গাঢ় ছায়া --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/25 to-transparent"
                     aria-hidden="true"></div>

                @if ($slide->title || $slide->subtitle || $slide->hasLink())
                    <div class="absolute inset-x-0 bottom-0">
                        <div class="mx-auto max-w-4xl px-4 pb-14 text-center text-white sm:pb-20">
                            @if ($slide->title)
                                <h2 class="text-2xl font-bold leading-tight text-balance drop-shadow sm:text-4xl">
                                    {{ $slide->title }}
                                </h2>
                            @endif

                            @if ($slide->subtitle)
                                <p class="mx-auto mt-3 max-w-2xl text-sm text-pretty text-white/85 drop-shadow sm:text-base">
                                    {{ $slide->subtitle }}
                                </p>
                            @endif

                            @if ($slide->hasLink())
                                <a href="{{ $slide->link_url }}"
                                   class="mt-6 inline-block rounded bg-[var(--site-accent)] px-6 py-3 text-sm font-medium shadow-lg transition-opacity duration-150 hover:opacity-90">
                                    {{ $slide->link_label }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ক্রম নির্দেশক — একাধিক ছবি থাকলেই।
         anchor লিংক বলে scroll-smooth নিজেই স্লাইড করে, JS লাগে না। --}}
    @if ($slides->count() > 1)
        <div class="absolute inset-x-0 bottom-4 flex justify-center gap-2">
            @foreach ($slides as $slide)
                <a href="#slide-{{ $slide->id }}"
                   class="grid size-7 place-content-center rounded-full border border-white/40 bg-black/25 text-[11px] font-medium text-white/90 backdrop-blur-sm transition-colors duration-150 hover:bg-[var(--site-accent)]"
                   aria-label="{{ $loop->iteration }} নম্বর ছবি">
                    @bn($loop->iteration)
                </a>
            @endforeach
        </div>
    @endif
</section>

{{-- পরিসংখ্যান পট্টি — স্থির হিরোতে যেমন ছিল, স্লাইডারের নিচেও তেমন। --}}
<div class="bg-[var(--site-brand)] text-white">
    <div class="mx-auto grid max-w-5xl grid-cols-2 divide-x divide-white/10 border-t border-white/15 px-4 sm:grid-cols-4">
        @foreach ([
            ['ছাত্র', $stats['students']],
            ['শিক্ষক ও কর্মচারী', $stats['employees']],
            ['বিভাগ', $stats['marhalas']],
            ['কিতাব', $stats['kitabs']],
        ] as [$label, $value])
            <div class="px-3 py-5 text-center">
                <p class="text-2xl font-bold tabular-nums text-[var(--site-accent)] sm:text-3xl">
                    @bn($value)
                </p>
                <p class="mt-1 text-xs text-white/70 sm:text-sm">{{ $label }}</p>
            </div>
        @endforeach
    </div>
</div>
