<div>
    <!-- Premium Command+K Search Modal -->
    <style>
        .global-search-modal[x-cloak] { display: none !important; }
        .global-search-modal.show { 
            display: block !important; 
            opacity: 1 !important;
            visibility: visible !important;
            z-index: 9999 !important;
        }
    </style>
    <div x-data="{ 
        open: false,
        selectedIndex: -1,
        searchTerm: '',
        results: [],
        
        closeModal() {
            this.open = false;
            this.searchTerm = '';
            this.selectedIndex = -1;
        },
        
        async performSearch() {
            if (this.searchTerm.length < 2) {
                this.results = [];
                return;
            }
            
            try {
                // Simple fetch to search endpoint
                const response = await fetch(`/api/contacts/search?q=${encodeURIComponent(this.searchTerm)}`);
                if (response.ok) {
                    this.results = await response.json();
                } else {
                    this.results = [];
                }
            } catch (error) {
                this.results = [];
            }
        },
        
        selectNext() {
            if (this.selectedIndex < this.results.length - 1) {
                this.selectedIndex++;
            }
        },
        
        selectPrevious() {
            if (this.selectedIndex > -1) {
                this.selectedIndex--;
            }
        },
        
        selectCurrentContact() {
            if (this.selectedIndex >= 0 && this.selectedIndex < this.results.length) {
                const contact = this.results[this.selectedIndex];
                if (contact) {
                    // Navigate to contact
                    window.location.href = '/contacts/' + contact.id;
                }
            }
        }
    }">
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="global-search-modal fixed inset-0 z-50" 
             :class="{ 'show': open }"
             role="dialog" 
             aria-modal="true"
             style="display: none;"
             :style="open ? 'display: block !important; opacity: 1 !important; visibility: visible !important; z-index: 9999 !important;' : 'display: none;'"
         @keydown.escape="closeModal()"
         @keydown.cmd.k.prevent="closeModal()"
         @keydown.ctrl.k.prevent="closeModal()"
         @keydown.arrow-down.prevent="selectNext()"
         @keydown.arrow-up.prevent="selectPrevious()"
         @keydown.enter.prevent="selectCurrentContact()">
        
        <!-- Premium Background overlay -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gradient-to-br from-slate-900/80 via-slate-800/70 to-slate-900/80 backdrop-blur-sm transition-opacity"
             @click="closeModal()"></div>
        
        <!-- Premium Modal -->
        <div x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
            
            <div class="mx-auto max-w-3xl transform overflow-hidden rounded-2xl bg-white/95 backdrop-blur-xl shadow-2xl ring-1 ring-slate-200/60 transition-all border border-slate-200/60">
                
                <!-- Premium Search Header -->
                <div class="relative bg-gradient-to-r from-slate-50 to-white border-b border-slate-200/60 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900">Quick Search</h3>
                            <p class="text-sm text-slate-600 mt-1">Find contacts instantly across all platforms</p>
                        </div>
                        <button @click="closeModal()" 
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Premium Search Input -->
                <div class="relative p-6 pb-4">
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-6 w-6 text-slate-400 group-focus-within:text-indigo-500 transition-colors duration-200" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchTerm"
                               @input.debounce.300ms="performSearch()"
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-500 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-lg font-medium shadow-sm hover:shadow-md focus:shadow-lg transition-all duration-300" 
                               placeholder="Search contacts by name, email, phone, tags, or source..." 
                               role="combobox" 
                               aria-expanded="false" 
                               aria-controls="search-results"
                               x-ref="searchInput">
                    </div>
                </div>
                
                <!-- Premium Search Results -->
                <div id="search-results" class="max-h-96 overflow-y-auto">
                    @if(empty($results) && strlen($search) === 0)
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-slate-100 to-slate-200 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900 mb-2">Start searching</h4>
                            <p class="text-slate-600 mb-6 max-w-sm mx-auto">Type to find contacts by name, email, phone, tags, or source platform</p>
                            <div class="flex items-center justify-center space-x-4 text-xs text-slate-500">
                                <div class="flex items-center space-x-1">
                                    <kbd class="inline-flex items-center rounded-md border border-slate-300 bg-slate-50 px-2 py-1 font-mono shadow-sm">↑↓</kbd>
                                    <span>navigate</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <kbd class="inline-flex items-center rounded-md border border-slate-300 bg-slate-50 px-2 py-1 font-mono shadow-sm">↵</kbd>
                                    <span>select</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <kbd class="inline-flex items-center rounded-md border border-slate-300 bg-slate-50 px-2 py-1 font-mono shadow-sm">esc</kbd>
                                    <span>close</span>
                                </div>
                            </div>
                        </div>
                    @elseif(empty($results) && strlen($search) >= 2)
                        <div class="p-8 text-center">
                            <div class="w-16 h-16 bg-gradient-to-br from-amber-100 to-orange-100 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6m2 5.291A7.962 7.962 0 0112 15c-2.034 0-3.9.785-5.291 2.09M6.343 6.343A8 8 0 1017.657 17.657 8 8 0 006.343 6.343z" />
                                </svg>
                            </div>
                            <h4 class="text-lg font-bold text-slate-900 mb-2">No contacts found</h4>
                            <p class="text-slate-600 mb-4">No results for "<span class="font-semibold text-slate-900">{{ $search }}</span>"</p>
                            <p class="text-sm text-slate-500">Try searching with different keywords or check your spelling</p>
                        </div>
                    @else
                        <div class="p-4 space-y-2">
                            @foreach($results as $index => $contact)
                                <div @click="window.location.href = '/contacts/' + {{ $contact['id'] }}"
                                     class="group cursor-pointer select-none p-4 rounded-xl hover:bg-slate-50 {{ $selectedIndex === $index ? 'bg-indigo-50 border-2 border-indigo-200' : 'border-2 border-transparent' }} transition-all duration-200">
                                    <div class="flex items-center space-x-4">
                                        <!-- Premium Avatar -->
                                        <div class="relative">
                                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-200">
                                                <span class="text-sm font-bold text-white">{{ $contact['initials'] }}</span>
                                            </div>
                                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-sm border border-white">
                                                <div class="w-3 h-3 rounded-full {{ $contact['primary_source_color'] }}"></div>
                                            </div>
                                        </div>
                                        
                                        <!-- Contact Info -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between mb-1">
                                                <h4 class="text-base font-bold text-slate-900 truncate">{{ $contact['name'] }}</h4>
                                                <div class="flex flex-wrap gap-1">
                                                    @if(count($contact['sources']) > 1)
                                                        @foreach(array_slice($contact['sources'], 0, 2) as $sourceObj)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold {{ $sourceObj->getCssClass() }} shadow-sm">
                                                                {{ $sourceObj->getLabel() }}
                                                            </span>
                                                        @endforeach
                                                        @if(count($contact['sources']) > 2)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-bold bg-slate-200 text-slate-700">
                                                                +{{ count($contact['sources']) - 2 }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $contact['primary_source_color'] }} shadow-sm">
                                                            {{ $contact['primary_source'] }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="flex items-center space-x-4 text-sm text-slate-600">
                                                @if($contact['email'])
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                                        </svg>
                                                        <span class="font-medium">{{ $contact['email'] }}</span>
                                                    </div>
                                                @endif
                                                
                                                @if($contact['phone'])
                                                    <div class="flex items-center space-x-2">
                                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                        </svg>
                                                        <span class="font-medium">{{ $contact['phone'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            @if(!empty($contact['tags']))
                                                <div class="flex flex-wrap gap-1.5 mt-2">
                                                    @foreach(array_slice($contact['tags'], 0, 3) as $tag)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                                            <svg class="w-2.5 h-2.5 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                            </svg>
                                                            {{ $tag }}
                                                        </span>
                                                    @endforeach
                                                    @if(count($contact['tags']) > 3)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-slate-200 text-slate-600">
                                                            +{{ count($contact['tags']) - 3 }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <!-- Select Indicator -->
                                        <div class="opacity-0 group-hover:opacity-100 {{ $selectedIndex === $index ? 'opacity-100' : '' }} transition-opacity duration-200">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Global search modal management
    document.addEventListener('livewire:initialized', () => {
        // Listen for openSearch event
        window.addEventListener('openSearch', () => {
            // Find the GlobalSearch component and set Alpine state
            const globalSearchModal = document.querySelector('[x-data*="selectedIndex"]');
            if (globalSearchModal && globalSearchModal._x_dataStack && globalSearchModal._x_dataStack[0]) {
                const alpineData = globalSearchModal._x_dataStack[0];
                
                // Force reset state completely
                alpineData.open = false;
                alpineData.searchTerm = '';
                alpineData.selectedIndex = -1;
                
                // Use setTimeout to ensure state is reset before opening
                setTimeout(() => {
                    alpineData.open = true;
                    
                    // Focus search input after modal opens
                    setTimeout(() => {
                        const input = document.querySelector('[x-ref="searchInput"]');
                        if (input) input.focus();
                    }, 50);
                }, 50);
            }
        });
    });
</script>