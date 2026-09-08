<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Symfony\Component\HttpFoundation\Response;

/**
 * Livewire-এর নিজস্ব রুটগুলোর জন্য tenancy — কিন্তু central ডোমেইনে নয়।
 *
 * Livewire registers ONE /livewire/update endpoint that both the tenant panel
 * and the super-admin panel post to. Wrapping it in
 * PreventAccessFromCentralDomains would 404 every super-admin interaction,
 * while omitting tenancy entirely would run tenant components with no tenant
 * context (the global scope silently becomes a no-op).
 *
 * So: initialize tenancy on a tenant host, and pass through untouched on a
 * central host.
 */
class InitializeTenancyIfTenantDomain
{
    public function __construct(private readonly InitializeTenancyByDomain $tenancy) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isCentralDomain($request)) {
            return $next($request);
        }

        return $this->tenancy->handle($request, $next);
    }

    private function isCentralDomain(Request $request): bool
    {
        return in_array(
            $request->getHost(),
            (array) config('tenancy.central_domains', []),
            true
        );
    }
}
