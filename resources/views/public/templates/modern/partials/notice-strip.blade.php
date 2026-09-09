<section class="border-t border-stone-200 bg-white py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        @include('public.templates.modern.partials.heading', [
            'label' => 'ঘোষণা',
            'title' => 'সাম্প্রতিক নোটিশ',
            'subtitle' => null,
            'link' => route('public.notices'),
            'linkLabel' => 'সব নোটিশ',
        ])

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($notices as $notice)
                @include('public.templates.modern.partials.notice-card', ['notice' => $notice])
            @endforeach
        </div>
    </div>
</section>
