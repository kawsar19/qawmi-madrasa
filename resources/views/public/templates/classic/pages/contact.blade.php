@php($pageTitle = 'যোগাযোগ')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <x-site.page-header title="যোগাযোগ ও ভর্তি" subtitle="যেকোনো প্রয়োজনে আমাদের সাথে যোগাযোগ করুন" />

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4">
            @include('public.templates.classic.partials.contact-grid')
        </div>
    </section>
</x-dynamic-component>
