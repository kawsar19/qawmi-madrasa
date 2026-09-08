@props(['label', 'value', 'href' => null, 'icon' => null])

@php
    $paths = [
        'users' => 'M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20M9.5 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM21 20v-1.5a4 4 0 0 0-3-3.87M16.5 4a4 4 0 0 1 0 7.75',
        'badge' => 'M12 3 4 6v5.5c0 4.4 3.2 7.9 8 9.5 4.8-1.6 8-5.1 8-9.5V6l-8-3Zm0 6v.01M12 12v3',
        'layers' => 'm12 3 9 5-9 5-9-5 9-5Zm9 11-9 5-9-5',
        'book' => 'M5 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm12 3h2v13h-2',
    ];
    $path = $icon !== null ? ($paths[$icon] ?? null) : null;

    // outer radius = inner radius + padding (concentric radius)
    $classes = 'group flex items-start justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 transition-shadow duration-150';
@endphp

<{{ $href ? 'a' : 'div' }}
    @if ($href) href="{{ $href }}" @endif
    class="{{ $classes }} {{ $href ? 'hover:border-gray-300 hover:shadow-sm' : '' }}"
>
    <div class="min-w-0">
        <p class="truncate text-sm text-gray-600">{{ $label }}</p>
        {{-- tabular-nums: গণনা বদলালে সংখ্যা লাফায় না। --}}
        <p class="mt-1 text-2xl font-semibold tabular-nums text-gray-900">
            {{ \App\Support\Bn::num($value) }}
        </p>
    </div>

    @if ($path)
        <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-gray-50 text-gray-400 transition-colors duration-150 {{ $href ? 'group-hover:bg-brand-50 group-hover:text-brand-600' : '' }}">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.6"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="{{ $path }}"/>
            </svg>
        </span>
    @endif
</{{ $href ? 'a' : 'div' }}>
