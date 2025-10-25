<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use App\Modules\Integration\Models\IntegratedAccount;
use Livewire\Component;

class IntegrationErrors extends Component
{
    public $errorAccounts = [];
    
    protected $listeners = [
        'refreshErrors' => '$refresh',
        'accountConnected' => '$refresh',
        'contactsSynced' => '$refresh',
    ];

    public function mount(): void
    {
        $this->loadErrorAccounts();
    }

    public function loadErrorAccounts(): void
    {
        $this->errorAccounts = IntegratedAccount::where('user_id', auth()->id())
            ->where('status', 'error')
            ->whereNotNull('error_message')
            ->orderBy('last_error_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'provider' => $account->provider->getLabel(),
                    'account_name' => $account->account_name,
                    'error_message' => $account->error_message,
                    'last_error_at' => $account->last_error_at?->diffForHumans(),
                    'can_retry' => $this->canRetryAccount($account),
                ];
            })
            ->toArray();
    }

    public function retryAccount(int $accountId): void
    {
        $account = IntegratedAccount::where('user_id', auth()->id())
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            session()->flash('error', 'Account not found.');
            return;
        }

        try {
            // Reset error state
            $account->update([
                'status' => 'active',
                'error_message' => null,
                'last_error_at' => null,
            ]);

            // Dispatch sync job
            // \App\Modules\Integration\Jobs\SyncContactsFromAccount::dispatch($account); // OLD: Queued version
            
            // NEW: IMMEDIATE sync for testing
            \Log::info('IntegrationErrors: Running IMMEDIATE sync (not queued)', [
                'account_id' => $account->id,
                'provider' => $account->provider,
            ]);
            
            $job = new \App\Modules\Integration\Jobs\SyncContactsFromAccount($account);
            $job->handle(
                app(\App\Modules\Integration\Services\UnipileService::class),
                app(\App\Modules\Contact\Contracts\ContactRepositoryInterface::class)
            );
            
            \Log::info('IntegrationErrors: IMMEDIATE sync completed', [
                'account_id' => $account->id,
                'provider' => $account->provider,
            ]);

            session()->flash('success', "Retry synchronization for {$account->provider->getLabel()} started.");
            $this->loadErrorAccounts();
            
        } catch (\Exception $e) {
            \Log::error('Failed to retry account sync', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            
            session()->flash('error', 'Failed to start retry synchronization. Please try again later.');
        }
    }

    public function dismissError(int $accountId): void
    {
        $account = IntegratedAccount::where('user_id', auth()->id())
            ->where('id', $accountId)
            ->first();

        if (!$account) {
            return;
        }

        $account->update([
            'error_message' => null,
            'last_error_at' => null,
        ]);

        $this->loadErrorAccounts();
        session()->flash('info', 'Error dismissed.');
    }

    private function canRetryAccount(IntegratedAccount $account): bool
    {
        // Don't allow retry if last error was less than 5 minutes ago
        if ($account->last_error_at && $account->last_error_at->diffInMinutes() < 5) {
            return false;
        }

        // Don't allow retry for certain error types (like invalid credentials)
        $errorMessage = strtolower($account->error_message ?? '');
        $nonRetryableErrors = [
            'invalid credentials',
            'account blocked',
            'access denied',
            'api key',
        ];

        foreach ($nonRetryableErrors as $errorPattern) {
            if (str_contains($errorMessage, $errorPattern)) {
                return false;
            }
        }

        return true;
    }

    public function render()
    {
        return view('livewire.integration.integration-errors');
    }
}
