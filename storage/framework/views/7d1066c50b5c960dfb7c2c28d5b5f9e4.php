<div>
    <!-- Premium Command+K Search Modal -->
    <div x-data="{ 
        open: false,
        selectedIndex: -1,
        searchTerm: '',
        results: [],
        
        closeModal() {
            this.open = false;
            this.searchTerm = '';
            this.selectedIndex = -1;
            this.results = [];
        },
        
        async performSearch() {
            if (this.searchTerm.length < 2) {
                this.results = [];
                return;
            }
            
            try {
                // Simple fetch to search endpoint
                const response = await fetch(`/contacts/search?q=${encodeURIComponent(this.searchTerm)}`);
                if (response.ok) {
                    this.results = await response.json();
                    console.log('Search results:', this.results);
                } else {
                    this.results = [];
                }
            } catch (error) {
                console.error('Search error:', error);
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
            // Do nothing - just for keyboard navigation visual feedback
        }
    }"
    @keydown.escape.window="closeModal()"
    @keydown.cmd.k.window.prevent="open = true; $nextTick(() => $refs.searchInput.focus())"
    @keydown.ctrl.k.window.prevent="open = true; $nextTick(() => $refs.searchInput.focus())"
    @keydown.arrow-down.prevent="selectNext()"
    @keydown.arrow-up.prevent="selectPrevious()"
    @keydown.enter.prevent="selectCurrentContact()"
    class="relative z-50">
        
        <!-- Modal Backdrop -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-500/75 backdrop-blur-sm transition-opacity">
        </div>

        <!-- Modal Dialog -->
        <div x-show="open" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="fixed inset-0 z-10 w-screen overflow-y-auto p-4 sm:p-6 md:p-20">
            
            <div class="mx-auto max-w-3xl transform overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200/60 transition-all">
                
                <!-- Search Header -->
                <div class="relative bg-slate-50 border-b border-slate-200 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                            <svg class="w-5 h-5 text-white" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-slate-900">Quick Search</h3>
                            <p class="text-sm text-slate-600 mt-1">Find contacts instantly</p>
                        </div>
                        <button @click="closeModal()" 
                                class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Search Input -->
                <div class="relative p-6 pb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-6 w-6 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="searchTerm"
                               @input.debounce.300ms="performSearch()"
                               class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-xl text-slate-900 placeholder:text-slate-500 focus:bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 text-lg font-medium shadow-sm transition-all duration-300" 
                               placeholder="Search contacts by name, email, phone, tags..." 
                               x-ref="searchInput">
                    </div>
                </div>
                
                <!-- Search Results -->
                <div class="max-h-96 overflow-y-auto">
                    <!-- Empty State -->
                    <div x-show="results.length === 0 && searchTerm.length === 0" class="p-8 text-center">
                        <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">Start searching</h4>
                        <p class="text-slate-600">Type to find contacts by name, email, phone, or tags</p>
                    </div>
                    
                    <!-- No Results -->
                    <div x-show="results.length === 0 && searchTerm.length >= 2" class="p-8 text-center">
                        <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6-4h6" />
                            </svg>
                        </div>
                        <h4 class="text-lg font-bold text-slate-900 mb-2">No contacts found</h4>
                        <p class="text-slate-600 mb-4">No results for "<span class="font-semibold text-slate-900" x-text="searchTerm"></span>"</p>
                    </div>
                    
                    <!-- Results -->
                    <div x-show="results.length > 0" class="p-4 space-y-2">
                        <template x-for="(contact, index) in results" :key="contact.id">
                            <div :class="selectedIndex === index ? 'bg-indigo-50 border-indigo-200' : 'border-transparent hover:bg-slate-50'"
                                 class="p-4 rounded-xl border-2 transition-all duration-200">
                                <div class="flex items-center space-x-4">
                                    <!-- Avatar -->
                                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                                        <span class="text-sm font-bold text-white" x-text="contact.initials"></span>
                                    </div>
                                    
                                    <!-- Contact Info -->
                                    <div class="flex-1 min-w-0">
                                        <!-- Name and Source -->
                                        <div class="flex items-center justify-between mb-2">
                                            <h4 class="text-base font-bold text-slate-900 truncate" x-text="contact.name"></h4>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-slate-100 text-slate-600" x-text="contact.primary_source"></span>
                                        </div>
                                        
                                        <!-- Contact Details -->
                                        <div class="space-y-1 text-sm text-slate-600">
                                            <!-- Email -->
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                                </svg>
                                                <span class="font-medium truncate" x-text="contact.email || '—'"></span>
                                            </div>
                                            
                                            <!-- Phone -->
                                            <div class="flex items-center space-x-2">
                                                <svg class="w-3 h-3 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                                <span class="font-medium truncate" x-text="contact.phone || '—'"></span>
                                            </div>
                                            
                                            <!-- Tags -->
                                            <div class="flex items-start space-x-2">
                                                <svg class="w-3 h-3 text-slate-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                <div class="flex-1 min-w-0">
                                                    <template x-if="contact.tags && contact.tags.length > 0">
                                                        <div class="flex flex-wrap gap-1">
                                                            <template x-for="tag in contact.tags.slice(0, 3)" :key="tag">
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800" x-text="tag"></span>
                                                            </template>
                                                            <span x-show="contact.tags.length > 3" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600" x-text="'+' + (contact.tags.length - 3)"></span>
                                                        </div>
                                                    </template>
                                                    <template x-if="!contact.tags || contact.tags.length === 0">
                                                        <span class="text-slate-500 font-medium">—</span>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
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
                alpineData.results = [];
                
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
</script><?php /**PATH /Users/macbook/Documents/Remi CRM/crm-backend/resources/views/livewire/contact/global-search.blade.php ENDPATH**/ ?>