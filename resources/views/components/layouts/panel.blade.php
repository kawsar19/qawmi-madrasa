<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- প্রতিটি পেজ শুধু heading পাঠায়; ট্যাবের নামও সেটাই হওয়া উচিত। --}}
    <title>{{ $title ?? $heading ?? 'ড্যাশবোর্ড' }} — {{ tenant('name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<div x-data="{ sidebarOpen: false }">

    {{-- সাইডবার — ডেস্কটপে fixed, তাই মূল অংশ স্ক্রল করলেও মেনু স্থির থাকে।
         নিজের overflow-y-auto আছে, কারণ মেনু ভিউপোর্টের চেয়ে লম্বা হতে পারে। --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 ease-out lg:translate-x-0"
        :class="sidebarOpen && 'translate-x-0'"
    >
        <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-gray-200 px-4">
            <span class="grid size-8 shrink-0 place-items-center rounded-lg bg-brand-600 text-sm font-semibold text-white">
                {{ mb_substr(tenant('name'), 0, 1) }}
            </span>
            <span class="truncate font-semibold text-gray-900" title="{{ tenant('name') }}">
                {{ tenant('name') }}
            </span>
        </div>

        {{-- min-h-0 ছাড়া flex child স্ক্রল করে না। --}}
        <nav class="min-h-0 flex-1 space-y-5 overflow-y-auto p-3 text-sm">
            <x-panel.nav-group label="সাধারণ">
                <x-panel.nav-link :href="route('tenant.dashboard')" :active="request()->routeIs('tenant.dashboard')" icon="home">
                    ড্যাশবোর্ড
                </x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="একাডেমিক">
                <x-panel.nav-link :href="route('tenant.academic.sessions')" :active="request()->routeIs('tenant.academic.sessions')" icon="calendar">
                    শিক্ষাবর্ষ
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.academic.marhalas')" :active="request()->routeIs('tenant.academic.marhalas')" icon="layers">
                    বিভাগ
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.academic.jamaats')" :active="request()->routeIs('tenant.academic.jamaats')" icon="grid">
                    ক্লাস
                </x-panel.nav-link>
                <x-panel.nav-link href="#" icon="book">কিতাব</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="clipboard">কারিকুলাম</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="ছাত্র ও শিক্ষক">
                <x-panel.nav-link :href="route('tenant.people.students')" :active="request()->routeIs('tenant.people.students')" icon="users">
                    ছাত্র
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.people.admissions')" :active="request()->routeIs('tenant.people.admissions')" icon="user-plus">
                    ভর্তি
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.people.employees')" :active="request()->routeIs('tenant.people.employees')" icon="badge">
                    শিক্ষক ও কর্মচারী
                </x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="দৈনন্দিন">
                <x-panel.nav-link href="#" icon="check">হাজিরা</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="book-open">হিফজ</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="পরীক্ষা">
                <x-panel.nav-link href="#" icon="pencil">পরীক্ষা ও নম্বর</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="chart">ফলাফল</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="আর্থিক">
                <x-panel.nav-link href="#" icon="receipt">ফি ও বিল</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="cash">আদায়</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="heart">দান ও যাকাত</x-panel.nav-link>
                <x-panel.nav-link href="#" icon="ledger">হিসাব</x-panel.nav-link>
            </x-panel.nav-group>
        </nav>
    </aside>

    {{-- সাইডবার ব্যাকড্রপ (মোবাইল) --}}
    <div
        x-show="sidebarOpen"
        x-on:click="sidebarOpen = false"
        x-transition.opacity.duration.200ms
        class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"
        x-cloak
    ></div>

    {{-- সাইডবার fixed, তাই মূল অংশকে সমান margin দিয়ে সরাতে হয়। --}}
    <div class="flex min-h-screen flex-col lg:ml-64">
        {{-- টপবার --}}
        <header class="sticky top-0 z-20 flex h-16 shrink-0 items-center gap-3 border-b border-gray-200 bg-white/85 px-4 backdrop-blur-sm lg:px-6">
            <button
                type="button"
                x-on:click="sidebarOpen = !sidebarOpen"
                class="-ml-1 grid size-10 place-items-center rounded-lg text-gray-600 transition-colors duration-150 hover:bg-gray-100 lg:hidden"
                aria-label="মেনু"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold text-gray-900">{{ $heading ?? 'ড্যাশবোর্ড' }}</h1>
            </div>

            <div class="flex items-center gap-2 text-sm">
                <span class="hidden text-gray-600 sm:inline">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="rounded-lg px-3 py-2 font-medium text-gray-700 transition-colors duration-150 hover:bg-gray-100 hover:text-gray-900"
                    >
                        লগআউট
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
