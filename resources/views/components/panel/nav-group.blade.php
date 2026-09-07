@props(['label'])

<div>
    <p class="px-3 pb-1 text-xs font-medium uppercase tracking-wide text-gray-400">{{ $label }}</p>
    <div class="space-y-0.5">
        {{ $slot }}
    </div>
</div>
