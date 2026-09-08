@props(['active' => false])

<a
    {{ $attributes }}
    @class([
        'block rounded-lg px-3 py-2 transition',
        'bg-gray-800 font-medium text-white' => $active,
        'text-gray-300 hover:bg-gray-800 hover:text-white' => ! $active,
    ])
>
    {{ $slot }}
</a>
