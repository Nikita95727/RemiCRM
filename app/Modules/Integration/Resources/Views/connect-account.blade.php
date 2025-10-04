<div>
    <!-- Connect Account Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto backdrop-blur-sm">
        
        <!-- Background Overlay -->
        <div class="fixed inset-0 transition-opacity bg-gradient-to-br from-slate-900/90 via-indigo-900/80 to-purple-900/90 backdrop-blur-md"></div>

        <!-- Modal Panel -->
        <div class="flex items-center justify-center min-h-full p-4 text-center sm:p-0">
            <div class="relative inline-block w-full max-w-2xl text-left align-middle transition-all transform bg-white/95 backdrop-blur-xl shadow-2xl rounded-2xl border border-slate-200/60">
                
                <!-- Premium Header -->
                <div class="relative bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 px-6 py-6 rounded-t-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/90 via-purple-600/90 to-indigo-700/90 rounded-t-2xl"></div>
                    <div class="relative flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white">Connect Account</h3>
                                <p class="text-indigo-100 text-sm mt-1">Choose a platform to sync your contacts</p>
                            </div>
                        </div>
                        <button wire:click="closeModal" 
                                class="text-white/80 hover:text-white transition-colors duration-200 p-2 hover:bg-white/10 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Content -->
                <div class="px-6 py-6 bg-gradient-to-b from-slate-50/80 via-white to-slate-50/50 backdrop-blur-sm">
                    <!-- Provider Selection -->
                    <div class="space-y-4">
                        <div class="text-center mb-6">
                            <h4 class="text-lg font-bold text-slate-900 mb-2">Select Platform</h4>
                            <p class="text-slate-600">Choose which platform you'd like to connect to your CRM</p>
                            
                        </div>

                        <div class="grid grid-cols-1 gap-4">
                            @foreach($providers as $key => $provider)
                                @if($provider['available'])
                                    @if($key === 'telegram')
                                        <div wire:click="connectTelegram" 
                                             class="group relative cursor-pointer bg-white/80 hover:bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl p-6 transition-all duration-300 hover:shadow-lg">
                                    @elseif($key === 'whatsapp')
                                        <div wire:click="connectWhatsApp" 
                                             class="group relative cursor-pointer bg-white/80 hover:bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl p-6 transition-all duration-300 hover:shadow-lg">
                                    @elseif($key === 'gmail')
                                        <div wire:click="connectGmail" 
                                             class="group relative cursor-pointer bg-white/80 hover:bg-white border-2 border-slate-200 hover:border-indigo-300 rounded-xl p-6 transition-all duration-300 hover:shadow-lg">
                                    @endif
                                @else
                                    <div class="group relative cursor-not-allowed bg-white/80 border-2 border-slate-200 rounded-xl p-6 opacity-60">
                                @endif
                                    
                                    <!-- Provider Icon & Info -->
                                    <div class="flex items-start space-x-4">
                                        <div class="w-12 h-12 bg-gradient-to-r {{ $provider['color'] }} rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="{{ $provider['icon'] }}"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <h5 class="text-lg font-bold text-slate-900">{{ $provider['name'] }}</h5>
                                                @if(!$provider['available'])
                                                    <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                        Coming Soon
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-slate-600 text-sm mt-1">{{ $provider['description'] }}</p>
                                            
                                            <!-- Connected Accounts Icons -->
                                            @if(isset($connectedAccounts[$key]) && count($connectedAccounts[$key]) > 0)
                                                <div class="mt-3">
                                                    <p class="text-xs text-slate-500 mb-2">Connected accounts ({{ count($connectedAccounts[$key]) }}):</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        @foreach($connectedAccounts[$key] as $account)
                                                            <div class="group relative">
                                                                <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 cursor-pointer hover:scale-110">
                                                                    <span class="text-white text-xs font-bold">{{ strtoupper(substr($account['name'], 0, 2)) }}</span>
                                                                </div>
                                                                <!-- Tooltip -->
                                                                <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-white text-black text-xs font-medium rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap z-50 pointer-events-none shadow-xl border border-gray-300">
                                                                    <div class="text-black font-bold">{{ $account['name'] }}</div>
                                                                    <div class="text-gray-600 text-xs mt-1">{{ $account['created_at'] }}</div>
                                                                    <!-- Tooltip arrow -->
                                                                    <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-l-transparent border-r-transparent border-t-white"></div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-slate-400 group-hover:text-indigo-600 transition-colors duration-200">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="bg-gradient-to-r from-slate-50 via-white to-slate-50 px-6 py-4 border-t border-slate-200/60 backdrop-blur-sm rounded-b-2xl">
                    <div class="flex items-center justify-end">
                        <button wire:click="closeModal" 
                                type="button" 
                                class="px-6 py-2 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl border border-slate-200 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
