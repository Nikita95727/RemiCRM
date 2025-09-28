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
    </head>
    <body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-white to-slate-50">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
            <!-- Premium Logo -->
            <div class="mb-8">
                <a href="/" wire:navigate class="flex items-center space-x-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl shadow-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">R</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">Remi CRM</h1>
                        <p class="text-sm text-slate-600 -mt-1">Personal Contact Manager</p>
                    </div>
                </a>
            </div>

            <!-- Premium Card -->
            <div class="w-full sm:max-w-md bg-white/95 backdrop-blur-xl shadow-2xl border border-slate-200/60 rounded-2xl overflow-hidden">
                <div class="px-8 py-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
