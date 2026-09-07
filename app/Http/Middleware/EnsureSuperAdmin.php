<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * শুধু সুপার অ্যাডমিন (tenant_id = null) central প্যানেলে ঢুকতে পারবে।
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_if($user === null || ! $user->isSuperAdmin(), 403);

        return $next($request);
    }
}
