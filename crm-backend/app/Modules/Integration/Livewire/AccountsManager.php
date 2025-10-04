<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Services\UnipileService;
use App\Shared\Enums\ContactSource;
use Livewire\Component;

class AccountsManager extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $accounts = [];

    public bool $isLoading = false;

    public function mount(): void
    {
        $this->loadAccounts();
    }

    public function loadAccounts(): void
    {
        $this->isLoading = true;

        $this->accounts = IntegratedAccount::byUser(auth()->id())
            ->with(['user'])
            ->get()
            ->map(function (IntegratedAccount $account): array {
                return [
                    'id' => $account->id,
                    'unipile_account_id' => $account->unipile_account_id,
                    'provider' => $account->provider,
                    'provider_label' => $account->provider_label,
                    'provider_icon' => $account->provider_icon,
                    'provider_css_class' => $account->provider_css_class,
                    'display_name' => $account->display_name,
                    'status' => $account->status,
                    'sync_enabled' => $account->sync_enabled,
                    'last_sync_at' => $account->last_sync_at?->format('M j, Y g:i A'),
                    'needs_resync' => $account->needsResync(),
                ];
            })
            ->toArray();

        $this->isLoading = false;
    }

    public function toggleSync(int $accountId): void
    {
        $account = IntegratedAccount::find($accountId);

        if ($account && $account->user_id === auth()->id()) {
            $account->update([
                'sync_enabled' => ! $account->sync_enabled,
            ]);

            $this->loadAccounts();

            $message = $account->sync_enabled ? 'Sync enabled' : 'Sync disabled';
            session()->flash('success', $message);
        }
    }

    public function resyncAccount(int $accountId): void
    {
        $account = IntegratedAccount::find($accountId);

        if ($account && $account->user_id === auth()->id()) {
            $unipileService = app(UnipileService::class);

            if ($unipileService->resyncAccount($account->unipile_account_id)) {
                $account->update([
                    'last_sync_at' => now(),
                ]);

                $this->loadAccounts();
                session()->flash('success', 'Account resync initiated');
            } else {
                session()->flash('error', 'Failed to resync account');
            }
        }
    }

    public function deleteAccount(int $accountId): void
    {
        $account = IntegratedAccount::find($accountId);

        if ($account && $account->user_id === auth()->id()) {
            $unipileService = app(UnipileService::class);
            $unipileService->deleteAccount($account->unipile_account_id);

            $account->delete();

            $this->loadAccounts();
            session()->flash('success', 'Account disconnected successfully');
        }
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableProvidersProperty(): array
    {
        return [
            ContactSource::TELEGRAM->value => ContactSource::TELEGRAM->getLabel(),
            ContactSource::WHATSAPP->value => ContactSource::WHATSAPP->getLabel(),
            ContactSource::GOOGLE_OAUTH->value => ContactSource::GOOGLE_OAUTH->getLabel(),
        ];
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        return view('integration::accounts-manager', [
            'availableProviders' => $this->availableProviders,
        ]);
    }
}
