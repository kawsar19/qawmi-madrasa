<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\People\Student;
use App\Services\Academic\CurrentSession;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(CurrentSession $currentSession): View
    {
        return view('tenant.dashboard', [
            'currentSession' => $currentSession->get(),
            'stats' => [
                'students' => Student::query()->active()->count(),
                // Employees arrive later in Phase 1; the count is wired here.
                'employees' => 0,
                'marhalas' => Marhala::count(),
                'kitabs' => Kitab::count(),
            ],
        ]);
    }
}
