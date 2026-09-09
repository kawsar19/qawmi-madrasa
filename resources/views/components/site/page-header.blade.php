{{-- ভেতরের পেজের শিরোনাম পট্টি — হোমপেজের হিরোর ছোট সংস্করণ। --}}
@props(['title', 'subtitle' => null])

<section class="relative overflow-hidden border-b-2 border-[var(--site-accent)] bg-[var(--site-brand)] text-white">
    <div class="absolute inset-0 text-[var(--site-accent)] opacity-[0.13]" aria-hidden="true">
        <svg class="size-full"><rect width="100%" height="100%" fill="url(#girih)"/></svg>
    </div>

    <div class="relative mx-auto max-w-6xl px-4 py-12 text-center sm:py-16">
        <h1 class="text-2xl font-bold text-balance sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mx-auto mt-3 max-w-2xl text-sm text-pretty text-white/75">{{ $subtitle }}</p>
        @endif
    </div>
</section>
