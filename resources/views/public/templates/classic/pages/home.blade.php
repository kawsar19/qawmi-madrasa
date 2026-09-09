<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu">

    {{-- স্লাইড দিলে স্লাইডার, না দিলে স্থির হিরো — দুটোই সম্পূর্ণ। --}}
    @if ($slides->isNotEmpty())
        @include('public.templates.classic.partials.slider')
    @else
        @include('public.templates.classic.partials.hero')
    @endif
    @include('public.templates.classic.partials.about-short')

    @if ($settings->show_notices && $notices->isNotEmpty())
        @include('public.templates.classic.partials.notice-strip')
    @endif

    @include('public.templates.classic.partials.departments')

    @if ($settings->show_teachers && $teachers->isNotEmpty())
        @include('public.templates.classic.partials.teacher-strip')
    @endif

    @if ($settings->show_gallery && $gallery->isNotEmpty())
        @include('public.templates.classic.partials.gallery-strip')
    @endif

    @include('public.templates.classic.partials.admission-cta')
</x-dynamic-component>
