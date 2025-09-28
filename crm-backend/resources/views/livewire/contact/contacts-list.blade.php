<div>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Success Message -->
        @if (session('success'))
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
                        <p class="text-base font-bold text-emerald-900">{{ session('success') }}</p>
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
        @endif

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
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200/60 shadow-sm h-32 flex items-center">
                            <div class="flex items-center space-x-4 w-full">
                                <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-600">Total</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $contacts->total() ?? 0 }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200/60 shadow-sm h-32 flex items-center">
                            <div class="flex items-center space-x-4 w-full">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-600">CRM</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $contacts->filter(fn($c) => in_array('crm', $c->sources ?? []))->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200/60 shadow-sm h-32 flex items-center">
                            <div class="flex items-center space-x-4 w-full">
                                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-600">Telegram</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $contacts->filter(fn($c) => in_array('telegram', $c->sources ?? []))->count() }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200/60 shadow-sm h-32 flex items-center">
                            <div class="flex items-center space-x-4 w-full">
                                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-base font-semibold text-slate-600">WhatsApp</p>
                                    <p class="text-2xl font-bold text-slate-900">{{ $contacts->filter(fn($c) => in_array('whatsapp', $c->sources ?? []))->count() }}</p>
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
                        <button @if($hasActiveAccounts) wire:click="manualSync" @endif
                                @click="open = false"
                                @if(!$hasActiveAccounts) disabled @endif
                                class="w-full flex items-center px-6 py-4 text-left transition-all duration-200 group @if($hasActiveAccounts) text-slate-700 hover:bg-blue-50/80 hover:text-blue-700 cursor-pointer @else text-slate-400 cursor-not-allowed @endif">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-4 transition-transform duration-200 @if($hasActiveAccounts) bg-gradient-to-r from-blue-500 to-indigo-600 group-hover:scale-110 @else bg-gradient-to-r from-slate-300 to-slate-400 @endif">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="font-semibold text-base">Sync Contacts</div>
                                <div class="text-sm transition-colors duration-200 @if($hasActiveAccounts) text-slate-500 group-hover:text-blue-600 @else text-slate-400 @endif">
                                    @if($hasActiveAccounts)
                                        Manually sync from all accounts
                                    @else
                                        No accounts connected
                                    @endif
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
                                    @if(!empty($sourceFilters))
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                            {{ count($sourceFilters) }}
                                        </span>
                                    @endif
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
                                            @if(empty($sourceFilters))
                                                <span class="text-slate-500 text-sm">Select sources...</span>
                                            @else
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($sourceFilters as $selectedSource)
                                                        @php $sourceObj = \App\Shared\Enums\ContactSource::from($selectedSource); @endphp
                                                        <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                            <div class="w-3 h-3 rounded-sm {{ $sourceObj->getCssClass() }} flex items-center justify-center mr-1">
                                                                <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 24 24">
                                                                    <path d="{{ $sourceObj->getIcon() }}"></path>
                                                                </svg>
                                                            </div>
                                                            {{ $sourceObj->getLabel() }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
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
        @livewire('integration.integration-errors')

        <!-- Premium Contact Cards -->
        <div class="space-y-4">
                        @forelse($contacts as $contact)
                <div wire:key="contact-{{ $contact->id }}" class="group bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-sm hover:shadow-xl hover:border-slate-300/80 transition-all duration-300 p-6">
                    <div class="flex items-center justify-between">
                        <!-- Contact Info -->
                        <div class="flex items-center space-x-4 flex-1">
                            <!-- Avatar -->
                            <div class="relative">
                                <div class="w-14 h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-shadow duration-300">
                                    <span class="text-lg font-bold text-white">{{ $contact->initials }}</span>
                                                </div>
                                <!-- Source Indicators -->
                                @if(count($contact->sources) > 1)
                                    <div class="absolute -bottom-2 -right-2 flex space-x-1">
                                        @foreach(array_slice($contact->sourceObjects, 0, 3) as $index => $sourceObj)
                                            <div class="w-5 h-5 {{ $sourceObj->getCssClass() }} rounded-full flex items-center justify-center shadow-sm border border-white" 
                                                 style="z-index: {{ 10 - $index }}; margin-left: {{ $index * -8 }}px;">
                                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="{{ $sourceObj->getIcon() }}"></path>
                                                    </svg>
                                            </div>
                                        @endforeach
                                        @if(count($contact->sources) > 3)
                                            <div class="w-5 h-5 bg-slate-600 text-white rounded-full flex items-center justify-center shadow-sm border border-white text-xs font-bold"
                                                 style="margin-left: -8px;">
                                                +{{ count($contact->sources) - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif(count($contact->sources) === 1)
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-white rounded-full flex items-center justify-center shadow-sm border-2 border-white">
                                        <div class="w-4 h-4 {{ $contact->primarySource->getCssClass() }} rounded-full flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="{{ $contact->primarySource->getIcon() }}"></path>
                                            </svg>
                                        </div>
                                    </div>
                                @else
                                    <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-slate-100 rounded-full flex items-center justify-center shadow-sm border-2 border-white">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Contact Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h3 class="text-lg font-bold text-slate-900 truncate">{{ $contact->name }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @if(empty($contact->sourceObjects))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                No source
                                    </span>
                                        @else
                                            @foreach($contact->sourceObjects as $sourceObj)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold {{ $sourceObj->getCssClass() }} shadow-sm">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="{{ $sourceObj->getIcon() }}"></path>
                                                    </svg>
                                                    {{ $sourceObj->getLabel() }}
                                                </span>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 space-y-1 sm:space-y-0 text-sm text-slate-600">
                                    @if($contact->email)
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                            </svg>
                                            <span class="font-medium">{{ $contact->email }}</span>
                                        </div>
                                            @endif
                                    
                                    @if($contact->phone)
                                        <div class="flex items-center space-x-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
                                            <span class="font-medium">{{ $contact->phone }}</span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Tags -->
                                @if($contact->tags && count($contact->tags) > 0)
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach(array_slice($contact->tags, 0, 4) as $tag)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 shadow-sm">
                                                <svg class="w-3 h-3 mr-1 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                                </svg>
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                        @if(count($contact->tags) > 4)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-slate-200 text-slate-600 border border-slate-300">
                                                +{{ count($contact->tags) - 4 }} more
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex items-center space-x-3">
                            <button wire:click="editContact({{ $contact->id }})" 
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
                                    <button wire:click="confirmDelete({{ $contact->id }})"
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
            @empty
                <div class="text-center py-16">
                    <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-12 h-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No contacts found</h3>
                    <p class="text-slate-600 mb-6 max-w-md mx-auto">
                        @if($search || !empty($sourceFilters))
                            Try adjusting your search or filter criteria, or add a new contact to get started.
                        @else
                            Get started by adding your first contact to begin building your network.
                        @endif
                    </p>
                    <button wire:click="openContactForm" 
                            class="inline-flex items-center px-6 py-3 text-sm font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                        </svg>
                        Add Your First Contact
                    </button>
                </div>
                        @endforelse
        </div>

        <!-- Premium Pagination -->
        @if($contacts->hasPages())
            <div class="mt-8 flex justify-center">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl border border-slate-200/60 shadow-lg p-2">
                {{ $contacts->links('vendor.pagination.custom-tailwind') }}
                </div>
            </div>
        @endif
    </div>

    <!-- Premium Delete Confirmation Modal -->
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ show: @entangle('showDeleteModal') }"
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
                            <span class="font-bold text-slate-900">"{{ $contactToDeleteName }}"</span>? 
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
    @endif

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
        @this.call('startPolling');
    }, 2000);
}

// Auto-refresh polling for new contacts
let pollingInterval;

function startContactsPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
    
    pollingInterval = setInterval(() => {
        @this.call('checkForNewContacts');
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
    @this.call('stopPolling');
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
        
        @foreach($sources as $value => $label)
            @php $sourceObj = \App\Shared\Enums\ContactSource::from($value); @endphp
            <label class="flex items-center px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors duration-200"
                   onclick="event.stopPropagation();">
                <input type="checkbox" 
                       wire:model.live="sourceFilters" 
                       value="{{ $value }}"
                       class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                <div class="ml-3 flex items-center space-x-3">
                    <div class="w-5 h-5 rounded-md {{ $sourceObj->getCssClass() }} flex items-center justify-center shadow-sm">
                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="{{ $sourceObj->getIcon() }}"></path>
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-900">{{ $label }}</span>
                </div>
            </label>
        @endforeach
        
        @if(!empty($sourceFilters))
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
        @endif
    </div>

    </div>
</div>