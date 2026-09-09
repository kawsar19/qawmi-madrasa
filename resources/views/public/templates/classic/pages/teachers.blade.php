@php($pageTitle = 'শিক্ষকমণ্ডলী')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <x-site.page-header title="শিক্ষকমণ্ডলী" subtitle="অভিজ্ঞ ও যোগ্য উস্তাদগণের তত্ত্বাবধানে পাঠদান" />

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-6xl px-4">
            @if ($teachers->isEmpty())
                <x-site.empty message="শিক্ষকদের তথ্য এখনো যোগ করা হয়নি।" />
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($teachers as $teacher)
                        <x-site.teacher-card :teacher="$teacher" :labels="$designationLabels" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
</x-dynamic-component>
