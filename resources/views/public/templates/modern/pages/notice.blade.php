@php($pageTitle = $notice->title)

<x-dynamic-component :component="\App\Support\SiteTemplate::layout($settings->template)"
                     :settings="$settings" :menu="$menu" :page-title="$pageTitle">

    <section class="border-b border-stone-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-12 sm:py-16">
            <a href="{{ route('public.notices') }}"
               class="inline-flex items-center gap-1.5 text-sm text-stone-500 transition-colors hover:text-stone-900">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
                     stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                সব নোটিশ
            </a>

            <div class="mt-5 flex flex-wrap items-center gap-2">
                @if ($notice->is_pinned)
                    <span class="rounded-full px-2.5 py-1 text-xs font-medium text-white"
                          style="background: var(--site-accent)">
                        গুরুত্বপূর্ণ
                    </span>
                @endif
                <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs text-stone-600">
                    {{ \App\Models\Cms\Notice::categories()[$notice->category] ?? 'সাধারণ' }}
                </span>
                @if ($notice->published_on)
                    <span class="text-sm tabular-nums text-stone-400">
                        @bn($notice->published_on->format('d/m/Y'))
                    </span>
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-bold leading-tight text-balance text-stone-900 sm:text-4xl">
                {{ $notice->title }}
            </h1>
        </div>
    </section>

    <section class="bg-white py-10">
        <div class="mx-auto max-w-3xl px-4">
            @if ($notice->body)
                <div class="space-y-4 leading-relaxed text-pretty text-stone-700">
                    @foreach (preg_split('/\n\s*\n/', $notice->body) as $para)
                        @if (trim($para) !== '')
                            <p>{{ trim($para) }}</p>
                        @endif
                    @endforeach
                </div>
            @endif

            @if ($notice->attachment_path)
                <a href="{{ asset('storage/'.$notice->attachment_path) }}" target="_blank" rel="noopener"
                   class="mt-8 inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-medium text-white transition-opacity duration-150 hover:opacity-90"
                   style="background: var(--site-brand)">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 3v12m0 0-4-4m4 4 4-4M4 17v2a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-2"/>
                    </svg>
                    সংযুক্তি ডাউনলোড
                </a>
            @endif
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="border-t border-stone-200 bg-stone-50/60 py-12 sm:py-16">
            <div class="mx-auto max-w-6xl px-4">
                <h2 class="font-semibold text-stone-900">অন্যান্য নোটিশ</h2>

                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related->take(3) as $item)
                        @include('public.templates.modern.partials.notice-card', ['notice' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-dynamic-component>
