<?php

declare(strict_types=1);

namespace App\Livewire\Central\Plans;

use App\Models\Central\Plan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class PlanList extends Component
{
    public function toggleActive(int $planId): void
    {
        $plan = Plan::findOrFail($planId);
        $plan->update(['is_active' => ! $plan->is_active]);

        session()->flash('status', "{$plan->name} — অবস্থা পরিবর্তন করা হয়েছে।");
    }

    /**
     * @return Collection<int, Plan>
     */
    private function plans(): Collection
    {
        return Plan::withCount('subscriptions')->orderBy('sort_order')->get();
    }

    public function render(): View
    {
        return view('livewire.central.plans.plan-list', ['plans' => $this->plans()]);
    }
}
