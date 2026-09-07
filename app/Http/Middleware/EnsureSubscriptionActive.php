<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * মেয়াদোত্তীর্ণ বা স্থগিত মাদরাসা প্যানেল ব্যবহার করতে পারবে না।
 */
class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant instanceof Tenant) {
            abort(404);
        }

        if ($tenant->status === Tenant::STATUS_SUSPENDED) {
            return response()->view('tenant.suspended', ['tenant' => $tenant], 403);
        }

        // Trial তে থাকলে সাবস্ক্রিপশন ছাড়াই চলবে।
        if ($tenant->trial_ends_at !== null && $tenant->trial_ends_at->isFuture()) {
            return $next($request);
        }

        $hasActive = Subscription::query()
            ->where('tenant_id', $tenant->getTenantKey())
            ->active()
            ->exists();

        if (! $hasActive) {
            return response()->view('tenant.expired', ['tenant' => $tenant], 403);
        }

        return $next($request);
    }
}
