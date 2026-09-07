@props(['label', 'value'])

<div class="rounded-xl border border-gray-200 bg-white p-4">
    <p class="text-sm text-gray-600">{{ $label }}</p>
    <p class="mt-1 text-2xl font-semibold text-gray-900">{{ \App\Support\Bn::num($value) }}</p>
</div>
