{{-- হিরো — গাঢ় সবুজ জমিনে জ্যামিতিক নকশা, উপরে স্বাগত বার্তা। --}}
<section class="relative overflow-hidden bg-[var(--site-brand)] text-white">
    {{-- ব্যানার ছবি থাকলে পেছনে, না থাকলে শুধু নকশা। --}}
    @if ($settings->hero_image_path)
        <img src="{{ media($settings->hero_image_path) }}" alt=""
             class="absolute inset-0 size-full object-cover opacity-25">
    @endif

    <div class="absolute inset-0 text-[var(--site-accent)] opacity-[0.13]" aria-hidden="true">
        <svg class="size-full"><rect width="100%" height="100%" fill="url(#girih)"/></svg>
    </div>

    {{-- নিচের দিকে গাঢ় করে লেখা পড়ার মতো রাখা --}}
    <div class="absolute inset-0 bg-gradient-to-b from-transparent to-black/25" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-4xl px-4 py-20 text-center sm:py-28">
        {{-- বিসমিল্লাহ --}}
        <p dir="rtl" class="text-lg text-[var(--site-accent)] sm:text-xl">
            بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ
        </p>

        <div class="mx-auto my-6 flex items-center justify-center gap-3" aria-hidden="true">
            <span class="h-px w-16 bg-[var(--site-accent)]/50"></span>
            <span class="text-[var(--site-accent)]">❖</span>
            <span class="h-px w-16 bg-[var(--site-accent)]/50"></span>
        </div>

        <h1 class="text-3xl font-bold leading-tight text-balance sm:text-5xl">
            {{ $settings->displayTitle() }}
        </h1>

        @if ($settings->tagline)
            <p class="mx-auto mt-4 max-w-2xl text-base text-pretty text-white/80 sm:text-lg">
                {{ $settings->tagline }}
            </p>
        @endif

        <div class="mt-9 flex flex-wrap justify-center gap-3">
            @if ($settings->show_admission_form)
                <a href="#bhorti"
                   class="rounded bg-[var(--site-accent)] px-6 py-3 text-sm font-medium shadow-lg transition-opacity duration-150 hover:opacity-90">
                    ভর্তি আবেদন করুন
                </a>
            @endif
            <a href="#porichiti"
               class="rounded border border-white/40 px-6 py-3 text-sm font-medium transition-colors duration-150 hover:bg-white/10">
                আমাদের সম্পর্কে
            </a>
        </div>
    </div>

    {{-- পরিসংখ্যান পট্টি --}}
    <div class="relative border-t border-white/15 bg-black/20">
        <div class="mx-auto grid max-w-5xl grid-cols-2 divide-x divide-white/10 px-4 sm:grid-cols-4">
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
</section>
