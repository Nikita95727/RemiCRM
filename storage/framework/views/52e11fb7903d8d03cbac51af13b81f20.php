<div class="p-6">
    <!--[if BLOCK]><![endif]--><?php if(session()->has('status')): ?>
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            <?php echo e(session('status')); ?>

        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <div class="space-y-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Two-Factor Authentication</h3>
            <p class="mt-1 text-sm text-gray-600">
                Add additional security to your account using two-factor authentication.
            </p>
        </div>

        <!--[if BLOCK]><![endif]--><?php if(auth()->user()->needsTwoFactorSetup()): ?>
            <!-- First Time Setup Required -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <strong>Action Required:</strong> You must set up two-factor authentication to continue using this account.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if(!auth()->user()->hasTwoFactorEnabled() && !auth()->user()->hasTwoFactorDisabled() && !$showingQrCode): ?>
            <!-- Enable 2FA Button -->
            <div>
                <p class="text-sm text-gray-600 mb-4">
                    When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You may retrieve this token from your phone's Google Authenticator application.
                </p>
                <button wire:click="enableTwoFactor" 
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <!--[if BLOCK]><![endif]--><?php if(auth()->user()->needsTwoFactorSetup()): ?>
                        Setup Two-Factor Authentication (Required)
                    <?php else: ?>
                        Enable Two-Factor Authentication
                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                </button>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <?php if(auth()->user()->hasTwoFactorDisabled() && !$showingQrCode): ?>
            <!-- 2FA Disabled - Show Re-enable Option -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-900">Two-Factor Authentication is Disabled</p>
                        <p class="text-sm text-gray-600">You have disabled two-factor authentication for your account.</p>
                    </div>
                    <button wire:click="enableTwoFactorAgain"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Re-enable Two-Factor Authentication
                    </button>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($showingQrCode): ?>
            <!-- QR Code Section -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-600 mb-4">
                    Two-factor authentication is now enabled. Scan the following QR code using your phone's authenticator application.
                </p>

                <div class="bg-white p-4 inline-block rounded-lg border border-gray-200">
                    <?php echo $qrCode; ?>

                </div>

                <div class="mt-4">
                    <p class="text-sm text-gray-600 mb-2">
                        Setup Key: <code class="bg-gray-100 px-2 py-1 rounded text-xs"><?php echo e($secret); ?></code>
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
                    <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['confirmationCode'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!--[if BLOCK]><![endif]--><?php if($showingRecoveryCodes): ?>
            <!-- Recovery Codes Section -->
            <div class="border-t border-gray-200 pt-6">
                <p class="text-sm text-gray-600 mb-4">
                    Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.
                </p>

                <div class="bg-gray-100 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $recoveryCodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white px-3 py-2 rounded"><?php echo e($code); ?></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                </div>

                <div class="mt-4">
                    <button wire:click="$set('showingRecoveryCodes', false)"
                            class="text-sm text-gray-600 underline hover:text-gray-900">
                        Done
                    </button>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <?php if(auth()->user()->hasTwoFactorEnabled()): ?>
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
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>
</div><?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/livewire/two-factor-authentication.blade.php ENDPATH**/ ?>