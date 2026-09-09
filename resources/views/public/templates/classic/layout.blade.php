{{-- ক্লাসিক টেমপ্লেটের লেআউট — হেডার ও ফুটার একবার, সব পেজে ব্যবহৃত।

     প্রতিটি পেজ এটিকে <x-dynamic-component :component="..."> হিসেবে ডাকে,
     তাই পেজ ফাইলে হেডার/ফুটার কপি করতে হয় না।

     নতুন টেমপ্লেট বানাতে এই ফোল্ডারটি কপি করে ক্লাস বদলালেই হয় —
     PHP, রুট বা কন্ট্রোলার ছুঁতে হয় না। --}}
@props(['settings', 'menu' => [], 'pageTitle' => null])

<x-layouts.public :settings="$settings" :title="$pageTitle">

    {{-- ইসলামি জ্যামিতিক প্যাটার্ন — একবার সংজ্ঞায়িত, কয়েক জায়গায় ব্যবহৃত। --}}
    <svg width="0" height="0" class="absolute" aria-hidden="true">
        <defs>
            <pattern id="girih" width="40" height="40" patternUnits="userSpaceOnUse"
                     patternTransform="rotate(45)">
                <path d="M20 0 L40 20 L20 40 L0 20Z" fill="none"
                      stroke="currentColor" stroke-width="1" opacity="0.5"/>
                <circle cx="20" cy="20" r="6" fill="none"
                        stroke="currentColor" stroke-width="1" opacity="0.5"/>
            </pattern>
        </defs>
    </svg>

    @include('public.templates.classic.partials.topbar')
    @include('public.templates.classic.partials.header')

    <main>
        {{ $slot }}
    </main>

    @include('public.templates.classic.partials.footer')
</x-layouts.public>
