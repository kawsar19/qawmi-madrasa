@props(['active' => false])

<a
    {{ $attributes }}
    @class([
        'block rounded-lg px-3 py-2 transition',
        'bg-brand-50 font-medium text-brand-700' => $active,
        'text-gray-700 hover:bg-gray-100' => ! $active,
    ])
>
    {{ $slot }}
</a>
