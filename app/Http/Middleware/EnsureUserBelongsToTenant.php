<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * লগইন-করা ইউজার সত্যিই এই মাদরাসার কিনা তা প্রতিটি রিকোয়েস্টে যাচাই করে।
 *
 * Defence in depth: session cookies are host-only (SESSION_DOMAIN=null), but if
 * that config ever regresses, a session from madrasa A must still not
 * authenticate on madrasa B's domain.
 */
class EnsureUserBelongsToTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if (! tenancy()->initialized) {
            return $next($request);
        }

        // সুপার অ্যাডমিন impersonation ছাড়া tenant প্যানেলে ঢুকবে না।
        if (! $user->belongsToTenant(tenant()->getTenantKey())) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'এই অ্যাকাউন্ট এই মাদরাসার নয়।');
        }

        return $next($request);
    }
}
