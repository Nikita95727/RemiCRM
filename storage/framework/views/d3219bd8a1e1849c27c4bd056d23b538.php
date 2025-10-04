<div>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Success Message -->
        <!--[if BLOCK]><![endif]--><?php if(session('success')): ?>
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transform ease-out duration-300 transition"
                 x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                 x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="mb-8 rounded-2xl bg-gradient-to-r from-emerald-50 to-green-50 border-2 border-emerald-200/60 p-6 shadow-lg backdrop-blur-sm">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                            <svg class="h-6 w-6 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.53a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-base font-bold text-emerald-900"><?php echo e(session('success')); ?></p>
                        <p class="text-sm text-emerald-700 mt-1">Your contact has been successfully updated.</p>
                    </div>
                    <div class="ml-4">
                        <button @click="show = false" type="button" class="inline-flex rounded-xl bg-emerald-100/80 p-2 text-emerald-600 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-all duration-200">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Import Progress Indicator -->
        <!--[if BLOCK]><![endif]--><?php if($this->importStatus): ?>
            <?php
                $isCompleted = $this->importStatus->status === 'completed';
                $bgColor = $isCompleted ? 'from-emerald-50 to-green-50' : 'from-blue-50 to-indigo-50';
                $borderColor = $isCompleted ? 'border-emerald-200/60' : 'border-blue-200/60';
                $iconBg = $isCompleted ? 'bg-emerald-100' : 'bg-blue-100';
                $iconColor = $isCompleted ? 'text-emerald-600' : 'text-blue-600';
                $textColor = $isCompleted ? 'text-emerald-900' : 'text-blue-900';
                $subtextColor = $isCompleted ? 'text-emerald-700' : 'text-blue-700';
                $badgeBg = $isCompleted ? 'bg-emerald-100/80' : 'bg-blue-100/80';
                $badgeColor = $isCompleted ? 'text-emerald-600' : 'text-blue-600';
                $progressColor = $isCompleted ? 'bg-emerald-200' : 'bg-blue-200';
                $progressFillColor = $isCompleted ? 'bg-emerald-600' : 'bg-blue-600';
            ?>
            <div class="mb-8 rounded-2xl bg-gradient-to-r <?php echo e($bgColor); ?> border-2 <?php echo e($borderColor); ?> p-6 shadow-lg backdrop-blur-sm"
                 wire:poll.1s>
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-10 h-10 <?php echo e($iconBg); ?> rounded-xl flex items-center justify-center">
                            <!--[if BLOCK]><![endif]--><?php if($this->importStatus->status === 'importing'): ?>
                                <svg class="h-6 w-6 <?php echo e($iconColor); ?> animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            <?php elseif($this->importStatus->status === 'completed'): ?>
                                <svg class="h-6 w-6 <?php echo e($iconColor); ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            <?php else: ?>
                                <svg class="h-6 w-6 <?php echo e($iconColor); ?> animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-base font-bold <?php echo e($textColor); ?>">
                            <?php echo e($this->importStatus->status_message); ?>

                        </p>
                        <p class="text-sm <?php echo e($subtextColor); ?> mt-1">
                            <?php echo e($this->importStatus->message ?? 'Processing...'); ?>

                        </p>
                        <!--[if BLOCK]><![endif]--><?php if($this->importStatus->total_items > 0): ?>
                            <div class="mt-3">
                                <div class="flex justify-between text-xs <?php echo e($iconColor); ?> mb-1">
                                    <span>Progress</span>
                                    <span><?php echo e($this->importStatus->processed_items); ?>/<?php echo e($this->importStatus->total_items); ?></span>
                                </div>
                                <div class="w-full <?php echo e($progressColor); ?> rounded-full h-2">
                                    <div class="<?php echo e($progressFillColor); ?> h-2 rounded-full transition-all duration-300" 
                                         style="width: <?php echo e($this->importStatus->progress_percentage); ?>%"></div>
                                </div>
                            </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </div>
                    <div class="ml-4">
                        <div class="inline-flex rounded-xl <?php echo e($badgeBg); ?> p-2 <?php echo e($badgeColor); ?>">
                            <span class="text-xs font-medium"><?php echo e(ucfirst($this->importStatus->provider)); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

        <!-- Premium Header -->
        <div class="mb-8">
            <!-- Title Section -->
            <div class="flex items-center space-x-4 mb-6">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            <div>
                    <h1 class="text-3xl font-bold text-slate-900 tracking-tight">
                        Your Contacts
                </h1>
                    <p class="text-lg text-slate-600 font-medium mt-1">
                    Manage all your contacts from different platforms in one place
                </p>
                </div>
            </div>
            
            <!-- Stats Cards and Add Button -->
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="flex-1">
                    <div class="flex space-x-3">
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 border border-slate-200/60 shadow-sm h-20 w-32 flex items-center flex-shrink-0">
                            <div class="flex items-center space-x-2 w-full">
                                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">Total</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo e($contacts->total() ?? 0); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 border border-slate-200/60 shadow-sm h-20 w-32 flex items-center flex-shrink-0">
                            <div class="flex items-center space-x-2 w-full">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">CRM</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo e($this->contactStats['crm']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 border border-slate-200/60 shadow-sm h-20 w-32 flex items-center flex-shrink-0">
                            <div class="flex items-center space-x-2 w-full">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">Telegram</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo e($this->contactStats['telegram']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 border border-slate-200/60 shadow-sm h-20 w-32 flex items-center flex-shrink-0">
                            <div class="flex items-center space-x-2 w-full">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">WhatsApp</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo e($this->contactStats['whatsapp']); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 border border-slate-200/60 shadow-sm h-20 w-32 flex items-center flex-shrink-0">
                            <div class="flex items-center space-x-2 w-full">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-slate-600">Gmail</p>
                                    <p class="text-lg font-bold text-slate-900"><?php echo e($this->contactStats['gmail']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Burger Menu -->
                <div class="flex-shrink-0 self-start relative" x-data="{ open: false }">
                    <!-- Menu Button -->
                    <button @click="open = !open" 
                            type="button" 
                            class="group inline-flex items-center justify-center w-16 h-16 text-slate-800 bg-white border-2 border-slate-300 hover:border-indigo-400 rounded-2xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                        <div class="relative w-6 h-6">
                            <!-- Animated Burger Icon -->
                            <span class="absolute block w-6 h-0.5 bg-slate-800 transition-all duration-300 transform"
                                  :class="open ? 'rotate-45 top-3' : 'top-1'"></span>
                            <span class="absolute block w-6 h-0.5 bg-slate-800 top-3 transition-all duration-300"
                                  :class="open ? 'opacity-0' : 'opacity-100'"></span>
                            <span class="absolute block w-6 h-0.5 bg-slate-800 transition-all duration-300 transform"
                                  :class="open ? '-rotate-45 top-3' : 'top-5'"></span>
                        </div>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         x-transition:enter="transform ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transform ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                         @click.away="open = false"
                         class="absolute right-0 top-full mt-3 w-64 bg-white/95 backdrop-blur-sm rounded-2xl shadow-2xl border border-slate-200/60 py-3 z-50">
                        
                        <!-- Add Contact -->
                        <button @click="open = false; window.dispatchEvent(new CustomEvent('open-contact-form'))"
                                class="w-full flex items-center px-6 py-4 text-left text-slate-700 hover:bg-indigo-50/80 hover:text-indigo-700 transition-all duration-200 group">
                            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-base">Add Contact</div>
                                <div class="text-sm text-slate-500 group-hover:text-indigo-600">Create a new contact manually</div>
                            </div>
                        </button>

                        <!-- Divider -->
                        <div class="mx-4 my-2 h-px bg-slate-200"></div>

                        <!-- Connect Account -->
                        <button onclick="window.Livewire.dispatch('openConnectModal')" 
                                @click="open = false"
                                class="w-full flex items-center px-6 py-4 text-left text-slate-700 hover:bg-emerald-50/80 hover:text-emerald-700 transition-all duration-200 group">
                            <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-cyan-600 rounded-xl flex items-center justify-center mr-4 group-hover:scale-110 transition-transform duration-200">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-base">Connect Account</div>
                                <div class="text-sm text-slate-500 group-hover:text-emerald-600">Link Telegram, WhatsApp, Gmail</div>
                            </div>
                        </button>

                        <!-- Divider -->
                        <div class="mx-4 my-2 h-px bg-slate-200"></div>

                        <!-- Manual Sync -->
                        <button <?php if($hasActiveAccounts): ?> wire:click="manualSync" <?php endif; ?>
                                @click="open = false"
                                <?php if(!$hasActiveAccounts): ?> disabled <?php endif; ?>
                                class="w-full flex items-center px-6 py-4 text-left transition-all duration-200 group <?php if($hasActiveAccounts): ?> text-slate-700 hover:bg-blue-50/80 hover:text-blue-700 cursor-pointer <?php else: ?> text-slate-400 cursor-not-allowed <?php endif; ?>">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 transition-transform duration-200 <?php if($hasActiveAccounts): ?> bg-gradient-to-r from-blue-500 to-indigo-600 group-hover:scale-110 <?php else: ?> bg-gradient-to-r from-slate-300 to-slate-400 <?php endif; ?>">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-base">Sync Contacts</div>
                                <div class="text-sm transition-colors duration-200 <?php if($hasActiveAccounts): ?> text-slate-500 group-hover:text-blue-600 <?php else: ?> text-slate-400 <?php endif; ?>">
                                    <!--[if BLOCK]><![endif]--><?php if($hasActiveAccounts): ?>
                                        Manually sync from all accounts
                                    <?php else: ?>
                                        No accounts connected
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </div>
                </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Premium Filters -->
        <div class="mb-8 relative">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-lg p-6 overflow-visible">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 overflow-visible">
                    <!-- Advanced Search -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-bold text-slate-700 mb-3">
                            <div class="flex items-center space-x-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <span>Search Contacts</span>
                            </div>
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input type="text" 
                                   wire:model.live.debounce.300ms="search"
                                   id="search"
                                   class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-500 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-medium shadow-sm hover:shadow-md focus:shadow-lg transition-all duration-300" 
                                   placeholder="Search by name, email, phone, tags, notes, or source...">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4">
                                <div class="text-xs text-slate-400 font-mono bg-slate-200 px-2 py-1 rounded-md">
                                    ⌘K for quick search
                                </div>
                            </div>
                        </div>
                    </div>

                        <!-- Source Filter (Multi-select) -->
                        <div class="overflow-visible">
                            <label class="block text-sm font-bold text-slate-700 mb-3">
                                <div class="flex items-center space-x-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    <span>Filter by Source</span>
                                    <!--[if BLOCK]><![endif]--><?php if(!empty($sourceFilters)): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            <?php echo e(count($sourceFilters)); ?>

                                        </span>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            </label>
                            
                            <div class="relative" x-data="{ 
                                open: false,
                                toggleDropdown() {
                                    this.open = !this.open;
                                    if (this.open) {
                                        this.$nextTick(() => {
                                            const button = this.$refs.filterButton;
                                            const dropdown = document.getElementById('source-filter-dropdown');
                                            if (button && dropdown) {
                                                const rect = button.getBoundingClientRect();
                                                dropdown.style.display = 'block';
                                                dropdown.style.top = (rect.bottom + window.scrollY + 8) + 'px';
                                                dropdown.style.left = rect.left + 'px';
                                                dropdown.style.width = rect.width + 'px';
                                            }
                                        });
                                    } else {
                                        const dropdown = document.getElementById('source-filter-dropdown');
                                        if (dropdown) {
                                            dropdown.style.display = 'none';
                                        }
                                    }
                                }
                            }">
                                <!-- Multi-select Button -->
                                <button @click="toggleDropdown()" 
                                        type="button"
                                        x-ref="filterButton"
                                        class="relative w-full px-4 py-4 text-left bg-white border-2 border-slate-200 rounded-xl shadow-sm hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all duration-200 min-h-[60px]"
                                        style="height: 60px;">
                                    <div class="flex items-center justify-between h-full">
                                        <div class="flex items-center space-x-2 flex-1 min-w-0">
                                            <!--[if BLOCK]><![endif]--><?php if(empty($sourceFilters)): ?>
                                                <span class="text-slate-500 text-sm">Select sources...</span>
                                            <?php else: ?>
                                                <div class="flex flex-wrap gap-1">
                                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sourceFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $selectedSource): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <?php $sourceObj = \App\Shared\Enums\ContactSource::from($selectedSource); ?>
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                            <div class="w-3 h-3 rounded-sm <?php echo e($sourceObj->getCssClass()); ?> flex items-center justify-center mr-1">
                                                                <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                                                                </svg>
                                                            </div>
                                                            <?php echo e($sourceObj->getLabel()); ?>

                                                        </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                                </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                        </div>
                                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>
                    </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integration Errors -->
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('integration.integration-errors');

$__html = app('livewire')->mount($__name, $__params, 'lw-171909754-0', $__slots ?? [], get_defined_vars());

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

        <!-- Premium Contact Cards -->
        <div class="space-y-4">
                        <!--[if BLOCK]><![endif]--><?php $__empty_1 = true; $__currentLoopData = $contacts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div wire:key="contact-<?php echo e($contact->id); ?>" class="group bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-slate-300/80 transition-all duration-300 p-6">
                    <div class="flex items-center justify-between">
                        <!-- Contact Info -->
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Avatar -->
                            <div class="relative">
                                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <span class="text-lg font-bold text-white"><?php echo e($contact->initials); ?></span>
                                                </div>
                                <!-- Source Indicators -->
                                <!--[if BLOCK]><![endif]--><?php if(($contact->sources ?? []) && count($contact->sources ?? []) > 1): ?>
                                    <div class="absolute -bottom-2 -right-2 flex space-x-1">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = array_slice($contact->sourceObjects, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $sourceObj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div class="w-5 h-5 <?php echo e($sourceObj->getCssClass()); ?> rounded-full flex items-center justify-center shadow-sm border border-white" 
                                                 style="z-index: <?php echo e(10 - $index); ?>; margin-left: <?php echo e($index * -8); ?>px;">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                                                    </svg>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        <!--[if BLOCK]><![endif]--><?php if(count($contact->sources ?? []) > 3): ?>
                                            <div class="w-5 h-5 bg-slate-600 text-white rounded-full flex items-center justify-center shadow-sm border border-white text-xs font-bold"
                                                 style="margin-left: -8px;">
                                                +<?php echo e(count($contact->sources ?? []) - 3); ?>

                                            </div>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php elseif(count($contact->sources ?? []) === 1): ?>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-sm border-2 border-white">
                                        <div class="w-4 h-4 <?php echo e($contact->primarySource->getCssClass()); ?> rounded-full flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="<?php echo e($contact->primarySource->getIcon()); ?>"></path>
                                            </svg>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center shadow-sm border-2 border-white">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                            
                            <!-- Contact Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900 truncate"><?php echo e($contact->name); ?></h3>
                                    <div class="flex flex-wrap gap-2">
                                        <!--[if BLOCK]><![endif]--><?php if(empty($contact->sourceObjects)): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                No source
                                    </span>
                                        <?php else: ?>
                                            <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $contact->sourceObjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sourceObj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold <?php echo e($sourceObj->getCssClass()); ?> shadow-sm">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                                                    </svg>
                                                    <?php echo e($sourceObj->getLabel()); ?>

                                                </span>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-1 sm:space-y-0 text-sm text-slate-600">
                                    <!--[if BLOCK]><![endif]--><?php if($contact->email): ?>
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                            </svg>
                                            <span class="font-medium"><?php echo e($contact->email); ?></span>
                                        </div>
                                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    
                                    <!--[if BLOCK]><![endif]--><?php if($contact->phone): ?>
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="font-medium"><?php echo e($contact->phone); ?></span>
                                        </div>
                                    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                                
                                <!-- Tags -->
                                <!--[if BLOCK]><![endif]--><?php if(($contact->tags ?? []) && count($contact->tags ?? []) > 0): ?>
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = array_slice($contact->tags, 0, 4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                                                <svg class="w-3 h-3 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                <?php echo e($tag); ?>

                                            </span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                        <!--[if BLOCK]><![endif]--><?php if(count($contact->tags ?? []) > 4): ?>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-200 text-slate-600 border border-slate-300">
                                                +<?php echo e(count($contact->tags ?? []) - 4); ?> more
                                            </span>
                                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                                    </div>
                                <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            <button wire:click="viewContact(<?php echo e($contact->id); ?>)" 
                                    class="group/btn inline-flex items-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 hover:border-slate-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2 group-hover/btn:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                View
                            </button>
                            
                            <button wire:click="editContact(<?php echo e($contact->id); ?>)" 
                                    class="group/btn inline-flex items-center px-4 py-2 text-sm font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-xl border border-indigo-200 hover:border-indigo-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2 group-hover/btn:rotate-12 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                Edit
                                    </button>
                            
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="inline-flex items-center p-2 text-slate-500 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Premium Dropdown Menu -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95 transform -translate-y-2"
                                     x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="open = false"
                                     class="absolute right-0 top-full mt-2 w-48 bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl border border-slate-200/60 py-2 z-50">
                                    
                                    <!-- Delete Option -->
                                    <button wire:click="confirmDelete(<?php echo e($contact->id); ?>)"
                                            @click="open = false"
                                            class="group w-full flex items-center px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-3 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete Contact
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-12 h-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No contacts found</h3>
                    <p class="text-slate-600 mb-6 max-w-md mx-auto">
                        <!--[if BLOCK]><![endif]--><?php if($search || !empty($sourceFilters)): ?>
                            Try adjusting your search or filter criteria, or add a new contact to get started.
                        <?php else: ?>
                            Get started by adding your first contact to begin building your network.
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                    </p>
                    <button wire:click="openContactForm" 
                            class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                        </svg>
                        Add Your First Contact
                    </button>
                </div>
                        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
        </div>

        <!-- Premium Pagination -->
        <!--[if BLOCK]><![endif]--><?php if($contacts->hasPages()): ?>
            <div class="mt-8 flex justify-center">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-lg p-2">
                <?php echo e($contacts->links('vendor.pagination.custom-tailwind')); ?>

                </div>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- Premium Delete Confirmation Modal -->
    <!--[if BLOCK]><![endif]--><?php if($showDeleteModal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ show: <?php if ((object) ('showDeleteModal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDeleteModal'->value()); ?>')<?php echo e('showDeleteModal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('showDeleteModal'); ?>')<?php endif; ?> }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <!-- Background Overlay -->
            <div class="fixed inset-0 bg-gradient-to-br from-slate-900/80 via-red-900/60 to-slate-900/80 backdrop-blur-sm"></div>
            
            <!-- Modal Container -->
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="relative bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-red-200/60 max-w-md w-full mx-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-90 transform translate-y-8"
                     x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-90">
                    
                    <!-- Header with Warning Icon -->
                    <div class="relative bg-gradient-to-r from-red-50 to-orange-50 px-6 py-4 rounded-t-2xl border-b border-red-200/60">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-slate-900">Delete Contact</h3>
                                <p class="text-sm text-slate-600 mt-1">This action cannot be undone</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content -->
                    <div class="px-6 py-6">
                        <p class="text-slate-700 text-base leading-relaxed">
                            Are you sure you want to delete 
                            <span class="font-bold text-slate-900">"<?php echo e($contactToDeleteName); ?>"</span>? 
                            This contact will be removed from your CRM and cannot be recovered.
                        </p>
                    </div>
                    
                    <!-- Actions -->
                    <div class="bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-4 rounded-b-2xl border-t border-slate-200/60 flex justify-end space-x-3">
                        <!-- Cancel Button -->
                        <button wire:click="cancelDelete" 
                                class="inline-flex items-center px-6 py-3 text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50 rounded-xl border border-slate-300 hover:border-slate-400 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Cancel
                        </button>
                        
                        <!-- Delete Button -->
                        <button wire:click="deleteContact" 
                                class="group inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Delete Contact
                            <div class="absolute inset-0 rounded-xl bg-gradient-to-r from-red-600 to-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300 -z-10 blur-xl"></div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    <script>

function connectTelegramDirectly() {
    // Show loading message
    const toast = document.createElement('div');
    toast.className = 'fixed top-4 right-4 z-50 p-4 bg-blue-600 text-white rounded-lg shadow-lg';
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Connecting to Telegram...</span>
        </div>
    `;
    document.body.appendChild(toast);
    
    // Call Livewire method to connect Telegram directly
    window.Livewire.find('connect-account-component')?.call('connectTelegramDirect');
    
    // If component not found, fallback to dispatch
    if (!window.Livewire.find('connect-account-component')) {
        window.Livewire.dispatch('connect-telegram-direct');
    }
    
    // Start polling after connecting
    setTimeout(() => {
        window.Livewire.dispatch('start-polling');
    }, 2000);
}

// Auto-refresh polling for new contacts
let pollingInterval;

function startContactsPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    
    pollingInterval = setInterval(() => {
        window.Livewire.dispatch('check-new-contacts');
    }, 3000); // Check every 3 seconds
    
    // Stop polling after 2 minutes to avoid infinite polling
    setTimeout(() => {
        stopContactsPolling();
    }, 120000);
}

function stopContactsPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    window.Livewire.dispatch('stop-polling');
}

// Start polling when page loads if user came from integration
document.addEventListener('DOMContentLoaded', function() {
    // Check if user came from integration waiting page
    if (document.referrer.includes('/integration/waiting') || 
        sessionStorage.getItem('integration_completed') === 'true') {
        
        console.log('Starting contacts polling after integration...');
        startContactsPolling();
        
        // Clear the flag
        sessionStorage.removeItem('integration_completed');
    }
});

// Listen for integration completion events
window.addEventListener('integration-completed', function() {
    console.log('Integration completed, starting polling...');
    sessionStorage.setItem('integration_completed', 'true');
    startContactsPolling();
});

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('source-filter-dropdown');
    const button = document.querySelector('[x-ref="filterButton"]');
    const filterContainer = button?.closest('[x-data]');
    
    if (dropdown && button && filterContainer &&
        dropdown.style.display === 'block' &&
        !dropdown.contains(event.target) && 
        !button.contains(event.target)) {
        
        dropdown.style.display = 'none';
        // Update Alpine.js state
        if (filterContainer._x_dataStack && filterContainer._x_dataStack[0]) {
            filterContainer._x_dataStack[0].open = false;
        }
    }
});
</script>

    <!-- Portal Dropdown - Fixed Position -->
    <div id="source-filter-dropdown" 
         style="display: none; position: fixed; z-index: 99999; min-width: 300px;"
         class="bg-white border-2 border-slate-200 rounded-xl shadow-2xl py-2">
        
        <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $sourceObj = \App\Shared\Enums\ContactSource::from($value); ?>
            <label class="flex items-center px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors duration-200"
                   onclick="event.stopPropagation();">
                <input type="checkbox" 
                       wire:model.live="sourceFilters" 
                       value="<?php echo e($value); ?>"
                       class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                <div class="ml-3 flex items-center space-x-3">
                    <div class="w-5 h-5 rounded-md <?php echo e($sourceObj->getCssClass()); ?> flex items-center justify-center shadow-sm">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-900"><?php echo e($label); ?></span>
                </div>
            </label>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
        
        <!--[if BLOCK]><![endif]--><?php if(!empty($sourceFilters)): ?>
            <div class="border-t border-slate-200 mt-2 pt-2">
            <button wire:click="$set('sourceFilters', [])"
                    onclick="setTimeout(() => { document.getElementById('source-filter-dropdown').style.display = 'none'; }, 100);"
                    class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 hover:bg-slate-50 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear All
                </button>
            </div>
        <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
    </div>

    <!-- View Contact Modal -->
    <!--[if BLOCK]><![endif]--><?php if($viewingContact): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <!-- Background overlay -->
            <div class="flex items-center justify-center min-h-screen px-4 py-4 text-center sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gradient-to-br from-slate-900/90 via-indigo-900/80 to-purple-900/90 backdrop-blur-md" 
                     wire:click="closeViewModal"></div>

                <!-- Modal panel -->
                <div class="relative inline-block w-full max-w-2xl text-left align-middle transition-all transform bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-slate-200/60">
                    
                    <!-- Premium Header -->
                    <div class="relative bg-gradient-to-r from-slate-600 via-slate-700 to-slate-600 px-6 py-4 rounded-t-2xl">
                        <div class="absolute inset-0 bg-gradient-to-r from-slate-600/90 via-slate-700/90 to-slate-600/90 backdrop-blur-sm rounded-t-2xl"></div>
                        <div class="relative flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div>
                                    <h3 class="text-lg font-bold text-white tracking-tight" id="modal-title">
                                        <?php echo e($viewingContact->name); ?>

                                    </h3>
                                    <p class="text-slate-100 text-sm font-medium">
                                        View contact information
                                    </p>
                                </div>
                            </div>
                            <button wire:click="closeViewModal" 
                                    class="group rounded-xl bg-slate-700/90 hover:bg-slate-800/90 p-2 text-white focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all duration-200 backdrop-blur-sm border border-slate-600 hover:border-slate-500">
                                <svg class="h-4 w-4 group-hover:rotate-90 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="px-6 py-4 bg-gradient-to-b from-slate-50/80 via-white to-slate-50/50 backdrop-blur-sm space-y-4">
                        
                        <!-- Contact Information -->
                        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Contact Information
                            </h4>
                            <div class="space-y-4">
                                <!-- Full Name -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-600">Full Name</p>
                                        <p class="font-semibold text-slate-900"><?php echo e($viewingContact->name); ?></p>
                                    </div>
                                </div>

                                <!-- Phone -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-600">Phone</p>
                                        <p class="font-medium text-slate-900">
                                            <?php echo e($viewingContact->phone ?: '—'); ?>

                                        </p>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm text-slate-600">Email</p>
                                        <p class="font-medium text-slate-900">
                                            <?php echo e($viewingContact->email ?: '—'); ?>

                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sources -->
                        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                                Connected Platforms
                            </h4>
                            <!--[if BLOCK]><![endif]--><?php if(($viewingContact->sources ?? []) && count($viewingContact->sources ?? []) > 0): ?>
                                <div class="flex flex-wrap gap-3">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $viewingContact->sourceObjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sourceObj): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="inline-flex items-center px-4 py-2 rounded-lg <?php echo e($sourceObj->getCssClass()); ?> shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="<?php echo e($sourceObj->getIcon()); ?>"></path>
                                            </svg>
                                            <span class="font-medium"><?php echo e($sourceObj->getLabel()); ?></span>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php else: ?>
                                <p class="text-slate-500 italic">No connected platforms</p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Tags -->
                        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Tags
                            </h4>
                            <!--[if BLOCK]><![endif]--><?php if(($viewingContact->tags ?? []) && count($viewingContact->tags ?? []) > 0): ?>
                                <div class="flex flex-wrap gap-2">
                                    <!--[if BLOCK]><![endif]--><?php $__currentLoopData = $viewingContact->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                            <svg class="w-3 h-3 mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                            </svg>
                                            <?php echo e($tag); ?>

                                        </span>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><!--[if ENDBLOCK]><![endif]-->
                                </div>
                            <?php else: ?>
                                <p class="text-slate-500 italic">No tags assigned</p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>

                        <!-- Notes -->
                        <div class="bg-white rounded-xl p-6 border border-slate-200 shadow-sm">
                            <h4 class="text-lg font-semibold text-slate-900 mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Notes
                            </h4>
                            <!--[if BLOCK]><![endif]--><?php if($viewingContact->notes): ?>
                                <div class="prose prose-sm max-w-none">
                                    <p class="text-slate-700 whitespace-pre-wrap"><?php echo e($viewingContact->notes); ?></p>
                                </div>
                            <?php else: ?>
                                <p class="text-slate-500 italic">No notes available</p>
                            <?php endif; ?><!--[if ENDBLOCK]><![endif]-->
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-50 via-white to-slate-50 border-t border-slate-200/60 rounded-b-2xl flex items-center justify-end space-x-3">
                        <button wire:click="closeViewModal" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200 shadow-sm">
                            Close
                        </button>
                        <button wire:click="editContact(<?php echo e($viewingContact->id); ?>)" 
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 rounded-xl border border-transparent focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200 shadow-lg">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Edit Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!--[if ENDBLOCK]><![endif]-->

    </div>
</div><?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/livewire/contact/contacts-list.blade.php ENDPATH**/ ?>