{{-- যোগাযোগ ও ভর্তি — যোগাযোগ পেজ ও হোমপেজ দুই জায়গায়। --}}
<div class="grid gap-4 lg:grid-cols-[3fr_2fr]">
    {{-- যোগাযোগের তথ্য --}}
    <div class="rounded-2xl border border-stone-200 bg-white p-6">
        <h3 class="font-semibold text-stone-900">যোগাযোগ</h3>

        <dl class="mt-5 space-y-5 text-sm">
            @if ($settings->address)
                <div class="flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl"
                          style="background: color-mix(in oklab, var(--site-brand) 10%, white); color: var(--site-brand)">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs text-stone-500">ঠিকানা</dt>
                        <dd class="mt-0.5 text-pretty text-stone-800">{{ $settings->address }}</dd>
                    </div>
                </div>
            @endif

            @if ($settings->phone)
                <div class="flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl"
                          style="background: color-mix(in oklab, var(--site-brand) 10%, white); color: var(--site-brand)">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.1a2 2 0 0 1 2.1-.5c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs text-stone-500">ফোন</dt>
                        <dd class="mt-0.5 tabular-nums text-stone-800">
                            <a href="tel:{{ $settings->phone }}" class="hover:underline">@bn($settings->phone)</a>
                            @if ($settings->phone_alt)
                                <span class="text-stone-300">·</span>
                                <a href="tel:{{ $settings->phone_alt }}" class="hover:underline">@bn($settings->phone_alt)</a>
                            @endif
                        </dd>
                    </div>
                </div>
            @endif

            @if ($settings->email)
                <div class="flex gap-3">
                    <span class="grid size-9 shrink-0 place-items-center rounded-xl"
                          style="background: color-mix(in oklab, var(--site-brand) 10%, white); color: var(--site-brand)">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.7"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M4 4h16a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Zm0 2 8 6 8-6"/>
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <dt class="text-xs text-stone-500">ইমেইল</dt>
                        <dd class="mt-0.5 truncate text-stone-800">
                            <a href="mailto:{{ $settings->email }}" class="hover:underline">{{ $settings->email }}</a>
                        </dd>
                    </div>
                </div>
            @endif
        </dl>
    </div>

    {{-- ভর্তির আহ্বান --}}
    <div class="relative overflow-hidden rounded-2xl p-6 text-white"
         style="background: var(--site-brand)">
        <div class="absolute inset-0 opacity-10" aria-hidden="true">
            <svg class="size-full"><rect width="100%" height="100%" fill="url(#dots)"/></svg>
        </div>

        <div class="relative">
            <h3 class="text-lg font-semibold text-balance">ভর্তি চলছে</h3>
            <p class="mt-2 text-sm text-pretty text-white/80">
                ভর্তির জন্য মাদরাসার কার্যালয়ে যোগাযোগ করুন। প্রয়োজনীয় কাগজপত্র
                সহ অভিভাবককে উপস্থিত থাকতে হবে।
            </p>

            <ul class="mt-5 space-y-2.5 text-sm text-white/85">
                @foreach (['জন্মনিবন্ধন সনদের কপি', 'পূর্ববর্তী মাদরাসার ছাড়পত্র', 'অভিভাবকের এনআইডি কপি', 'সদ্য তোলা ছবি ২ কপি'] as $item)
                    <li class="flex gap-2">
                        <svg class="mt-0.5 size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2"
                             stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="m5 13 4 4L19 7"/>
                        </svg>
                        <span class="text-pretty">{{ $item }}</span>
                    </li>
                @endforeach
            </ul>

            @if ($settings->phone)
                <a href="tel:{{ $settings->phone }}"
                   class="mt-6 inline-block rounded-xl bg-white/15 px-5 py-2.5 text-sm font-medium backdrop-blur-sm transition-colors duration-150 hover:bg-white/25">
                    ফোন করুন — @bn($settings->phone)
                </a>
            @endif
        </div>
    </div>
</div>
