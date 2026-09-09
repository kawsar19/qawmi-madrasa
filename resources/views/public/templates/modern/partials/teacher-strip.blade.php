<section class="border-t border-stone-200 bg-stone-50/60 py-16 sm:py-20">
    <div class="mx-auto max-w-6xl px-4">
        @include('public.templates.modern.partials.heading', [
            'label' => 'আমাদের উস্তাদগণ',
            'title' => 'শিক্ষকমণ্ডলী',
            'subtitle' => null,
            'link' => route('public.teachers'),
            'linkLabel' => 'সব শিক্ষক',
        ])

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            @foreach ($teachers as $teacher)
                @include('public.templates.modern.partials.teacher-card', [
                    'teacher' => $teacher,
                    'labels' => $designationLabels,
                ])
            @endforeach
        </div>
    </div>
</section>
