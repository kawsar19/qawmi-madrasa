@php($pageTitle = 'নোটিশ')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <x-site.page-header title="নোটিশ ও এলান" subtitle="মাদরাসার সর্বশেষ ঘোষণা ও বিজ্ঞপ্তি" />

    <section class="bg-stone-100 py-12 sm:py-16">
        <div class="mx-auto max-w-4xl px-4">
            {{-- ক্যাটাগরি ফিল্টার --}}
            <div class="flex flex-wrap justify-center gap-2">
                <a href="{{ route('public.notices') }}"
                   @class([
                       'rounded-full px-4 py-2 text-sm transition-colors duration-150',
                       'bg-[var(--site-brand)] font-medium text-white' => $activeCategory === '',
                       'border border-stone-300 bg-white text-stone-700 hover:bg-stone-50' => $activeCategory !== '',
                   ])>
                    সব
                </a>

                @foreach ($categories as $value => $label)
                    <a href="{{ route('public.notices', ['category' => $value]) }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm transition-colors duration-150',
                           'bg-[var(--site-brand)] font-medium text-white' => $activeCategory === $value,
                           'border border-stone-300 bg-white text-stone-700 hover:bg-stone-50' => $activeCategory !== $value,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($notices->isEmpty())
                <div class="mt-8">
                    <x-site.empty message="এই বিভাগে এখনো কোনো নোটিশ নেই।" />
                </div>
            @else
                <div class="mt-8 divide-y divide-stone-200 overflow-hidden rounded-lg border border-stone-200 bg-white">
                    @foreach ($notices as $notice)
                        <x-site.notice-row :notice="$notice" />
                    @endforeach
                </div>

                <div class="mt-8">{{ $notices->links() }}</div>
            @endif
        </div>
    </section>
</x-dynamic-component>
