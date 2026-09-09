{{-- শিক্ষকের কার্ড — হোমপেজ ও শিক্ষক পেজ দুই জায়গায়। --}}
@props(['teacher', 'labels' => []])

<div class="rounded-lg border border-stone-200 bg-white p-5 text-center">
    <div class="mx-auto grid size-20 place-items-center overflow-hidden rounded-full border-2 border-[var(--site-accent)]/30 bg-stone-100">
        @if ($teacher->photo_path)
            <img src="{{ media($teacher->photo_path) }}" alt="" class="size-full object-cover">
        @else
            <span class="text-2xl font-semibold text-[var(--site-brand)]">
                {{ mb_substr($teacher->name, 0, 1) }}
            </span>
        @endif
    </div>

    <h3 class="mt-4 font-semibold text-pretty text-stone-900">{{ $teacher->name }}</h3>

    @if ($teacher->designation)
        <p class="mt-1 text-sm text-[var(--site-brand)]">{{ $labels[$teacher->designation] ?? '' }}</p>
    @endif

    @if ($teacher->qualification)
        <p class="mt-1 text-xs text-pretty text-stone-500">{{ $teacher->qualification }}</p>
    @endif
</div>
