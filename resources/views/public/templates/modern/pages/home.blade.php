<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu">

    @include('public.templates.modern.partials.hero')
    @include('public.templates.modern.partials.about-short')

    @if ($settings->show_notices && $notices->isNotEmpty())
        @include('public.templates.modern.partials.notice-strip')
    @endif

    @include('public.templates.modern.partials.departments')

    @if ($settings->show_teachers && $teachers->isNotEmpty())
        @include('public.templates.modern.partials.teacher-strip')
    @endif

    @include('public.templates.modern.partials.admission-cta')
</x-dynamic-component>
