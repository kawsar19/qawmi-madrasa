@props(['settings' => null, 'title' => null])

@php
    $settings ??= \App\Models\Cms\SiteSetting::current();
    $siteTitle = $settings->displayTitle();
@endphp

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? $title.' — '.$siteTitle : $siteTitle }}</title>

    @if ($settings->about_short)
        <meta name="description" content="{{ Str::limit(strip_tags($settings->about_short), 160) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- wire:loading এলিমেন্ট ডিফল্টে লুকানোর CSS এখান থেকে আসে; ছাড়া
         "লোড হচ্ছে…" লেখাগুলো সবসময় দেখা যায়। --}}
    @livewireStyles

    {{-- প্রতি মাদরাসার রঙ CSS ভেরিয়েবল হিসেবে — তাই ২০০ মাদরাসার জন্য
         ২০০টা আলাদা CSS বিল্ড লাগে না, একটাই বান্ডিল সবার কাজে লাগে। --}}
    <style>
        :root {
            --site-brand: {{ $settings->brand_color }};
            --site-accent: {{ $settings->accent_color }};
        }

        /* Alpine লোড হওয়ার আগে x-show/x-collapse এলিমেন্ট দেখা যাওয়া ঠেকায়,
           নইলে মোবাইল মেনু এক ঝলক খোলা অবস্থায় দেখা যায়। */
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-stone-50 text-stone-800 antialiased">
    {{ $slot }}

    {{-- Alpine আসে Livewire-এর সাথে; ছাড়া x-collapse/x-show কাজ করে না
         আর মোবাইল মেনু খোলাই থেকে যায়। --}}
    @livewireScripts
</body>
</html>
