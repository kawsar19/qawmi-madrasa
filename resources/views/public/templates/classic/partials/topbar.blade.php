{{-- ছোট উপরের বার — যোগাযোগ --}}
<div class="border-b border-white/10 bg-[var(--site-brand)] text-white/90">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-2 text-xs">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
            @if ($settings->phone)
                <a href="tel:{{ $settings->phone }}" class="flex items-center gap-1.5 opacity-90 transition-opacity hover:opacity-100">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>
                    </svg>
                    <span class="tabular-nums">@bn($settings->phone)</span>
                </a>
            @endif
            @if ($settings->email)
                <a href="mailto:{{ $settings->email }}" class="hidden items-center gap-1.5 opacity-90 hover:opacity-100 sm:flex">
                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm0 2 8 6 8-6"/>
                    </svg>
                    {{ $settings->email }}
                </a>
            @endif
        </div>

        <div class="flex items-center gap-3">
            @if ($settings->established_year)
                <span class="opacity-80">প্রতিষ্ঠা: @bn($settings->established_year)</span>
            @endif
            <a href="{{ route('tenant.login') }}" class="opacity-80 hover:opacity-100">প্যানেল</a>
        </div>
    </div>
</div>
