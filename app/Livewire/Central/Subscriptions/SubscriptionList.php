<?php

declare(strict_types=1);

namespace App\Livewire\Central\Subscriptions;

use App\Models\Central\Subscription;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionList extends Component
{
    use WithPagination;

    /** expiring | expired | all */
    #[Url(except: 'all')]
    public string $filter = 'all';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<int, Subscription>
     */
    private function subscriptions(): LengthAwarePaginator
    {
        return Subscription::query()
            ->with(['tenant', 'plan'])
            ->when($this->filter === 'expiring', fn (Builder $q) => $q
                ->where('status', Subscription::STATUS_ACTIVE)
                ->whereBetween('ends_at', [now()->toDateString(), now()->addDays(30)->toDateString()]))
            ->when($this->filter === 'expired', fn (Builder $q) => $q
                ->whereDate('ends_at', '<', now()->toDateString()))
            ->orderBy('ends_at')
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.central.subscriptions.subscription-list', [
            'subscriptions' => $this->subscriptions(),
        ]);
    }
}
