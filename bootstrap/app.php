<?php

use App\Http\Middleware\EnsureSubscriptionActive;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Middleware\EnsureUserBelongsToTenant;
use App\Http\Middleware\SetCurrentAcademicSession;
use App\Http\Middleware\SetPermissionsTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The `auth` middleware redirects guests to a route named "login".
        // Ours is "tenant.login" (tenant panel) / "central.login" (super
        // admin), so point it at the right one for the current host.
        $middleware->redirectGuestsTo(function (Request $request): string {
            return tenancy()->initialized
                ? route('tenant.login')
                : route('home');
        });

        $middleware->alias([
            'super-admin' => EnsureSuperAdmin::class,
            'tenant.subscribed' => EnsureSubscriptionActive::class,
        ]);

        // মাদরাসার পাবলিক ওয়েবসাইট — লগইন লাগে না।
        //
        // InitializeTenancyByDomain (NOT BySubdomain) because a tenant may be
        // reached by either a subdomain or a custom domain; domains.domain
        // stores the full hostname for both.
        $middleware->group('tenant.public', [
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ]);

        // লগইন পেজ — tenancy লাগে, auth লাগে না।
        $middleware->group('tenant.guest', [
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
        ]);

        // মাদরাসার প্যানেল। Order matters: tenancy must be initialized before
        // permissions team, subscription check and academic session resolve.
        $middleware->group('tenant.panel', [
            'web',
            InitializeTenancyByDomain::class,
            PreventAccessFromCentralDomains::class,
            'auth',
            EnsureUserBelongsToTenant::class,
            SetPermissionsTeam::class,
            EnsureSubscriptionActive::class,
            SetCurrentAcademicSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // অচেনা ডোমেইন = 404, 500 নয়। Someone pointing a stray DNS record at
        // us should get "not found", not a stack trace.
        $exceptions->render(function (TenantCouldNotBeIdentifiedOnDomainException $e) {
            abort(404);
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
