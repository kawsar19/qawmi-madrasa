@php($pageTitle = 'পরিচিতি')

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    @include('public.templates.modern.partials.page-header', [
        'title' => 'আমাদের সম্পর্কে',
        'subtitle' => $settings->tagline,
    ])

    <section class="bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4">
            <div class="grid gap-10 lg:grid-cols-[3fr_2fr]">
                <div>
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
                    <aside>
                        <div class="h-fit rounded-2xl border border-stone-200 bg-stone-50/60 p-6">
                            <p class="text-xs font-semibold uppercase tracking-wider"
                               style="color: var(--site-accent)">
                                মুহতামিমের বাণী
                            </p>

                            <blockquote class="mt-3 text-sm leading-relaxed text-pretty text-stone-700">
                                {{ $settings->principal_message }}
                            </blockquote>

                            @if ($settings->principal_name)
                                <p class="mt-5 border-t border-stone-200 pt-4 text-sm font-medium text-stone-900">
                                    — {{ $settings->principal_name }}
                                </p>
                            @endif
                        </div>
                    </aside>
                @endif
            </div>
        </div>
    </section>

    @include('public.templates.modern.partials.departments')
</x-dynamic-component>
