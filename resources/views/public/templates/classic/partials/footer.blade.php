<footer class="border-t-2 border-[var(--site-accent)] bg-[var(--site-brand)] text-white/80">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="text-center">
            <p class="text-lg font-bold text-white text-balance">{{ $settings->displayTitle() }}</p>

            @if ($settings->address)
                <p class="mx-auto mt-2 max-w-md text-sm text-pretty">{{ $settings->address }}</p>
            @endif

            <div class="mx-auto my-6 flex items-center justify-center gap-2" aria-hidden="true">
                <span class="h-px w-12 bg-white/20"></span>
                <span class="text-sm text-[var(--site-accent)]">❖</span>
                <span class="h-px w-12 bg-white/20"></span>
            </div>

            <p class="text-xs text-white/60">
                © @bn(now()->format('Y')) {{ $settings->displayTitle() }} — সর্বস্বত্ব সংরক্ষিত
            </p>
        </div>
    </div>
</footer>
