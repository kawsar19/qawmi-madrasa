<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu">

    @include('public.templates.classic.partials.hero')
    @include('public.templates.classic.partials.about-short')

    @if ($settings->show_notices && $notices->isNotEmpty())
        @include('public.templates.classic.partials.notice-strip')
    @endif

    @include('public.templates.classic.partials.departments')

    @if ($settings->show_teachers && $teachers->isNotEmpty())
        @include('public.templates.classic.partials.teacher-strip')
    @endif

    @include('public.templates.classic.partials.admission-cta')
</x-dynamic-component>
