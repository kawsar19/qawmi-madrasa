<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'সুপার অ্যাডমিন' }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
<div x-data="{ sidebarOpen: false }" class="flex min-h-screen">

    <aside
        class="fixed inset-y-0 left-0 z-40 w-60 -translate-x-full overflow-y-auto border-r border-gray-200 bg-gray-900 transition-transform lg:static lg:translate-x-0"
        :class="sidebarOpen && 'translate-x-0'"
    >
        <div class="flex h-16 items-center border-b border-gray-800 px-4">
            <span class="font-semibold text-white">সুপার অ্যাডমিন</span>
        </div>

        <nav class="space-y-0.5 p-3 text-sm">
            <x-central.nav-link :href="route('central.dashboard')" :active="request()->routeIs('central.dashboard')">
                ড্যাশবোর্ড
            </x-central.nav-link>
            <x-central.nav-link :href="route('central.tenants.index')" :active="request()->routeIs('central.tenants.*')">
                মাদরাসা
            </x-central.nav-link>
            <x-central.nav-link :href="route('central.plans.index')" :active="request()->routeIs('central.plans.*')">
                প্ল্যান
            </x-central.nav-link>
            <x-central.nav-link :href="route('central.subscriptions.index')" :active="request()->routeIs('central.subscriptions.*')">
                সাবস্ক্রিপশন
            </x-central.nav-link>
        </nav>
    </aside>

    <div
        x-show="sidebarOpen"
        x-on:click="sidebarOpen = false"
        class="fixed inset-0 z-30 bg-gray-900/40 lg:hidden"
        x-cloak
    ></div>

    <div class="flex min-w-0 flex-1 flex-col">
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
                <h1 class="truncate text-base font-semibold">{{ $heading ?? 'ড্যাশবোর্ড' }}</h1>
            </div>

            <div class="flex items-center gap-3 text-sm">
                <span class="hidden text-gray-600 sm:inline">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('central.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg px-3 py-1.5 text-gray-700 hover:bg-gray-100">লগআউট</button>
                </form>
            </div>
        </header>

        <main class="flex-1 p-4 lg:p-6">
            @if (session('status'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
@livewireScripts
</body>
</html>
