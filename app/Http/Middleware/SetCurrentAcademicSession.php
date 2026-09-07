<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Academic\AcademicSession;
use App\Services\Academic\CurrentSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * চলতি শিক্ষাবর্ষ container singleton-এ বসায়।
 *
 * Kept in the container (not just session()) so queued jobs, console commands
 * and views all resolve the same session — see plan risk #2.
 */
class SetCurrentAcademicSession
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            return $next($request);
        }

        $current = app(CurrentSession::class);

        $selectedId = $request->session()->get('academic_session_id');

        $session = $selectedId !== null
            ? AcademicSession::find($selectedId)
            : null;

        $session ??= AcademicSession::query()->current()->first();

        if ($session !== null) {
            $current->set($session);
        }

        return $next($request);
    }
}
