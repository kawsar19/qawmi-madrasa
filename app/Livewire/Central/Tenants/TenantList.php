<?php

declare(strict_types=1);

namespace App\Livewire\Central\Tenants;

use App\Models\Central\Tenant;
use App\Support\Search;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * সব মাদরাসার তালিকা — সুপার অ্যাডমিনের প্রধান স্ক্রিন।
 */
class TenantList extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function suspend(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

        session()->flash('status', "{$tenant->name} স্থগিত করা হয়েছে।");
    }

    public function activate(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['status' => Tenant::STATUS_ACTIVE]);

        session()->flash('status', "{$tenant->name} সক্রিয় করা হয়েছে।");
    }

    /**
     * @return LengthAwarePaginator<int, Tenant>
     */
    private function tenants(): LengthAwarePaginator
    {
        return Tenant::query()
            ->with(['domains', 'subscriptions' => fn ($q) => $q->latest('ends_at')->limit(1)])
            ->when($this->search !== '', function (Builder $query): void {
                Search::anyOf($query, ['name', 'slug', 'eiin'], Search::term($this->search));
            })
            ->when($this->status !== '', fn (Builder $q) => $q->where('status', $this->status))
            ->latest('id')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.central.tenants.tenant-list', [
            'tenants' => $this->tenants(),
        ]);
    }
}
