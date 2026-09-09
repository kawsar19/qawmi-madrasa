<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu">

    <x-site.page-header title="ফটো গ্যালারি" subtitle="মাদরাসার কার্যক্রমের ছবি" />

    <section class="bg-white py-14 sm:py-20">
        <div class="mx-auto max-w-6xl px-4">
            @if ($gallery->isEmpty())
                <x-site.empty message="এখনো কোনো ছবি যোগ করা হয়নি।" />
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($gallery as $image)
                        <figure class="group relative overflow-hidden rounded border border-stone-200">
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
            @endif
        </div>
    </section>
</x-dynamic-component>
