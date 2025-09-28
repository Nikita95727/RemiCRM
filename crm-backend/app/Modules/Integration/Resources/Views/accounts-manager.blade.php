<div>
    <!-- Premium Header with Add Account Button -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-black text-slate-900 mb-2">Connected Accounts</h2>
            <p class="text-lg text-slate-600">Manage your messaging and email integrations</p>
        </div>
        
        <div class="flex space-x-4">
            @foreach($availableProviders as $value => $provider)
                <button type="button" 
                        class="group inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r {{ $provider->getCssClass() }} rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5">
                    <div class="flex items-center space-x-3">
                        <div class="w-5 h-5 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $provider->getIcon() }}"></path>
                            </svg>
                        </div>
                        <span>Connect {{ $provider->getLabel() }}</span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Loading State -->
    <div wire:loading.delay wire:target="loadAccounts" class="flex justify-center py-12">
        <div class="flex items-center space-x-3">
            <svg class="w-6 h-6 text-indigo-600 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-lg font-medium text-slate-700">Loading accounts...</span>
        </div>
    </div>

    <!-- Accounts List -->
    <div wire:loading.remove wire:target="loadAccounts">
        @if(empty($accounts))
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center shadow-lg">
                    <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7l2 2m0 0l2 2m-2-2v6m-2-2H9"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3">No Connected Accounts</h3>
                <p class="text-lg text-slate-600 mb-8 max-w-md mx-auto">
                    Connect your first messaging or email account to start syncing contacts automatically.
                </p>
                <div class="flex justify-center space-x-4">
                    @foreach($availableProviders as $value => $provider)
                        <button type="button" 
                                class="group inline-flex items-center px-8 py-4 text-base font-bold text-white bg-gradient-to-r {{ $provider->getCssClass() }} rounded-2xl shadow-xl hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105 hover:-translate-y-1">
                            <div class="flex items-center space-x-3">
                                <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="{{ $provider->getIcon() }}"></path>
                                    </svg>
                                </div>
                                <span>Connect {{ $provider->getLabel() }}</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Premium Accounts Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($accounts as $account)
                    <div class="group bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-slate-300/80 transition-all duration-300 p-6">
                        <!-- Account Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <!-- Provider Icon -->
                                <div class="w-12 h-12 {{ $account['provider_css_class'] }} rounded-xl flex items-center justify-center shadow-md">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="{{ $account['provider_icon'] }}"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900">{{ $account['provider_label'] }}</h3>
                                    <p class="text-sm text-slate-600 truncate max-w-32">{{ $account['display_name'] }}</p>
                                </div>
                            </div>
                            
                            <!-- Status Badge -->
                            <div class="flex items-center space-x-2">
                                @if($account['status'] === 'active')
                                    <div class="w-3 h-3 bg-emerald-500 rounded-full"></div>
                                    <span class="text-xs font-semibold text-emerald-700 uppercase tracking-wider">Active</span>
                                @elseif($account['status'] === 'error')
                                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                                    <span class="text-xs font-semibold text-red-700 uppercase tracking-wider">Error</span>
                                @else
                                    <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">{{ ucfirst($account['status']) }}</span>
                                @endif
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="mb-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Sync Enabled:</span>
                                <span class="font-semibold {{ $account['sync_enabled'] ? 'text-emerald-700' : 'text-slate-500' }}">
                                    {{ $account['sync_enabled'] ? 'Yes' : 'No' }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-slate-600">Last Sync:</span>
                                <span class="font-semibold text-slate-700">{{ $account['last_sync_at'] ?: 'Never' }}</span>
                            </div>
                            @if($account['needs_resync'])
                                <div class="flex items-center space-x-2 mt-2">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wider">Needs Sync</span>
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                            <div class="flex space-x-2">
                                <!-- Toggle Sync -->
                                <button wire:click="toggleSync({{ $account['id'] }})"
                                        type="button" 
                                        class="inline-flex items-center px-3 py-2 text-xs font-semibold {{ $account['sync_enabled'] ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border-amber-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' }} rounded-lg border hover:border-opacity-80 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                                    {{ $account['sync_enabled'] ? 'Disable Sync' : 'Enable Sync' }}
                                </button>

                                <!-- Resync -->
                                @if($account['status'] === 'active')
                                    <button wire:click="resyncAccount({{ $account['id'] }})"
                                            type="button" 
                                            class="inline-flex items-center px-3 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 hover:border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                        Sync Now
                                    </button>
                                @endif
                            </div>

                            <!-- Delete -->
                            <button wire:click="deleteAccount({{ $account['id'] }})"
                                    wire:confirm="Are you sure you want to disconnect this account? This will stop syncing contacts from this source."
                                    type="button" 
                                    class="inline-flex items-center px-3 py-2 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 rounded-lg border border-red-200 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Disconnect
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
