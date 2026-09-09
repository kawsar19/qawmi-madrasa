<section class="border-t border-stone-200 bg-stone-50/60 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        @include('public.templates.modern.partials.heading', [
            'label' => 'যোগাযোগ',
            'title' => 'যোগাযোগ ও ভর্তি',
            'subtitle' => null,
            'link' => route('public.contact'),
            'linkLabel' => 'বিস্তারিত',
        ])

        <div class="mt-8">
            @include('public.templates.modern.partials.contact-grid')
        </div>
    </div>
</section>
