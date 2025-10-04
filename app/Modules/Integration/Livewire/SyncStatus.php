<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use App\Modules\Integration\Models\IntegratedAccount;
use Livewire\Component;

class SyncStatus extends Component
{
    /** @var array<string, mixed> */
    public array $stats = [];

    public bool $isLoading = false;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function loadStats(): void
    {
        $this->isLoading = true;

        $accounts = IntegratedAccount::byUser(auth()->id())->get();

        $this->stats = [
            'total_accounts' => $accounts->count(),
            'active_accounts' => $accounts->where('status', 'active')->count(),
            'syncing_accounts' => $accounts->where('sync_enabled', true)->count(),
            'last_sync' => $accounts->where('last_sync_at', '!=', null)
                ->sortByDesc('last_sync_at')
                ->first()?->last_sync_at?->format('M j, Y g:i A') ?? 'Never',
            'accounts_needing_sync' => $accounts->filter(function ($account) {
                return $account->needsResync();
            })->count(),
        ];

        $this->isLoading = false;
    }

    public function refreshStats(): void
    {
        $this->loadStats();
        $this->dispatch('refreshAccounts');
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('integration::sync-status');
    }
}
