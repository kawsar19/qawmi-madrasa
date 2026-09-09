{{-- হিরো — সাদা জমিনে বাঁয়ে লেখা, ডানে ছবি/নকশা। ক্লাসিকের কেন্দ্রীভূত
     গাঢ় ব্যানারের বদলে অসম বিন্যাস। --}}
<section class="relative overflow-hidden bg-white">
    {{-- কোণে হালকা রঙের আভা --}}
    <div class="pointer-events-none absolute -right-32 -top-32 size-96 rounded-full opacity-[0.07] blur-3xl"
         style="background: var(--site-brand)" aria-hidden="true"></div>

    <div class="mx-auto max-w-6xl px-4 py-16 sm:py-24">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                @if ($settings->established_year)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-stone-50 px-3 py-1 text-xs font-medium text-stone-600">
                        <span class="size-1.5 rounded-full" style="background: var(--site-accent)"></span>
                        প্রতিষ্ঠা @bn($settings->established_year)
                    </span>
                @endif

                <h1 class="mt-5 text-3xl font-bold leading-tight text-balance text-stone-900 sm:text-5xl">
                    {{ $settings->displayTitle() }}
                </h1>

                @if ($settings->site_title_ar)
                    <p dir="rtl" class="mt-3 text-xl" style="color: var(--site-accent)">
                        {{ $settings->site_title_ar }}
                    </p>
                @endif

                @if ($settings->tagline)
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-pretty text-stone-600 sm:text-lg">
                        {{ $settings->tagline }}
                    </p>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    @if ($settings->show_admission_form)
                        <a href="{{ route('public.contact') }}"
                           class="rounded-xl px-6 py-3 text-sm font-medium text-white shadow-sm transition-opacity duration-150 hover:opacity-90"
                           style="background: var(--site-brand)">
                            ভর্তি আবেদন করুন
                        </a>
                    @endif
                    <a href="{{ route('public.about') }}"
                       class="rounded-xl border border-stone-300 px-6 py-3 text-sm font-medium text-stone-700 transition-colors duration-150 hover:bg-stone-50">
                        আমাদের সম্পর্কে
                    </a>
                </div>
            </div>

            {{-- ডান পাশ: ছবি থাকলে ছবি, নইলে বিসমিল্লাহ কার্ড --}}
            <div class="relative">
                @if ($settings->hero_image_path)
                    <img src="{{ asset('storage/'.$settings->hero_image_path) }}" alt=""
                         class="aspect-[4/3] w-full rounded-2xl object-cover shadow-lg">
                @else
                    <div class="relative aspect-[4/3] overflow-hidden rounded-2xl border border-stone-200 shadow-sm"
                         style="background: color-mix(in oklab, var(--site-brand) 6%, white)">
                        <div class="absolute inset-0 opacity-[0.15]" style="color: var(--site-brand)" aria-hidden="true">
                            <svg class="size-full"><rect width="100%" height="100%" fill="url(#dots)"/></svg>
                        </div>

                        <div class="relative grid h-full place-content-center px-8 text-center">
                            <p dir="rtl" class="text-2xl leading-relaxed sm:text-3xl" style="color: var(--site-brand)">
                                بِسْمِ اللهِ الرَّحْمٰنِ الرَّحِيْمِ
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- পরিসংখ্যান — ভাসমান কার্ড, গাঢ় পট্টি নয় --}}
        <div class="mt-14 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @foreach ([
                ['ছাত্র', $stats['students']],
                ['শিক্ষক ও কর্মচারী', $stats['employees']],
                ['বিভাগ', $stats['marhalas']],
                ['কিতাব', $stats['kitabs']],
            ] as [$label, $value])
                <div class="rounded-2xl border border-stone-200 bg-white p-5 transition-shadow duration-150 hover:shadow-md">
                    <p class="text-3xl font-bold tabular-nums" style="color: var(--site-brand)">
                        @bn($value)
                    </p>
                    <p class="mt-1 text-sm text-pretty text-stone-600">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
