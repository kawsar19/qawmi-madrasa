@php($pageTitle = 'পরিচিতি')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <x-site.page-header title="আমাদের সম্পর্কে" :subtitle="$settings->tagline" />

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="grid gap-10 lg:grid-cols-3">
                <div class="lg:col-span-2">
                    @if ($settings->about_full || $settings->about_short)
                        <div class="space-y-4 leading-relaxed text-pretty text-stone-700">
                            @foreach (preg_split('/\n\s*\n/', (string) ($settings->about_full ?: $settings->about_short)) as $para)
                                @if (trim($para) !== '')
                                    <p>{{ trim($para) }}</p>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <x-site.empty message="পরিচিতি এখনো যোগ করা হয়নি।" />
                    @endif
                </div>

                @if ($settings->principal_message)
                    <aside class="h-fit rounded-lg border-l-4 border-[var(--site-accent)] bg-stone-50 p-6">
                        <p class="text-sm font-semibold text-stone-900">মুহতামিমের বাণী</p>
                        <blockquote class="mt-3 text-sm leading-relaxed text-pretty text-stone-700">
                            {{ $settings->principal_message }}
                        </blockquote>
                        @if ($settings->principal_name)
                            <p class="mt-4 border-t border-stone-200 pt-3 text-sm font-medium text-stone-900">
                                — {{ $settings->principal_name }}
                            </p>
                        @endif
                    </aside>
                @endif
            </div>
        </div>
    </section>

    @include('public.templates.classic.partials.departments')
</x-dynamic-component>
