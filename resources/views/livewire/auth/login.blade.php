<div>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-semibold text-gray-900">
            {{ tenancy()->initialized ? tenant('name') : config('app.name') }}
        </h1>
        <p class="mt-1 text-sm text-gray-600">
            {{ tenancy()->initialized ? 'মাদরাসা প্যানেলে প্রবেশ করুন' : 'সুপার অ্যাডমিন প্যানেল' }}
        </p>
    </div>

    <form wire:submit="login" class="space-y-5 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">
                ইমেইল বা ইউজারনেম
            </label>
            <input
                wire:model="email"
                id="email"
                type="text"
                autocomplete="username"
                autofocus
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">
                পাসওয়ার্ড
            </label>
            <input
                wire:model="password"
                id="password"
                type="password"
                autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-brand-500 focus:ring-brand-500"
            >
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-gray-700">
            <input wire:model="remember" type="checkbox" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
            মনে রাখুন
        </label>

        <button
            type="submit"
            class="w-full rounded-lg bg-brand-600 px-4 py-2.5 font-medium text-white transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 disabled:opacity-60"
            wire:loading.attr="disabled"
        >
            <span wire:loading.remove wire:target="login">প্রবেশ করুন</span>
            <span wire:loading wire:target="login">অপেক্ষা করুন…</span>
        </button>
    </form>

    @if ($demoCredentials !== [])
        <div class="mt-4 rounded-xl border border-dashed border-amber-300 bg-amber-50 p-4 text-sm">
            <p class="mb-2 font-medium text-amber-900">ডেমো লগইন (শুধু ডেভেলপমেন্টে)</p>

            <ul class="space-y-2">
                @foreach ($demoCredentials as $demo)
                    <li class="flex flex-wrap items-center gap-2">
                        <span class="text-amber-900">{{ $demo['label'] }}:</span>
                        <code class="rounded bg-white px-1.5 py-0.5 text-xs text-gray-800" dir="ltr">{{ $demo['email'] }}</code>
                        <code class="rounded bg-white px-1.5 py-0.5 text-xs text-gray-800" dir="ltr">{{ $demo['password'] }}</code>
                        <button
                            type="button"
                            wire:click="fillDemo(@js($demo['email']), @js($demo['password']))"
                            class="ml-auto rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-medium text-white hover:bg-amber-700"
                        >
                            বসিয়ে দিন
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
