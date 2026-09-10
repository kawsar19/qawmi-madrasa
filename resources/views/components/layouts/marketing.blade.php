<!DOCTYPE html>
<html lang="bn" dir="ltr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }} — কওমি মাদরাসার জন্য সমন্বিত সফটওয়্যার</title>
    <meta name="description" content="ভর্তি, হাজিরা, পরীক্ষা, ফি, দান, হিফজ ও বোর্ডিং — বাংলাদেশের কওমি মাদরাসার জন্য পুরোপুরি বাংলায় তৈরি ম্যানেজমেন্ট সিস্টেম।">
    <meta name="theme-color" content="#1a6b46">

    <meta property="og:type" content="website">
    <meta property="og:locale" content="bn_BD">
    <meta property="og:title" content="{{ $title ?? config('app.name') }}">
    <meta property="og:description" content="কওমি মাদরাসার সব হিসাব এক জায়গায় — ভর্তি, হাজিরা, পরীক্ষা, ফি, দান ও হিফজ।">
    <meta property="og:url" content="{{ url()->current() }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-gray-900 antialiased">
    {{ $slot }}
</body>
</html>
