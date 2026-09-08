<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ড্যাশবোর্ড' }} — {{ tenant('name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

    {{-- সাইডবার --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 -translate-x-full overflow-y-auto border-r border-gray-200 bg-white transition-transform lg:static lg:translate-x-0"
        :class="sidebarOpen && 'translate-x-0'"
    >
        <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-4">
            <span class="truncate font-semibold text-gray-900">{{ tenant('name') }}</span>
        </div>

        <nav class="space-y-6 p-3 text-sm">
            <x-panel.nav-group label="সাধারণ">
                <x-panel.nav-link :href="route('tenant.dashboard')" :active="request()->routeIs('tenant.dashboard')">
                    ড্যাশবোর্ড
                </x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="একাডেমিক">
                <x-panel.nav-link :href="route('tenant.academic.sessions')" :active="request()->routeIs('tenant.academic.sessions')">
                    শিক্ষাবর্ষ
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.academic.marhalas')" :active="request()->routeIs('tenant.academic.marhalas')">
                    বিভাগ
                </x-panel.nav-link>
                <x-panel.nav-link :href="route('tenant.academic.jamaats')" :active="request()->routeIs('tenant.academic.jamaats')">
                    ক্লাস
                </x-panel.nav-link>
                <x-panel.nav-link href="#">কিতাব</x-panel.nav-link>
                <x-panel.nav-link href="#">কারিকুলাম</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="ছাত্র ও শিক্ষক">
                <x-panel.nav-link :href="route('tenant.people.students')" :active="request()->routeIs('tenant.people.students')">
                    ছাত্র
                </x-panel.nav-link>
                <x-panel.nav-link href="#">ভর্তি</x-panel.nav-link>
                <x-panel.nav-link href="#">শিক্ষক ও কর্মচারী</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="দৈনন্দিন">
                <x-panel.nav-link href="#">হাজিরা</x-panel.nav-link>
                <x-panel.nav-link href="#">হিফজ</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="পরীক্ষা">
                <x-panel.nav-link href="#">পরীক্ষা ও নম্বর</x-panel.nav-link>
                <x-panel.nav-link href="#">ফলাফল</x-panel.nav-link>
            </x-panel.nav-group>

            <x-panel.nav-group label="আর্থিক">
                <x-panel.nav-link href="#">ফি ও বিল</x-panel.nav-link>
                <x-panel.nav-link href="#">আদায়</x-panel.nav-link>
                <x-panel.nav-link href="#">দান ও যাকাত</x-panel.nav-link>
                <x-panel.nav-link href="#">হিসাব</x-panel.nav-link>
            </x-panel.nav-group>
        </nav>
    </aside>

    {{-- সাইডবার ব্যাকড্রপ (মোবাইল) --}}
    <div
        x-show="sidebarOpen"
        x-on:click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"
        x-cloak
    ></div>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- টপবার --}}
        <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-gray-200 bg-white px-4">
            <button
                type="button"
                x-on:click="sidebarOpen = !sidebarOpen"
                class="rounded-lg p-2 text-gray-600 hover:bg-gray-100 lg:hidden"
                aria-label="মেনু"
            >
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="min-w-0 flex-1">
                <h1 class="truncate text-base font-semibold text-gray-900">{{ $heading ?? 'ড্যাশবোর্ড' }}</h1>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-gray-600 sm:inline">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-3 py-1.5 text-gray-700 hover:bg-gray-100">
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
