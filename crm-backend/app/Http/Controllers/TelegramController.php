<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ConnectProviderRequest;
use App\Modules\Integration\Contracts\IntegrationServiceInterface;
use App\Modules\Integration\DTOs\CreateHostedAuthLinkDTO;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TelegramController extends Controller
{
    public function __construct(
        private IntegrationServiceInterface $integrationService
    ) {}

    public function connect(ConnectProviderRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return redirect()->route('login')
                    ->with('error', 'Authentication required.');
            }

            $dto = new CreateHostedAuthLinkDTO(
                provider: 'telegram',
                userId: $user->id,
                redirectUrl: route('integration.waiting')
            );

            $response = $this->integrationService->createHostedAuthLink($dto->provider, $user);

            if (isset($response['url'])) {
                session([
                    'pending_integration' => [
                        'user_id' => $user->id,
                        'provider' => 'telegram',
                        'started_at' => now()->toDateTimeString(),
                    ],
                ]);

                return redirect()->away($response['url']);
            } else {
                return redirect()->route('telegram.connect.form')
                    ->with('error', 'Failed to create authentication link. Please try again.');
            }
        } catch (\Exception $e) {
            return redirect()->route('telegram.connect.form')
                ->with('error', 'Connection failed: '.$e->getMessage());
        }
    }
}
