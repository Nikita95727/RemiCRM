<div class="p-6">
    @if (session()->has('status'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
            <p class="mt-1 text-sm text-gray-600">
                Add additional security to your account using two-factor authentication.
            </p>
        </div>

        @if (!auth()->user()->hasTwoFactorEnabled() && !$showingQrCode)
            <!-- Enable 2FA Button -->
            <div>
                <p class="text-sm text-gray-600 mb-4">
                    When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
                <button wire:click="enableTwoFactor" 
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Enable Two-Factor Authentication
                </button>
            </div>
        @endif

        @if ($showingQrCode)
            <!-- QR Code Section -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-600 mb-4">
                    Two-factor authentication is now enabled. Scan the following QR code using your phone's authenticator application.
                </p>

                <div class="bg-white p-4 inline-block rounded-lg border border-gray-200">
                    {!! $qrCode !!}
                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Setup Key: <code class="bg-gray-100 px-2 py-1 rounded text-xs">{{ $secret }}</code>
                    </p>
                </div>

                <!-- Confirmation Code Input -->
                <div class="mt-6">
                    <label for="confirmationCode" class="block text-sm font-medium text-gray-700 mb-2">
                        Enter the code from your authenticator app to confirm:
                    </label>
                    <div class="flex items-center space-x-2">
                        <input wire:model="confirmationCode" 
                               type="text" 
                               id="confirmationCode"
                               maxlength="6"
                               class="block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="000000">
                        <button wire:click="confirmTwoFactor"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Confirm
                        </button>
                    </div>
                    @error('confirmationCode')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif

        @if ($showingRecoveryCodes)
            <!-- Recovery Codes Section -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-600 mb-4">
                    Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                </p>

                <div class="bg-gray-100 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                        @foreach ($recoveryCodes as $code)
                            <div class="bg-white px-3 py-2 rounded">{{ $code }}</div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4">
                    <button wire:click="$set('showingRecoveryCodes', false)"
                            class="text-sm text-gray-600 underline hover:text-gray-900">
                        Done
                    </button>
                </div>
            </div>
        @endif

        @if (auth()->user()->hasTwoFactorEnabled())
            <!-- Manage 2FA Actions -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Two-Factor Authentication is Enabled</p>
                        <p class="text-sm text-gray-600">Your account is protected with two-factor authentication.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <button wire:click="showRecoveryCodes"
                                class="text-sm text-blue-600 underline hover:text-blue-900">
                            Show Recovery Codes
                        </button>
                        <button wire:click="regenerateRecoveryCodes"
                                class="text-sm text-blue-600 underline hover:text-blue-900">
                            Regenerate Codes
                        </button>
                        <button wire:click="disableTwoFactor"
                                class="text-sm text-red-600 underline hover:text-red-900">
                            Disable
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
