{{-- প্রধান হেডার — মেনু SiteTemplate::menu() থেকে আসে, তাই বন্ধ সেকশনের
     লিংক এখানে দেখা যায় না। --}}
<header class="sticky top-0 z-30 border-b-2 border-[var(--site-accent)] bg-[var(--site-brand)] text-white shadow-lg"
        x-data="{ open: false }">
    <div class="mx-auto max-w-6xl px-4">
        <div class="flex h-20 items-center gap-4">
            <a href="{{ route('public.home') }}" class="flex min-w-0 items-center gap-3">
                <span class="grid size-12 shrink-0 place-items-center rounded-full border-2 border-[var(--site-accent)]/60 bg-white/10">
                    @if ($settings->logo_path)
                        <img src="{{ asset('storage/'.$settings->logo_path) }}" alt=""
                             class="size-10 rounded-full object-cover">
                    @else
                        <span class="text-lg font-bold text-[var(--site-accent)]">
                            {{ mb_substr($settings->displayTitle(), 0, 1) }}
                        </span>
                    @endif
                </span>

                <span class="min-w-0">
                    <span class="block truncate text-lg font-bold leading-tight sm:text-xl">
                        {{ $settings->displayTitle() }}
                    </span>
                    @if ($settings->site_title_ar)
                        <span dir="rtl" class="block truncate text-sm text-[var(--site-accent)]">
                            {{ $settings->site_title_ar }}
                        </span>
                    @elseif ($settings->tagline)
                        <span class="block truncate text-xs text-white/70">{{ $settings->tagline }}</span>
                    @endif
                </span>
            </a>

            {{-- মেনুকে ডানে ঠেলে দেয়, নইলে নামের সাথে ওভারল্যাপ করে। --}}
            <div class="hidden flex-1 lg:block"></div>

            <nav class="hidden items-center gap-1 text-sm lg:flex">
                @foreach ($menu as $item)
                    @php($isActive = request()->routeIs($item['route']))
                    <a href="{{ route($item['route']) }}"
                       @if ($isActive) aria-current="page" @endif
                       @class([
                           'rounded px-3 py-2 transition-colors duration-150',
                           'bg-white/15 font-medium text-white' => $isActive,
                           'text-white/85 hover:bg-white/10 hover:text-white' => ! $isActive,
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                @if ($settings->show_admission_form)
                    <a href="{{ route('public.contact') }}"
                       class="ml-2 rounded bg-[var(--site-accent)] px-4 py-2 font-medium transition-opacity duration-150 hover:opacity-90">
                        ভর্তি আবেদন
                    </a>
                @endif
            </nav>

            <button type="button" x-on:click="open = !open"
                    class="ml-auto grid size-10 place-items-center rounded text-white/90 transition-colors hover:bg-white/10 lg:hidden"
                    aria-label="মেনু">
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- মোবাইল মেনু --}}
        <nav x-show="open" x-cloak class="border-t border-white/10 pb-3 lg:hidden">
            @foreach ($menu as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'block rounded px-3 py-2.5 text-sm',
                       'bg-white/15 font-medium' => request()->routeIs($item['route']),
                       'text-white/85 hover:bg-white/10' => ! request()->routeIs($item['route']),
                   ])>
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if ($settings->show_admission_form)
                <a href="{{ route('public.contact') }}"
                   class="mt-1 block rounded bg-[var(--site-accent)] px-3 py-2.5 text-center text-sm font-medium">
                    ভর্তি আবেদন
                </a>
            @endif
        </nav>
    </div>
</header>
