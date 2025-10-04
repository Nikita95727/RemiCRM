<div wire:id="contact-form">
    <!--[if BLOCK]><![endif]--><?php if($isOpen): ?>
    <!-- Ultra Premium Modal Overlay -->
    <div x-data="{ 
             show: <?php echo \Illuminate\Support\Js::from($isOpen)->toHtml() ?>,
             init() {
                 // Listen for Livewire updates
                 this.$watch('show', value => {
                     if (value) {
                         document.body.style.overflow = 'hidden';
                     } else {
                         document.body.style.overflow = '';
                     }
                 });
                 
                 // Update show when Livewire isOpen changes
                 Livewire.on('modal-opened', () => {
                     this.show = true;
                 });
                 
                 Livewire.on('modal-closed', () => {
                     this.show = false;
                 });
                 
                 // Watch for Livewire property changes
                 this.$wire.$watch('isOpen', (value) => {
                     this.show = value;
                 });
             }
         }" 
         x-show="show" 
         @keydown.escape.window="show = false; $wire.closeForm()" 
         class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm" 
         style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 py-4 text-center sm:p-0">
            <!-- Premium Gradient Background -->
            <div class="fixed inset-0 transition-opacity bg-gradient-to-br from-slate-900/90 via-indigo-900/80 to-purple-900/90 backdrop-blur-md" 
                 wire:click="closeForm"></div>

            <!-- Compact Premium Modal -->
            <div class="relative inline-block w-full max-w-2xl text-left align-middle transition-all transform bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-slate-200/60">
                
                <!-- Compact Premium Header -->
                <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 px-6 py-4 rounded-t-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/90 via-purple-600/90 to-indigo-700/90 backdrop-blur-sm rounded-t-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm shadow-lg border border-white/30">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white tracking-tight">
                                    <?php echo e($isEdit ? 'Edit Contact' : 'Create New Contact'); ?>

                                </h3>
                                <p class="text-indigo-100 text-sm font-medium">
                                    <?php echo e($isEdit ? 'Update contact information' : 'Add a new contact to your CRM'); ?>

                                </p>
                            </div>
                        </div>
                        <button wire:click="closeForm" 
                                class="group rounded-xl bg-slate-700/90 hover:bg-slate-800/90 p-2 text-white focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all duration-200 backdrop-blur-sm border border-slate-600 hover:border-slate-500">
                            <svg class="h-4 w-4 group-hover:rotate-90 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Ultra Compact Form Content -->
                <div class="px-6 py-4 bg-gradient-to-b from-slate-50/80 via-white to-slate-50/50 backdrop-blur-sm">
                    <form wire:submit="save" class="space-y-4">
                        
                        <!-- Premium Name Field -->
                        <div class="group relative">
                            <label for="name" class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Full Name</span>
                                <span class="text-red-500 text-lg">*</span>
                            </label>
                            <div class="relative">
                                <input wire:model="name" 
                                       type="text" 
                                       id="name"
                                       class="w-full px-4 py-3 text-slate-900 bg-white/80 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-base font-medium shadow-lg hover:shadow-xl focus:shadow-2xl backdrop-blur-sm transition-all duration-300 placeholder:text-slate-400 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 focus:border-red-500 focus:ring-red-500/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                       placeholder="Enter full name">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-6">
                                    <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="flex items-center mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                    <div class="w-5 h-5 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-red-700"><?php echo e($message); ?></span>
                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Premium Contact Details Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <!-- Email Field -->
                            <div class="group relative">
                                <label for="email" class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                    <span>Email Address</span>
                                </label>
                                <div class="relative">
                                    <input wire:model="email" 
                                           type="email" 
                                           id="email"
                                           class="w-full px-4 py-3 text-slate-900 bg-white/80 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-base font-medium shadow-lg hover:shadow-xl focus:shadow-2xl backdrop-blur-sm transition-all duration-300 placeholder:text-slate-400 <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 focus:border-red-500 focus:ring-red-500/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           placeholder="john@example.com">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-6">
                                        <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                                            <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="flex items-center mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                        <div class="w-5 h-5 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-red-700"><?php echo e($message); ?></span>
                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>

                            <!-- Phone Field -->
                            <div class="group relative">
                                <label for="phone" class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span>Phone Number</span>
                                </label>
                                <div class="relative">
                                    <input wire:model="phone" 
                                           type="tel" 
                                           id="phone"
                                           x-data="{ 
                                               formatPhone(event) {
                                                   let value = event.target.value;
                                                   // Remove all non-digit characters except + - ( ) and spaces
                                                   value = value.replace(/[^0-9+\-\(\)\s]/g, '');
                                                   event.target.value = value;
                                                   // Update Livewire model
                                                   this.$wire.set('phone', value);
                                               }
                                           }"
                                           @input="formatPhone($event)"
                                           @keypress="
                                               // Allow only numbers, +, -, (, ), and space
                                               const char = String.fromCharCode($event.which);
                                               if (!/[0-9+\-\(\)\s]/.test(char)) {
                                                   $event.preventDefault();
                                               }
                                           "
                                           class="w-full px-4 py-3 text-slate-900 bg-white/80 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-base font-medium shadow-lg hover:shadow-xl focus:shadow-2xl backdrop-blur-sm transition-all duration-300 placeholder:text-slate-400 <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 focus:border-red-500 focus:ring-red-500/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           placeholder="+1 (555) 123-4567"
                                           title="Only numbers, +, -, (, ), and spaces are allowed">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-6">
                                        <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                                            <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="flex items-center mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                        <div class="w-5 h-5 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-red-700"><?php echo e($message); ?></span>
                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <!-- Compact Multi-Source Selection -->
                        <div class="group relative">
                            <label class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.034 0-3.9.785-5.291 2.09M6.343 6.343A8 8 0 1017.657 17.657 8 8 0 006.343 6.343z"></path>
                                </svg>
                                <span>Contact Sources</span>
                                <span class="text-red-500 text-lg">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $sourceObj = $sourceObjects[$value]; ?>
                                    <label class="group/checkbox flex items-center p-2 bg-white/80 border-2 border-slate-200 rounded-lg hover:border-slate-300 hover:shadow-md cursor-pointer transition-all duration-200 <?php echo e(in_array($value, $selectedSources) ? 'border-indigo-500 bg-indigo-50/80 shadow-lg' : ''); ?>">
                                        <div class="relative">
                                            <input type="checkbox" 
                                                   wire:model="selectedSources" 
                                                   value="<?php echo e($value); ?>"
                                                   class="sr-only">
                                            <div class="w-5 h-5 rounded-md border-2 <?php echo e(in_array($value, $selectedSources) ? 'border-indigo-500 bg-indigo-500' : 'border-slate-300 bg-white'); ?> flex items-center justify-center transition-all duration-200">
                                                <!--[if BLOCK]><![endif]--><?php if(in_array($value, $selectedSources)): ?>
                                                    <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                            </div>
                                        </div>
                                        <div class="ml-2 flex items-center space-x-2">
                                            <div class="w-5 h-5 rounded-md <?php echo e($sourceObj->getCssClass()); ?> flex items-center justify-center shadow-sm">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                                                </svg>
                                            </div>
                                            <span class="text-sm font-semibold text-slate-900"><?php echo e($label); ?></span>
                                        </div>
                                    </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['selectedSources'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="flex items-center mt-2 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <div class="w-4 h-4 bg-red-100 rounded-md flex items-center justify-center mr-2">
                                        <svg class="w-2.5 h-2.5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-xs font-semibold text-red-700"><?php echo e($message); ?></span>
                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Compact Additional Fields Grid -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

                            <!-- Tags & Keywords -->
                            <div class="group relative">
                                <label for="tagsInput" class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                    </svg>
                                    <span>Tags & Keywords</span>
                                </label>
                                <div class="relative">
                                    <input wire:model="tagsInput" 
                                           type="text" 
                                           id="tagsInput"
                                           class="w-full px-4 py-3 text-slate-900 bg-white/80 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-base font-medium shadow-lg hover:shadow-xl focus:shadow-2xl backdrop-blur-sm transition-all duration-300 placeholder:text-slate-400 <?php $__errorArgs = ['tagsInput'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 focus:border-red-500 focus:ring-red-500/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                           placeholder="crypto, banking, fintech">
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-6">
                                        <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                                            <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3 text-sm text-slate-600 flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Separate multiple tags with commas
                                </p>
                                <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['tagsInput'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <div class="flex items-center mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                        <div class="w-5 h-5 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                            <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <span class="text-sm font-semibold text-red-700"><?php echo e($message); ?></span>
                                    </div>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>

                        <!-- Premium Notes Field -->
                        <div class="group relative">
                            <label for="notes" class="block text-sm font-bold text-slate-700 mb-2 flex items-center space-x-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                <span>Additional Notes</span>
                            </label>
                            <div class="relative">
                                <textarea wire:model="notes" 
                                          id="notes" 
                                          rows="3"
                                          class="w-full px-4 py-3 text-slate-900 bg-white/80 border-2 border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-base font-medium shadow-lg hover:shadow-xl focus:shadow-2xl backdrop-blur-sm resize-none transition-all duration-300 placeholder:text-slate-400 <?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 focus:border-red-500 focus:ring-red-500/20 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                          placeholder="Additional notes, context, or important details about this contact..."></textarea>
                                <div class="absolute top-4 right-6">
                                    <div class="w-8 h-8 bg-slate-100 group-hover:bg-indigo-100 rounded-xl flex items-center justify-center transition-colors duration-200">
                                        <svg class="w-4 h-4 text-slate-500 group-hover:text-indigo-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <!--[if BLOCK]><![endif]--><?php $__errorArgs = ['notes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <div class="flex items-center mt-3 p-3 bg-red-50 border border-red-200 rounded-xl">
                                    <div class="w-5 h-5 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="w-3 h-3 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-semibold text-red-700"><?php echo e($message); ?></span>
                                </div>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </form>
                </div>

                <!-- Ultra Compact Action Footer -->
                <div class="bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-3 border-t border-slate-200/60 backdrop-blur-sm rounded-b-2xl">
                    <div class="flex items-center justify-between">
                        <button wire:click="closeForm" 
                                type="button"
                                class="group inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-white/80 border-2 border-slate-300 rounded-xl shadow-lg hover:bg-slate-50 hover:border-slate-400 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 backdrop-blur-sm transition-all duration-200">
                            <svg class="w-4 h-4 mr-2 group-hover:-rotate-12 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </button>
                        <button wire:click="save" 
                                type="button"
                                class="group inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 rounded-xl shadow-xl hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 backdrop-blur-sm transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                            <div class="flex items-center space-x-2">
                                <div class="w-5 h-5 bg-white/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-3 h-3 group-hover:rotate-12 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <span><?php echo e($isEdit ? 'Update Contact' : 'Create Contact'); ?></span>
                            </div>
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
</div><?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/livewire/contact/contact-form.blade.php ENDPATH**/ ?>