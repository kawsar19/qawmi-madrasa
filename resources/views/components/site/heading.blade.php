@props(['title', 'subtitle' => null])

<div class="text-center">
    <h2 class="text-2xl font-bold text-balance text-stone-900 sm:text-3xl">{{ $title }}</h2>

    <div class="mx-auto mt-4 flex items-center justify-center gap-2" aria-hidden="true">
        <span class="h-px w-12 bg-[var(--site-accent)]/40"></span>
        <span class="text-sm text-[var(--site-accent)]">❖</span>
        <span class="h-px w-12 bg-[var(--site-accent)]/40"></span>
    </div>

    @if ($subtitle)
        <p class="mx-auto mt-3 max-w-2xl text-sm text-pretty text-stone-600">{{ $subtitle }}</p>
    @endif
</div>
