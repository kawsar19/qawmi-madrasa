@props(['status'])

@php
    $labels = [
        'active' => 'সক্রিয়',
        'pending' => 'অপেক্ষমাণ',
        'suspended' => 'স্থগিত',
        'expired' => 'মেয়াদোত্তীর্ণ',
    ];
    $classes = [
        'active' => 'bg-green-50 text-green-700 ring-green-600/20',
        'pending' => 'bg-amber-50 text-amber-800 ring-amber-600/20',
        'suspended' => 'bg-red-50 text-red-700 ring-red-600/20',
        'expired' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
@endphp

<span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $classes[$status] ?? $classes['expired'] }}">
    {{ $labels[$status] ?? $status }}
</span>
