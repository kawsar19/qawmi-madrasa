{{-- হালকা ফুটার — গাঢ় জমিনের বদলে সাদা, উপরে পাতলা বর্ডার। --}}
<footer class="border-t border-stone-200 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr]">
            <div>
                <div class="flex items-center gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl text-sm font-bold text-white"
                          style="background: var(--site-brand)">
                        {{ mb_substr($settings->displayTitle(), 0, 1) }}
                    </span>
                    <span class="font-bold text-balance text-stone-900">{{ $settings->displayTitle() }}</span>
                </div>

                @if ($settings->about_short)
                    <p class="mt-4 max-w-sm text-sm leading-relaxed text-pretty text-stone-600">
                        {{ Str::limit(strip_tags($settings->about_short), 160) }}
                    </p>
                @endif
            </div>

            <div>
                <p class="text-sm font-semibold text-stone-900">পাতা</p>
                <ul class="mt-3 space-y-2 text-sm">
                    @foreach ($menu as $item)
                        <li>
                            <a href="{{ route($item['route']) }}"
                               class="text-stone-600 transition-colors hover:text-stone-900">
                                {{ $item['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-sm font-semibold text-stone-900">যোগাযোগ</p>
                <ul class="mt-3 space-y-2 text-sm text-stone-600">
                    @if ($settings->address)
                        <li class="text-pretty">{{ $settings->address }}</li>
                    @endif
                    @if ($settings->phone)
                        <li>
                            <a href="tel:{{ $settings->phone }}" class="tabular-nums transition-colors hover:text-stone-900">
                                @bn($settings->phone)
                            </a>
                        </li>
                    @endif
                    @if ($settings->email)
                        <li>
                            <a href="mailto:{{ $settings->email }}" class="transition-colors hover:text-stone-900">
                                {{ $settings->email }}
                            </a>
                        </li>
                    @endif
                </ul>

                @if ($settings->facebook_url || $settings->youtube_url)
                    <div class="mt-4 flex gap-2">
                        @if ($settings->facebook_url)
                            <a href="{{ $settings->facebook_url }}" target="_blank" rel="noopener"
                               class="grid size-9 place-items-center rounded-lg border border-stone-200 text-stone-600 transition-colors hover:bg-stone-50"
                               aria-label="ফেসবুক">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h3l1-3h-4v-2c0-.6.4-1 1-1Z"/>
                                </svg>
                            </a>
                        @endif
                        @if ($settings->youtube_url)
                            <a href="{{ $settings->youtube_url }}" target="_blank" rel="noopener"
                               class="grid size-9 place-items-center rounded-lg border border-stone-200 text-stone-600 transition-colors hover:bg-stone-50"
                               aria-label="ইউটিউব">
                                <svg class="size-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M22 12s0-3.4-.4-5c-.3-.9-1-1.6-1.9-1.8C18 5 12 5 12 5s-6 0-7.7.4c-.9.2-1.6.9-1.9 1.8C2 8.6 2 12 2 12s0 3.4.4 5c.3.9 1 1.6 1.9 1.8C6 19 12 19 12 19s6 0 7.7-.4c.9-.2 1.6-.9 1.9-1.8.4-1.6.4-5 .4-5ZM10 15V9l5 3-5 3Z"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="mt-10 border-t border-stone-200 pt-6 text-center">
            <p class="text-xs text-stone-500">
                © @bn(now()->format('Y')) {{ $settings->displayTitle() }} — সর্বস্বত্ব সংরক্ষিত
            </p>
        </div>
    </div>
</footer>
