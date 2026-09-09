{{-- হোমপেজে গ্যালারির ঝলক। --}}
<section class="bg-white py-16 sm:py-24">
    <div class="mx-auto max-w-6xl px-4">
        @include('public.templates.modern.partials.heading', [
            'title' => 'ফটো গ্যালারি',
            'subtitle' => 'মাদরাসার কিছু মুহূর্ত',
        ])

        <div class="mt-12 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($gallery as $image)
                <figure class="group overflow-hidden rounded-2xl border border-stone-200">
                    <img src="{{ $image->url() }}" alt="{{ $image->title ?? '' }}" loading="lazy"
                         class="aspect-square w-full object-cover transition-transform duration-300 group-hover:scale-105">

                    @if ($image->title)
                        <figcaption class="px-3 py-2 text-xs text-pretty text-stone-600">
                            {{ $image->title }}
                        </figcaption>
                    @endif
                </figure>
            @endforeach
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('public.gallery') }}"
               class="inline-block rounded-xl border border-stone-300 px-6 py-3 text-sm font-medium text-stone-700 transition-colors duration-150 hover:bg-stone-50">
                সব ছবি দেখুন
            </a>
        </div>
    </div>
</section>
