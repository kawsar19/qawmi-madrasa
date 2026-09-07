<?php

declare(strict_types=1);

namespace App\Livewire\Shared;

use App\Models\Academic\AcademicSession;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * পরীক্ষামূলক কম্পোনেন্ট — Livewire action-এ tenant context আছে কিনা যাচাই করে।
 *
 * Kept in the codebase (not just tests) because it is the canonical smoke test
 * for the Livewire/tenancy wiring in AppServiceProvider.
 */
class TenantProbe extends Component
{
    public string $tenantName = '';

    public int $sessionCount = 0;

    public function mount(): void
    {
        $this->refreshFromTenant();
    }

    /**
     * এই action-টি livewire/update রুটে চলে — সেখানে tenancy না থাকলে
     * global scope ভেঙে যাবে এবং ভুল ডেটা আসবে।
     */
    public function refreshFromTenant(): void
    {
        $this->tenantName = tenancy()->initialized ? (string) tenant('name') : '';
        $this->sessionCount = AcademicSession::count();
    }

    public function render(): View
    {
        return view('livewire.shared.tenant-probe');
    }
}
