{{-- হোমপেজে গ্যালারির ঝলক — বিস্তারিত আলাদা পেজে। --}}
<section class="bg-stone-100 py-14 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        <x-site.heading title="ফটো গ্যালারি" subtitle="মাদরাসার কিছু মুহূর্ত" />

        <div class="mt-10 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($gallery as $image)
                <figure class="group relative overflow-hidden rounded border border-stone-200 bg-white">
                    <img src="{{ $image->url() }}" alt="{{ $image->title ?? '' }}" loading="lazy"
                         class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105">

                    @if ($image->title)
                        <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-3 py-2 text-xs text-white">
                            {{ $image->title }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        <div class="mt-8 text-center">
            <a href="{{ route('public.gallery') }}"
               class="inline-block rounded border border-[var(--site-brand)] px-6 py-2.5 text-sm font-medium text-[var(--site-brand)] transition-colors duration-150 hover:bg-[var(--site-brand)] hover:text-white">
                সব ছবি দেখুন
            </a>
        </div>
    </div>
</section>
