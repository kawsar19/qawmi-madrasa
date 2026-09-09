{{-- একটি নোটিশের সারি — হোমপেজ ও নোটিশ তালিকা দুই জায়গায় ব্যবহৃত। --}}
@props(['notice'])

<a href="{{ route('public.notice', $notice->id) }}"
   class="flex gap-4 p-5 transition-colors duration-150 hover:bg-stone-50">
    <div class="grid size-14 shrink-0 place-content-center rounded border border-stone-200 bg-stone-50 text-center">
        <p class="text-lg font-bold tabular-nums leading-none text-[var(--site-brand)]">
            @bn($notice->published_on?->format('d') ?? '—')
        </p>
        <p class="mt-0.5 text-[10px] text-stone-500">
            @bn($notice->published_on?->format('m/y') ?? '')
        </p>
    </div>

    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            @if ($notice->is_pinned)
                <span class="rounded bg-[var(--site-accent)]/15 px-2 py-0.5 text-[11px] font-medium text-[var(--site-accent)]">
                    গুরুত্বপূর্ণ
                </span>
            @endif
            <span class="rounded bg-stone-100 px-2 py-0.5 text-[11px] text-stone-600">
                {{ \App\Models\Cms\Notice::categories()[$notice->category] ?? 'সাধারণ' }}
            </span>
        </div>

        <h3 class="mt-1.5 font-semibold text-pretty text-stone-900">{{ $notice->title }}</h3>

        @if ($notice->body)
            <p class="mt-1 line-clamp-2 text-sm text-pretty text-stone-600">{{ $notice->body }}</p>
        @endif
    </div>
</a>
