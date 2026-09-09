@props(['title', 'subtitle' => null])

<section class="relative overflow-hidden border-b border-stone-200 bg-white">
    <div class="pointer-events-none absolute -right-24 -top-24 size-72 rounded-full opacity-[0.06] blur-3xl"
         style="background: var(--site-brand)" aria-hidden="true"></div>

    <div class="relative mx-auto max-w-6xl px-4 py-12 sm:py-16">
        <h1 class="text-2xl font-bold text-balance text-stone-900 sm:text-4xl">{{ $title }}</h1>

        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-sm text-pretty text-stone-600">{{ $subtitle }}</p>
        @endif
    </div>
</section>
