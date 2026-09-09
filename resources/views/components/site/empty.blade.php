{{-- খালি অবস্থার বার্তা — সব পেজে একরকম দেখাতে। --}}
@props(['message'])

<div class="rounded-lg border border-dashed border-stone-300 bg-white p-12 text-center">
    <p class="text-pretty text-stone-500">{{ $message }}</p>
</div>
