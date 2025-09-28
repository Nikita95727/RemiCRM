<div>
    <!-- Premium Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
        <!-- Total Accounts -->
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-lg hover:border-slate-300/80 transition-all duration-300">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14-7l2 2m0 0l2 2m-2-2v6m-2-2H9"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['total_accounts'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Total Accounts</p>
                </div>
            </div>
        </div>

        <!-- Active Accounts -->
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-lg hover:border-slate-300/80 transition-all duration-300">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-emerald-500 to-green-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['active_accounts'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Active</p>
                </div>
            </div>
        </div>

        <!-- Syncing Accounts -->
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-lg hover:border-slate-300/80 transition-all duration-300">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['syncing_accounts'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Syncing</p>
                </div>
            </div>
        </div>

        <!-- Needs Sync -->
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-lg hover:border-slate-300/80 transition-all duration-300">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ $stats['accounts_needing_sync'] ?? 0 }}</p>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Needs Sync</p>
                </div>
            </div>
        </div>

        <!-- Last Sync -->
        <div class="group bg-white/80 backdrop-blur-sm rounded-xl p-4 border border-slate-200/60 shadow-sm hover:shadow-lg hover:border-slate-300/80 transition-all duration-300">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-gradient-to-r from-slate-500 to-gray-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-slate-900 truncate">{{ $stats['last_sync'] ?? 'Never' }}</p>
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wider">Last Sync</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Refresh Button -->
    <div class="flex justify-center">
        <button wire:click="refreshStats" 
                wire:loading.attr="disabled"
                type="button" 
                class="group inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed">
            <div wire:loading.remove wire:target="refreshStats" class="flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh Status</span>
            </div>
            <div wire:loading wire:target="refreshStats" class="flex items-center space-x-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Refreshing...</span>
            </div>
        </button>
    </div>
</div>
