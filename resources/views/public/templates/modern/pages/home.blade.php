<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu">

    {{-- স্লাইড দিলে স্লাইডার হিরোর জায়গা নেয়; না দিলে স্থির হিরো। --}}
    @if ($slides->isNotEmpty())
        @include('public.templates.modern.partials.slider')
    @else
        @include('public.templates.modern.partials.hero')
    @endif
    @include('public.templates.modern.partials.about-short')

    @if ($settings->show_notices && $notices->isNotEmpty())
        @include('public.templates.modern.partials.notice-strip')
    @endif

    @include('public.templates.modern.partials.departments')

    @if ($settings->show_teachers && $teachers->isNotEmpty())
        @include('public.templates.modern.partials.teacher-strip')
    @endif

    @if ($settings->show_gallery && $gallery->isNotEmpty())
        @include('public.templates.modern.partials.gallery-strip')
    @endif

    @include('public.templates.modern.partials.admission-cta')
</x-dynamic-component>
