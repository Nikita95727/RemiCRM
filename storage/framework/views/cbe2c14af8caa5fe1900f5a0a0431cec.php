<div>
    <!--[if BLOCK]><![endif]--><?php if(($errorAccounts ?? []) && count($errorAccounts ?? []) > 0): ?>
        <div class="mb-6 space-y-4">
            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $errorAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3 flex-1">
                            <!-- Error Icon -->
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            
                            <!-- Error Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 mb-1">
                                    <h4 class="text-sm font-semibold text-red-800">
                                        <?php echo e($account['provider']); ?> Synchronization Error
                                    </h4>
                                    <!--[if BLOCK]><![endif]--><?php if($account['account_name']): ?>
                                        <span class="text-xs text-red-600 bg-red-100 px-2 py-1 rounded-full">
                                            <?php echo e($account['account_name']); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                
                                <p class="text-sm text-red-700 mb-2">
                                    <?php echo e($account['error_message']); ?>

                                </p>
                                
                                <!--[if BLOCK]><![endif]--><?php if($account['last_error_at']): ?>
                                    <p class="text-xs text-red-600">
                                        Last error: <?php echo e($account['last_error_at']); ?>

                                    </p>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-2 ml-4">
                            <!--[if BLOCK]><![endif]--><?php if($account['can_retry']): ?>
                                <button 
                                    wire:click="retryAccount(<?php echo e($account['id']); ?>)"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg border border-transparent shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                                    wire:loading.attr="disabled"
                                    wire:target="retryAccount(<?php echo e($account['id']); ?>)"
                                >
                                    <svg wire:loading.remove wire:target="retryAccount(<?php echo e($account['id']); ?>)" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    <svg wire:loading wire:target="retryAccount(<?php echo e($account['id']); ?>)" class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span wire:loading.remove wire:target="retryAccount(<?php echo e($account['id']); ?>)">Retry</span>
                                    <span wire:loading wire:target="retryAccount(<?php echo e($account['id']); ?>)">Starting...</span>
                                </button>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            
                            <button 
                                wire:click="dismissError(<?php echo e($account['id']); ?>)"
                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg border border-red-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                                title="Dismiss error"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
            
            <!-- Help Text -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">What to do when synchronization errors occur:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>Check your internet connection</li>
                            <li>Make sure your account is not blocked</li>
                            <li>For 2FA errors, enter the verification code</li>
                            <li>If the problem persists, reconnect your account</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/livewire/integration/integration-errors.blade.php ENDPATH**/ ?>