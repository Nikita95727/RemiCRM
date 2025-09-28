<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Modules\Integration\Contracts\IntegratedAccountRepositoryInterface;
use App\Modules\Integration\Contracts\IntegrationServiceInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IntegrationSuccessController extends Controller
{
    public function __construct(
        private IntegrationServiceInterface $integrationService,
        private IntegratedAccountRepositoryInterface $accountRepository
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Authentication required.');
        }

        try {
            $result = $this->integrationService->checkIntegrationStatus($user);
            $recentAccounts = $this->accountRepository->findRecentByUser($user, 5);

            if ($result['status'] === 'success') {
                return view('integration-success', [
                    'accounts' => $recentAccounts,
                    'message' => $recentAccounts->count() > 0
                        ? 'Account connected successfully! Contacts are being synchronized in the background.'
                        : 'Connection completed successfully!',
                ]);
            } else {
                return view('integration-success', [
                    'accounts' => $recentAccounts,
                    'message' => 'Connection in progress. Please wait...',
                ]);
            }

        } catch (\Exception $e) {
            return redirect()->route('contacts')
                ->with('error', 'Connection completed, but there was an issue with synchronization. Please try again.');
        }
    }
}
