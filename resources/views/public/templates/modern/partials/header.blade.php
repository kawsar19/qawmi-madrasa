{{-- সাদা স্টিকি হেডার — মেনু পিল আকারে, রঙ শুধু সক্রিয় আইটেমে। --}}
<header class="sticky top-0 z-30 border-b border-stone-200/80 bg-white/90 backdrop-blur-md"
        x-data="{ open: false }">
    <div class="mx-auto max-w-6xl px-4">
        <div class="flex h-18 items-center gap-4 py-3">
            <a href="{{ route('public.home') }}" class="flex min-w-0 items-center gap-3">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl text-base font-bold text-white shadow-sm"
                      style="background: var(--site-brand)">
                    @if ($settings->logo_path)
                        <img src="{{ media($settings->logo_path) }}" alt=""
                             class="size-11 rounded-xl object-cover">
                    @else
                        {{ mb_substr($settings->displayTitle(), 0, 1) }}
                    @endif
                </span>

                <span class="min-w-0">
                    <span class="block truncate text-base font-bold leading-tight text-stone-900 sm:text-lg">
                        {{ $settings->displayTitle() }}
                    </span>
                    @if ($settings->tagline)
                        <span class="block truncate text-xs text-stone-500">{{ $settings->tagline }}</span>
                    @elseif ($settings->site_title_ar)
                        <span dir="rtl" class="block truncate text-xs text-stone-500">{{ $settings->site_title_ar }}</span>
                    @endif
                </span>
            </a>

            <div class="hidden flex-1 lg:block"></div>

            {{-- পিল নেভিগেশন --}}
            <nav class="hidden items-center gap-0.5 rounded-full bg-stone-100 p-1 text-sm lg:flex">
                @foreach ($menu as $item)
                    @php($isActive = request()->routeIs($item['route']))
                    <a href="{{ route($item['route']) }}"
                       @if ($isActive) aria-current="page" @endif
                       @class([
                           'rounded-full px-4 py-2 transition-colors duration-150',
                           'bg-white font-medium text-stone-900 shadow-sm' => $isActive,
                           'text-stone-600 hover:text-stone-900' => ! $isActive,
                       ])>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            @if ($settings->show_admission_form)
                <a href="{{ route('public.contact') }}"
                   class="hidden rounded-full px-5 py-2.5 text-sm font-medium text-white transition-opacity duration-150 hover:opacity-90 lg:block"
                   style="background: var(--site-brand)">
                    ভর্তি আবেদন
                </a>
            @endif

            <button type="button" x-on:click="open = !open"
                    class="ml-auto grid size-10 place-items-center rounded-lg text-stone-600 transition-colors hover:bg-stone-100 lg:hidden"
                    aria-label="মেনু">
                <svg class="size-6" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path x-show="! open" d="M4 6h16M4 12h16M4 18h16"/>
                    <path x-show="open" x-cloak d="M6 6l12 12M18 6L6 18"/>
                </svg>
            </button>
        </div>

        {{-- মোবাইল মেনু --}}
        <nav x-show="open" x-cloak class="border-t border-stone-200 py-2 lg:hidden">
            @foreach ($menu as $item)
                <a href="{{ route($item['route']) }}"
                   @class([
                       'block rounded-lg px-3 py-2.5 text-sm',
                       'bg-stone-100 font-medium text-stone-900' => request()->routeIs($item['route']),
                       'text-stone-600 hover:bg-stone-50' => ! request()->routeIs($item['route']),
                   ])>
                    {{ $item['label'] }}
                </a>
            @endforeach

            @if ($settings->show_admission_form)
                <a href="{{ route('public.contact') }}"
                   class="mt-2 block rounded-lg px-3 py-2.5 text-center text-sm font-medium text-white"
                   style="background: var(--site-brand)">
                    ভর্তি আবেদন
                </a>
            @endif
        </nav>
    </div>
</header>
