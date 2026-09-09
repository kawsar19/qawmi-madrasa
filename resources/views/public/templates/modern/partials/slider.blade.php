{{-- স্লাইডার — CSS scroll-snap, JavaScript ছাড়া।
     ক্লাসিকের পূর্ণ-প্রস্থ গাঢ় ব্যানারের বদলে গোল কোণের কার্ড, দুই পাশে
     ফাঁকা জায়গা — মডার্নের বাকি অংশের সঙ্গে মেলে। --}}
<section class="bg-white pt-6 sm:pt-10" aria-roledescription="carousel" aria-label="মাদরাসার ছবি">
    <div class="mx-auto max-w-6xl px-4">
        <div class="relative overflow-hidden rounded-2xl shadow-sm">
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
                             class="h-[clamp(18rem,52vh,30rem)] w-full object-cover">

                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"
                             aria-hidden="true"></div>

                        @if ($slide->title || $slide->subtitle || $slide->hasLink())
                            {{-- ক্লাসিকের কেন্দ্রীভূত লেখার বদলে বাঁয়ে --}}
                            <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-10">
                                <div class="max-w-xl">
                                    @if ($slide->title)
                                        <h2 class="text-2xl font-bold leading-tight text-balance sm:text-4xl">
                                            {{ $slide->title }}
                                        </h2>
                                    @endif

                                    @if ($slide->subtitle)
                                        <p class="mt-3 text-sm text-pretty text-white/85 sm:text-base">
                                            {{ $slide->subtitle }}
                                        </p>
                                    @endif

                                    @if ($slide->hasLink())
                                        <a href="{{ $slide->link_url }}"
                                           class="mt-5 inline-block rounded-xl bg-white px-5 py-2.5 text-sm font-medium shadow-sm transition-opacity duration-150 hover:opacity-90"
                                           style="color: var(--site-brand)">
                                            {{ $slide->link_label }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($slides->count() > 1)
                <div class="absolute bottom-4 right-4 flex gap-1.5">
                    @foreach ($slides as $slide)
                        <a href="#slide-{{ $slide->id }}"
                           class="grid size-7 place-content-center rounded-full bg-white/25 text-[11px] font-medium text-white backdrop-blur-sm transition-colors duration-150 hover:bg-white/50"
                           aria-label="{{ $loop->iteration }} নম্বর ছবি">
                            @bn($loop->iteration)
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</section>
