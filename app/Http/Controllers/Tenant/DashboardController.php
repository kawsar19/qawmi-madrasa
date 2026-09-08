<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Models\Academic\Kitab;
use App\Models\Academic\Marhala;
use App\Models\People\Employee;
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
                'employees' => Employee::query()->active()->count(),
                'marhalas' => Marhala::count(),
                'kitabs' => Kitab::count(),
            ],
        ]);
    }
}
