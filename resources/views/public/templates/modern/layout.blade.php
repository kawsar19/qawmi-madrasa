{{-- মডার্ন টেমপ্লেট — পরিষ্কার ও আধুনিক।

     ক্লাসিকের বিপরীত: সাদা জমিন, হালকা বর্ডার, প্রচুর ফাঁকা জায়গা।
     রঙ শুধু জোর দেওয়ার জায়গায় — পুরো হেডার রাঙানো নয়।

     লেআউট চুক্তি ক্লাসিকের মতোই: হেডার-ফুটার এখানে, পেজ শুধু $slot ভরে। --}}
@props(['settings', 'menu' => [], 'pageTitle' => null])

<x-layouts.public :settings="$settings" :title="$pageTitle">

    {{-- হালকা ডট গ্রিড — মডার্ন ধাঁচে জ্যামিতিক নকশার বদলে। --}}
    <svg width="0" height="0" class="absolute" aria-hidden="true">
        <defs>
            <pattern id="dots" width="24" height="24" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="1.5" fill="currentColor"/>
            </pattern>
        </defs>
    </svg>

    @include('public.templates.modern.partials.header')

    <main>
        {{ $slot }}
    </main>

    @include('public.templates.modern.partials.footer')
</x-layouts.public>
