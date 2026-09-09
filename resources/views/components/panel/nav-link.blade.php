@props(['active' => false, 'icon' => null])

@php
    // 20x20 stroke icons, একই ভিজ্যুয়াল ওজন রাখতে সবগুলোর stroke-width সমান।
    $paths = [
        'home' => 'M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1V9.5Z',
        'calendar' => 'M8 3v3m8-3v3M4 9h16M5 6h14a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1Z',
        'layers' => 'm12 3 9 5-9 5-9-5 9-5Zm9 11-9 5-9-5',
        'grid' => 'M4 4h6v6H4V4Zm10 0h6v6h-6V4ZM4 14h6v6H4v-6Zm10 0h6v6h-6v-6Z',
        'book' => 'M5 4h9a3 3 0 0 1 3 3v13H8a3 3 0 0 1-3-3V4Zm12 3h2v13h-2',
        'clipboard' => 'M9 4h6v3H9V4Zm-2 1H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1h-1M9 12h6m-6 4h4',
        'users' => 'M16 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20M9.5 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM21 20v-1.5a4 4 0 0 0-3-3.87M16.5 4a4 4 0 0 1 0 7.75',
        'user-plus' => 'M15 20v-1.5a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4V20M9 10.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7ZM18 8v6m3-3h-6',
        'badge' => 'M12 3 4 6v5.5c0 4.4 3.2 7.9 8 9.5 4.8-1.6 8-5.1 8-9.5V6l-8-3Zm0 6v.01M12 12v3',
        'check' => 'M9 5h6a1 1 0 0 1 1 1v1H8V6a1 1 0 0 1 1-1Zm-2 2H6a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-1m-8 6 2 2 4-4',
        'book-open' => 'M12 7c-1.5-1.3-3.7-2-6-2H4v13h2c2.3 0 4.5.7 6 2m0-13c1.5-1.3 3.7-2 6-2h2v13h-2c-2.3 0-4.5.7-6 2m0-13v13',
        'pencil' => 'M4 20h4L19.5 8.5a2.1 2.1 0 0 0-3-3L5 17v3Zm10.5-13 3 3',
        'chart' => 'M4 20h16M7 16v-5m5 5V7m5 9v-3',
        'receipt' => 'M6 3h12a1 1 0 0 1 1 1v17l-3-2-2 2-2-2-2 2-2-2-3 2V4a1 1 0 0 1 1-1Zm3 5h6M9 12h6',
        'cash' => 'M3 7h18a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1Zm9 7a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z',
        'heart' => 'M12 20s-7-4.4-7-9a3.8 3.8 0 0 1 7-2.1A3.8 3.8 0 0 1 19 11c0 4.6-7 9-7 9Z',
        'ledger' => 'M5 4h14a1 1 0 0 1 1 1v15a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm3 4h8M8 12h8M8 16h5',
        'globe' => 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Zm0 0c2.5-2.3 3.8-5.2 3.8-9S14.5 5.3 12 3C9.5 5.3 8.2 8.2 8.2 12s1.3 6.7 3.8 9ZM3.5 9h17M3.5 15h17',
        'megaphone' => 'M3 11v2a1 1 0 0 0 1 1h2l5 4V6L6 10H4a1 1 0 0 0-1 1Zm14-3a5 5 0 0 1 0 8M6 14v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1v-3',
    ];
    $path = $icon !== null ? ($paths[$icon] ?? null) : null;
@endphp

<a
    {{ $attributes }}
    @class([
        // min-h-10 রাখে ছোট টাচ-টার্গেট এড়াতে।
        'flex min-h-10 items-center gap-2.5 rounded-lg px-3 py-2 transition-colors duration-150',
        'bg-brand-50 font-medium text-brand-700' => $active,
        'text-gray-700 hover:bg-gray-100 hover:text-gray-900' => ! $active,
    ])
    @if ($active) aria-current="page" @endif
>
    @if ($path)
        <svg
            class="size-5 shrink-0 {{ $active ? 'text-brand-600' : 'text-gray-400' }}"
            fill="none" stroke="currentColor" stroke-width="1.6"
            stroke-linecap="round" stroke-linejoin="round"
            viewBox="0 0 24 24" aria-hidden="true"
        >
            <path d="{{ $path }}"/>
        </svg>
    @endif

    <span class="truncate">{{ $slot }}</span>
</a>
