<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;

/**
 * spatie/permission teams mode-কে চলতি tenant-এ সেট করে।
 *
 * Must run AFTER tenancy is initialized. Without this, role/permission checks
 * resolve against the wrong team and a user could inherit another madrasa's
 * roles.
 */
class SetPermissionsTeam
{
    public function handle(Request $request, Closure $next): Response
    {
        if (tenancy()->initialized) {
            app(PermissionRegistrar::class)->setPermissionsTeamId(tenant()->getTenantKey());
        }

        return $next($request);
    }
}
