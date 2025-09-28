<?php

declare(strict_types=1);

namespace App\Modules\Integration\Livewire;

use App\Modules\Integration\Models\IntegratedAccount;
use App\Modules\Integration\Exceptions\UnipileApiException;
use App\Modules\Integration\Services\UnipileService;
use App\Shared\Enums\ContactSource;
use Livewire\Component;

class ConnectAccount extends Component
{
    public bool $showModal = false;

    /** @var array<string, string> */
    protected $listeners = [
        'openConnectModal' => 'openModal',
        'connect-telegram-direct' => 'connectTelegramDirect',
    ];

    public function openModal(): void
    {
        \Log::info('=== openModal called ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
        ]);
        $this->showModal = true;
        $this->selectedProvider = '';
    }

    public function closeModal(): void
    {
        \Log::info('=== closeModal called ===', [
            'timestamp' => now()->toDateTimeString(),
            'user_id' => auth()->id(),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5),
        ]);
        $this->showModal = false;
    }


    public function connectTelegram(): void
    {
        \Log::info('connectTelegram method started');

        try {
            // Check if Unipile credentials are configured
            $dsn = config('services.unipile.dsn');
            $token = config('services.unipile.token');

            \Log::info('Checking Unipile credentials', [
                'dsn_set' => ! empty($dsn),
                'token_set' => ! empty($token),
            ]);

            if (! $dsn || ! $token) {
                \Log::warning('Unipile credentials not configured');
                session()->flash('error', 'Unipile API credentials are not configured. Please contact administrator.');
                $this->closeModal();

                return;
            }

            \Log::info('Unipile credentials are configured, proceeding...');

            $unipileService = app(UnipileService::class);

            $redirectUrl = route('integration.waiting');
            $userId = (string) auth()->id();

            \Log::info('Creating Unipile Hosted Auth link', [
                'user_id' => $userId,
                'redirect_url' => $redirectUrl,
            ]);

            $response = $unipileService->createHostedAuthLink(
                ['TELEGRAM'],
                $userId,
                null, // No webhook notification needed
                $redirectUrl
            );

            \Log::info('Unipile response', ['response' => $response]);

            if (isset($response['url'])) {
                // Save pending integration in session
                session([
                    'pending_integration' => [
                        'user_id' => auth()->id(),
                        'provider' => 'telegram',
                        'started_at' => now()->toDateTimeString(),
                    ],
                ]);

                // Show message and redirect immediately
                session()->flash('info', 'Redirecting to Telegram authentication...');
                $this->closeModal();

                \Log::info('Redirecting to Unipile URL', ['url' => $response['url']]);

                // Use JavaScript redirect for better UX
                $this->dispatch('redirect-external', url: $response['url']);

                return;
            } else {
                \Log::warning('No URL in Unipile response', ['response' => $response]);
                session()->flash('error', 'Failed to create authentication link. Please try again.');
            }
        } catch (UnipileApiException $e) {
            \Log::error('Telegram connection API error', $e->getLogContext());
            session()->flash('error', $e->getUserMessage());
        } catch (\Exception $e) {
            \Log::error('Unexpected Telegram connection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'An unexpected error occurred while connecting Telegram. Please try again later or contact administrator.');
        }

        $this->closeModal();
    }

    public function connectWhatsApp(): void
    {
        \Log::info('connectWhatsApp method started');
        
        try {
            // Check if Unipile credentials are configured
            $dsn = config('services.unipile.dsn');
            $token = config('services.unipile.token');
            
            \Log::info('Checking Unipile credentials for WhatsApp', [
                'dsn_set' => !empty($dsn),
                'token_set' => !empty($token)
            ]);
            
            if (!$dsn || !$token) {
                \Log::warning('Unipile credentials not configured for WhatsApp');
                session()->flash('error', 'Unipile API credentials are not configured. Please contact administrator.');
                $this->closeModal();
                return;
            }
            
            \Log::info('Unipile credentials are configured, proceeding with WhatsApp...');

            $unipileService = app(UnipileService::class);
            
            $redirectUrl = route('integration.waiting');
            $userId = (string) auth()->id();
            
            \Log::info('Creating Unipile Hosted Auth link for WhatsApp', [
                'user_id' => $userId,
                'redirect_url' => $redirectUrl
            ]);
            
            $response = $unipileService->createHostedAuthLink(
                ['WHATSAPP'], 
                $userId, 
                null, // No webhook notification needed
                $redirectUrl
            );
            
            \Log::info('Unipile WhatsApp response', ['response' => $response]);
            
            if (isset($response['url'])) {
                // Save pending integration in session
                session([
                    'pending_integration' => [
                        'user_id' => auth()->id(),
                        'provider' => 'whatsapp',
                        'started_at' => now()->toDateTimeString()
                    ]
                ]);
                
                // Show message and redirect immediately
                session()->flash('info', 'Redirecting to WhatsApp authentication...');
                $this->closeModal();
                
                \Log::info('Redirecting to Unipile WhatsApp URL', ['url' => $response['url']]);
                
                // Use JavaScript redirect for better UX
                $this->dispatch('redirect-external', url: $response['url']);
                return;
            } else {
                \Log::warning('No URL in Unipile WhatsApp response', ['response' => $response]);
                session()->flash('error', 'Failed to create WhatsApp authentication link. Please try again.');
            }
        } catch (UnipileApiException $e) {
            \Log::error('WhatsApp connection API error', $e->getLogContext());
            session()->flash('error', $e->getUserMessage());
        } catch (\Exception $e) {
            \Log::error('Unexpected WhatsApp connection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'An unexpected error occurred while connecting WhatsApp. Please try again later or contact administrator.');
        }
        
        $this->closeModal();
    }

    public function connectGmail(): void
    {
        \Log::info('connectGmail method started');
        
        try {
            // Check if Unipile credentials are configured
            $dsn = config('services.unipile.dsn');
            $token = config('services.unipile.token');
            
            \Log::info('Checking Unipile credentials for Gmail', [
                'dsn_set' => !empty($dsn),
                'token_set' => !empty($token)
            ]);
            
            if (!$dsn || !$token) {
                \Log::warning('Unipile credentials not configured for Gmail');
                session()->flash('error', 'Unipile API credentials are not configured. Please contact administrator.');
                $this->closeModal();
                return;
            }
            
            \Log::info('Unipile credentials are configured, proceeding with Gmail...');

            $unipileService = app(UnipileService::class);
            
            $redirectUrl = route('integration.waiting');
            $userId = (string) auth()->id();
            
            \Log::info('Creating Unipile Hosted Auth link for Gmail', [
                'user_id' => $userId,
                'redirect_url' => $redirectUrl
            ]);
            
            $response = $unipileService->createHostedAuthLink(
                ['GMAIL'], 
                $userId, 
                null, // No webhook notification needed
                $redirectUrl
            );
            
            \Log::info('Unipile Gmail response', ['response' => $response]);
            
            if (isset($response['url'])) {
                // Save pending integration in session
                session([
                    'pending_integration' => [
                        'user_id' => auth()->id(),
                        'provider' => 'gmail',
                        'started_at' => now()->toDateTimeString()
                    ]
                ]);
                
                // Show message and redirect immediately
                session()->flash('info', 'Redirecting to Gmail authentication...');
                $this->closeModal();
                
                \Log::info('Redirecting to Unipile Gmail URL', ['url' => $response['url']]);
                
                // Use JavaScript redirect for better UX
                $this->dispatch('redirect-external', url: $response['url']);
                return;
            } else {
                \Log::warning('No URL in Unipile Gmail response', ['response' => $response]);
                session()->flash('error', 'Failed to create Gmail authentication link. Please try again.');
            }
        } catch (UnipileApiException $e) {
            \Log::error('Gmail connection API error', $e->getLogContext());
            session()->flash('error', $e->getUserMessage());
        } catch (\Exception $e) {
            \Log::error('Unexpected Gmail connection error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'An unexpected error occurred while connecting Gmail. Please try again later or contact administrator.');
        }
        
        $this->closeModal();
    }

    public function connectTelegramDirect(): void
    {
        \Log::info('connectTelegramDirect method called - bypassing modal');

        // Set provider and connect directly
        $this->selectedProvider = 'telegram';
        $this->connectTelegram();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function getAvailableProvidersProperty(): array
    {
        return [
            'telegram' => [
                'name' => 'Telegram',
                'description' => 'Connect your Telegram account to sync contacts and messages',
                'icon' => ContactSource::TELEGRAM->getIcon(),
                'color' => 'from-blue-500 to-cyan-600',
                'available' => true, // Hosted Auth Wizard implemented
            ],
            'whatsapp' => [
                'name' => 'WhatsApp',
                'description' => 'Connect your WhatsApp account to sync contacts and messages',
                'icon' => ContactSource::WHATSAPP->getIcon(),
                'color' => 'from-green-500 to-emerald-600',
                'available' => true, // Hosted Auth Wizard implemented
            ],
            'gmail' => [
                'name' => 'Gmail',
                'description' => 'Connect your Gmail account to sync email contacts',
                'icon' => ContactSource::GMAIL->getIcon(),
                'color' => 'from-red-500 to-pink-600',
                'available' => true, // Hosted Auth Wizard implemented
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConnectedAccountsProperty(): array
    {
        $user = auth()->user();
        if (! $user) {
            return [];
        }

        return IntegratedAccount::where('user_id', $user->id)
            ->where('status', 'active')
            ->get()
            ->groupBy('provider')
            ->map(function ($accounts, $provider) {
                return $accounts->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'name' => $account->account_name,
                        'provider' => $account->provider,
                        'created_at' => $account->created_at->format('M j, Y'),
                    ];
                })->toArray();
            })
            ->toArray();
    }

    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function render()
    {
        $providers = $this->getAvailableProvidersProperty();
        $connectedAccounts = $this->getConnectedAccountsProperty();

        \Log::info('ConnectAccount render', [
            'showModal' => $this->showModal,
            'providers' => array_keys($providers),
            'connectedAccounts' => $connectedAccounts,
        ]);

        return view('integration::connect-account', [
            'providers' => $providers,
            'connectedAccounts' => $connectedAccounts,
        ]);
    }
}
