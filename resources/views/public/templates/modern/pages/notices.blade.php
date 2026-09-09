@php($pageTitle = 'নোটিশ')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    @include('public.templates.modern.partials.page-header', [
        'title' => 'নোটিশ ও এলান',
        'subtitle' => 'মাদরাসার সর্বশেষ ঘোষণা ও বিজ্ঞপ্তি',
    ])

    <section class="bg-stone-50/60 py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4">
            {{-- ক্যাটাগরি ফিল্টার --}}
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('public.notices') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm transition-colors duration-150',
                       'font-medium text-white' => $activeCategory === '',
                       'border border-stone-300 bg-white text-stone-700 hover:bg-white' => $activeCategory !== '',
                   ])
                   @style(['background: var(--site-brand)' => $activeCategory === ''])>
                    সব
                </a>

                @foreach ($categories as $value => $label)
                    <a href="{{ route('public.notices', ['category' => $value]) }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm transition-colors duration-150',
                           'font-medium text-white' => $activeCategory === $value,
                           'border border-stone-300 bg-white text-stone-700 hover:bg-white' => $activeCategory !== $value,
                       ])
                       @style(['background: var(--site-brand)' => $activeCategory === $value])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($notices->isEmpty())
                <div class="mt-8">
                    <x-site.empty message="এই বিভাগে এখনো কোনো নোটিশ নেই।" />
                </div>
            @else
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($notices as $notice)
                        @include('public.templates.modern.partials.notice-card', ['notice' => $notice])
                    @endforeach
                </div>

                <div class="mt-8">{{ $notices->links() }}</div>
            @endif
        </div>
    </section>
</x-dynamic-component>
