<?php

declare(strict_types=1);

use App\Support\Media;

if (! function_exists('media')) {
    /**
     * আপলোড করা ফাইলের URL — লোকাল ডিস্ক বা R2, দুই ক্ষেত্রেই।
     *
     * ভিউতে `asset('storage/'.$path)` লেখার বদলে `media($path)`।
     *
     * @see Media
     */
    function media(?string $path): ?string
    {
        return Media::url($path);
    }
}
