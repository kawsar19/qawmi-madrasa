@props(['teacher', 'labels' => []])

<div class="flex items-center gap-4 rounded-2xl border border-stone-200 bg-white p-4 transition-shadow duration-150 hover:shadow-md">
    <div class="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl bg-stone-100">
        @if ($teacher->photo_path)
            <img src="{{ asset('storage/'.$teacher->photo_path) }}" alt="" class="size-full object-cover">
        @else
            <span class="text-xl font-semibold" style="color: var(--site-brand)">
                {{ mb_substr($teacher->name, 0, 1) }}
            </span>
        @endif
    </div>

    <div class="min-w-0">
        <h3 class="truncate font-semibold text-stone-900">{{ $teacher->name }}</h3>
        @if ($teacher->designation)
            <p class="truncate text-sm" style="color: var(--site-brand)">
                {{ $labels[$teacher->designation] ?? '' }}
            </p>
        @endif
        @if ($teacher->qualification)
            <p class="truncate text-xs text-stone-500">{{ $teacher->qualification }}</p>
        @endif
    </div>
</div>
