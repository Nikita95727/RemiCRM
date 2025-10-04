<div>
    <div class="max-w-2xl mx-auto">
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Add additional security to your account using two-factor authentication.
                </p>
            </div>

            <div class="px-6 py-4">
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                        <p class="text-sm text-green-800">{{ session('status') }}</p>
                    </div>
                @endif

                @if (!$user->hasEnabledTwoFactorAuthentication())
                    <!-- Enable 2FA -->
                    <div class="space-y-6">
                        <div class="text-center">
                            <button 
                                wire:click="enableTwoFactorAuthentication"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Enable Two-Factor Authentication
                            </button>
                        </div>
                    </div>
                @elseif ($confirmingTwoFactorAuthentication)
                    <!-- Confirm 2FA -->
                    <div class="space-y-6">
                        <div class="text-center">
                            <div class="mb-4">
                                {!! $qrCodeSvg !!}
                            </div>
                            <p class="text-sm text-gray-600 mb-4">
                                Scan this QR code with your authenticator app, then enter the code below.
                            </p>
                            <p class="text-xs text-gray-500 mb-4">
                                Secret Key: <code class="bg-gray-100 px-2 py-1 rounded">{{ $secretKey }}</code>
                            </p>
                        </div>

                        <form wire:submit="confirmTwoFactorAuthentication">
                            <div>
                                <x-input-label for="code" :value="__('Code')" />
                                <x-text-input 
                                    wire:model="code" 
                                    id="code" 
                                    class="block mt-1 w-full" 
                                    type="text" 
                                    name="code" 
                                    required 
                                    autofocus 
                                    autocomplete="one-time-code"
                                    placeholder="000000"
                                    maxlength="6"
                                />
                                <x-input-error :messages="$errors->get('code')" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <x-primary-button>
                                    {{ __('Confirm') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                @elseif ($showRecoveryCodes)
                    <!-- Show Recovery Codes -->
                    <div class="space-y-6">
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">
                                        Store these recovery codes in a secure password manager
                                    </h3>
                                    <div class="mt-2 text-sm text-yellow-700">
                                        <p>These recovery codes can be used to access your account if you lose your authenticator device.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($recoveryCodes as $code)
                                <div class="bg-gray-100 p-2 rounded text-center font-mono text-sm">
                                    {{ $code }}
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center">
                            <button 
                                wire:click="$set('showRecoveryCodes', false)"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                I have saved my recovery codes
                            </button>
                        </div>
                    </div>
                @else
                    <!-- 2FA Enabled -->
                    <div class="space-y-6">
                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        Two-factor authentication is enabled
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p>Your account is now protected with two-factor authentication.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center">
                            <button 
                                wire:click="disableTwoFactorAuthentication"
                                wire:confirm="Are you sure you want to disable two-factor authentication? This will make your account less secure."
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Disable Two-Factor Authentication
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
