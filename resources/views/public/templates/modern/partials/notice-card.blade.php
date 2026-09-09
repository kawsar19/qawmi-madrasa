@props(['notice'])

<a href="{{ route('public.notice', $notice->id) }}"
   class="group flex flex-col rounded-2xl border border-stone-200 bg-white p-5 transition-shadow duration-150 hover:shadow-md">
    <div class="flex flex-wrap items-center gap-2">
        @if ($notice->is_pinned)
            <span class="rounded-full px-2.5 py-0.5 text-[11px] font-medium text-white"
                  style="background: var(--site-accent)">
                গুরুত্বপূর্ণ
            </span>
        @endif
        <span class="rounded-full bg-stone-100 px-2.5 py-0.5 text-[11px] text-stone-600">
            {{ \App\Models\Cms\Notice::categories()[$notice->category] ?? 'সাধারণ' }}
        </span>
        @if ($notice->published_on)
            <span class="ml-auto text-xs tabular-nums text-stone-400">
                @bn($notice->published_on->format('d/m/Y'))
            </span>
        @endif
    </div>

    <h3 class="mt-3 font-semibold text-pretty text-stone-900">{{ $notice->title }}</h3>

    @if ($notice->body)
        <p class="mt-2 line-clamp-3 text-sm text-pretty text-stone-600">{{ $notice->body }}</p>
    @endif

    <span class="mt-4 inline-flex items-center gap-1 text-sm font-medium transition-transform duration-150 group-hover:translate-x-0.5"
          style="color: var(--site-brand)">
        বিস্তারিত
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2"
             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
            <path d="m9 18 6-6-6-6"/>
        </svg>
    </span>
</a>
