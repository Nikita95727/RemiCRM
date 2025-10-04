<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Remi CRM</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- Alpine.js x-cloak styles -->
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-slate-50">
        @auth
            <!-- Premium Navigation -->
            <nav class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-slate-200/60 shadow-sm">
                <div class="mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Logo, Brand and Navigation -->
                        <div class="flex items-center space-x-8">
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-lg shadow-lg flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">R</span>
                                </div>
                                <div>
                                    <h1 class="text-xl font-bold text-slate-900">Remi CRM</h1>
                                    <p class="text-xs text-slate-600 -mt-1">Personal Contact Manager</p>
                                </div>
                            </div>
                            
                            <!-- Navigation Links -->
                            <div class="hidden md:flex items-center">
                                <a href="{{ route('contacts') }}" 
                                   class="text-slate-700 hover:text-indigo-600 px-3 py-2 rounded-lg text-sm font-semibold transition-colors duration-200 {{ request()->routeIs('contacts') ? 'text-indigo-600 bg-indigo-50' : '' }}">
                                    Contacts
                                </a>
                            </div>
                        </div>

                        <!-- Search and Profile -->
                        <div class="flex items-center space-x-4">
                            <!-- Premium Search Button -->
                            <button onclick="window.dispatchEvent(new CustomEvent('openSearch'))" 
                                    class="group inline-flex items-center px-4 py-2 text-sm text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl border border-slate-200 hover:border-slate-300 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Search
                                <span class="ml-2 text-xs text-slate-500 font-mono">⌘K</span>
                            </button>

                            <!-- Profile Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="flex items-center space-x-2 text-sm text-slate-700 hover:text-slate-900 focus:outline-none">
                                    <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center shadow-sm">
                                        <span class="text-white font-semibold text-xs">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <span class="font-medium">{{ auth()->user()->name }}</span>
                                    <svg class="w-4 h-4 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" 
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     @click.away="open = false"
                                     class="absolute right-0 mt-2 w-48 bg-white/95 backdrop-blur-xl rounded-xl shadow-2xl border border-slate-200/60 py-2 z-50">
                                    
                                    <a href="{{ route('profile') }}" 
                                       class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition-colors duration-200">
                                        Profile
                                    </a>
                                    
                                    <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors duration-200">
                                            Sign Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        @endauth

        <!-- Global Search Component -->
        @auth
            @livewire('contact.global-search')
        @endauth

        <!-- Page Content -->
        <main>
            @yield('content')
            {{ $slot ?? '' }}
        </main>

        @livewireScripts
        
        <!-- Global Search Keyboard Shortcut -->
        <script>
            // Multiple event listeners for better capture
            document.addEventListener('keydown', function(e) {
                if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    
                    window.dispatchEvent(new CustomEvent('openSearch'));
                    return false;
                }
            }, true); // Use capture phase
            
            // Additional keyup listener as backup
            document.addEventListener('keyup', function(e) {
                if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    e.stopPropagation();
                    return false;
                }
            }, true);
            
            // Window level listener as last resort
            window.addEventListener('keydown', function(e) {
                if ((e.metaKey || e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                    e.preventDefault();
                    e.stopPropagation();
                    window.dispatchEvent(new CustomEvent('openSearch'));
                    return false;
                }
            }, true);
            
            // Global Contact Form Event Handler
            window.addEventListener('open-contact-form', function() {
                // Найти компонент ContactForm по данным snapshot
                const allComponents = window.Livewire.all();
                
                // Ищем компонент с isOpen и isEdit свойствами (это ContactForm)
                const contactFormComponent = allComponents.find(component => {
                    try {
                        const data = component.snapshot?.data || {};
                        return data.hasOwnProperty('isOpen') && data.hasOwnProperty('isEdit');
                    } catch (e) {
                        return false;
                    }
                });
                
                if (contactFormComponent && contactFormComponent.$wire && contactFormComponent.$wire.openForm) {
                    contactFormComponent.$wire.openForm();
                }
            });
        </script>
    </body>
</html>
