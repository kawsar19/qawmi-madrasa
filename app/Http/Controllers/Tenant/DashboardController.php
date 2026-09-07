<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Services\Academic\CurrentSession;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(CurrentSession $currentSession): View
    {
        return view('tenant.dashboard', [
            'currentSession' => $currentSession->get(),
            'stats' => [
                // Student/employee tables arrive in Phase 1 block 3; the
                // counts they will fill are wired up here already.
                'students' => 0,
                'employees' => 0,
                'marhalas' => Marhala::count(),
                'kitabs' => Kitab::count(),
            ],
        ]);
    }
}
