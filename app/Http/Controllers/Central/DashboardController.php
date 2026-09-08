<?php

declare(strict_types=1);

namespace App\Http\Controllers\Central;

use App\Models\Central\Subscription;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController
{
    public function __invoke(): View
    {
        return view('central.dashboard', [
            'stats' => [
                'tenants' => Tenant::count(),
                'active' => Tenant::where('status', Tenant::STATUS_ACTIVE)->count(),
                'suspended' => Tenant::where('status', Tenant::STATUS_SUSPENDED)->count(),
                'users' => User::whereNotNull('tenant_id')->count(),
            ],
            'expiringSoon' => Subscription::with(['tenant', 'plan'])
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereBetween('ends_at', [now()->toDateString(), now()->addDays(30)->toDateString()])
                ->orderBy('ends_at')
                ->limit(10)
                ->get(),
        ]);
    }
}
