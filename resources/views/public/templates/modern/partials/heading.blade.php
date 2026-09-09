@props(['label' => null, 'title', 'subtitle' => null, 'link' => null, 'linkLabel' => null])

<div class="flex flex-wrap items-end justify-between gap-4">
    <div class="max-w-xl">
        @if ($label)
            <p class="text-xs font-semibold uppercase tracking-wider" style="color: var(--site-accent)">
                {{ $label }}
            </p>
        @endif

        <h2 class="mt-2 text-2xl font-bold text-balance text-stone-900 sm:text-3xl">{{ $title }}</h2>

        @if ($subtitle)
            <p class="mt-2 text-sm text-pretty text-stone-600">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($link)
        <a href="{{ $link }}"
           class="inline-flex shrink-0 items-center gap-1.5 rounded-xl border border-stone-300 px-4 py-2.5 text-sm font-medium text-stone-700 transition-colors duration-150 hover:bg-stone-50">
            {{ $linkLabel ?? 'সব দেখুন' }}
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                 stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>
    @endif
</div>
