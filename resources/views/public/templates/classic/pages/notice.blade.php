@php($pageTitle = $notice->title)

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <x-site.page-header :title="$notice->title" />

    <section class="bg-white py-12 sm:py-16">
        <div class="mx-auto max-w-3xl px-4">
            <div class="flex flex-wrap items-center gap-2 text-sm">
                @if ($notice->is_pinned)
                    <span class="rounded bg-[var(--site-accent)]/15 px-2.5 py-1 text-xs font-medium text-[var(--site-accent)]">
                        গুরুত্বপূর্ণ
                    </span>
                @endif
                <span class="rounded bg-stone-100 px-2.5 py-1 text-xs text-stone-600">
                    {{ $categories[$notice->category] ?? \App\Models\Cms\Notice::categories()[$notice->category] ?? 'সাধারণ' }}
                </span>
                @if ($notice->published_on)
                    <span class="tabular-nums text-stone-500">@bn($notice->published_on->format('d/m/Y'))</span>
                @endif
            </div>

            @if ($notice->body)
                <div class="mt-6 space-y-4 leading-relaxed text-pretty text-stone-700">
                    @foreach (preg_split('/\n\s*\n/', $notice->body) as $para)
                        @if (trim($para) !== '')
                            <p>{{ trim($para) }}</p>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($notice->attachment_path)
                <a href="{{ media($notice->attachment_path) }}" target="_blank" rel="noopener"
                   class="mt-8 inline-flex items-center gap-2 rounded border border-[var(--site-brand)] px-5 py-2.5 text-sm font-medium text-[var(--site-brand)] transition-colors duration-150 hover:bg-[var(--site-brand)] hover:text-white">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2"/>
                    </svg>
                    সংযুক্তি ডাউনলোড
                </a>
            @endif

            <div class="mt-10 border-t border-stone-200 pt-6">
                <a href="{{ route('public.notices') }}"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-[var(--site-brand)] hover:underline">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m15 18-6-6 6-6"/>
                    </svg>
                    সব নোটিশে ফিরে যান
                </a>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="bg-stone-100 py-12">
            <div class="mx-auto max-w-3xl px-4">
                <h2 class="font-semibold text-stone-900">অন্যান্য নোটিশ</h2>

                <div class="mt-4 divide-y divide-stone-200 overflow-hidden rounded-lg border border-stone-200 bg-white">
                    @foreach ($related as $item)
                        <x-site.notice-row :notice="$item" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-dynamic-component>
