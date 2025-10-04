<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Integration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full">
            <!-- Main Card -->
            <div class="bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 p-8 text-center">
                
                <!-- Loading Animation -->
                <div class="mb-6">
                    <div class="w-16 h-16 mx-auto bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center animate-pulse">
                        <svg class="w-8 h-8 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </div>
                </div>

                <!-- Title -->
                <h1 class="text-2xl font-bold text-slate-900 mb-2">
                    Connecting Account
                </h1>
                
                <!-- Status Message -->
                <p id="status-message" class="text-slate-600 mb-6">
                    Please complete the authentication process in the popup window...
                </p>

                <!-- Progress Indicator -->
                <div class="mb-6">
                    <div class="w-full bg-slate-200 rounded-full h-2">
                        <div id="progress-bar" class="bg-gradient-to-r from-blue-500 to-indigo-600 h-2 rounded-full transition-all duration-500" style="width: 10%"></div>
                    </div>
                </div>

                <!-- Cancel Button -->
                <button onclick="window.location.href='{{ route('contacts') }}'" 
                        class="px-6 py-3 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl border border-slate-200 hover:border-slate-300 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2 transition-all duration-200">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <script>
        let checkCount = 0;
        const maxChecks = 60; // 5 минут (60 * 5 секунд)
        
        function updateProgress() {
            checkCount++;
            const progress = Math.min((checkCount / maxChecks) * 100, 90);
            document.getElementById('progress-bar').style.width = progress + '%';
        }

        function updateStatus(message) {
            document.getElementById('status-message').textContent = message;
        }

        function checkIntegrationStatus() {
            updateProgress();
            
            fetch('{{ route('integration.check-status') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Integration status:', data);
                
                if (data.status === 'success') {
                    updateStatus('Integration completed! Redirecting...');
                    document.getElementById('progress-bar').style.width = '100%';
                    
                    // Set flag for contacts page to start polling
                    sessionStorage.setItem('integration_completed', 'true');
                    
                    setTimeout(() => {
                        window.location.href = data.redirect || '{{ route('contacts') }}';
                    }, 2000);
                    
                } else if (data.status === 'no_pending') {
                    updateStatus('No pending integration found. Redirecting...');
                    setTimeout(() => {
                        window.location.href = '{{ route('contacts') }}';
                    }, 2000);
                    
                } else if (data.status === 'pending') {
                    updateStatus('Waiting for account connection...');
                    
                    if (checkCount < maxChecks) {
                        setTimeout(checkIntegrationStatus, 5000); // Check every 5 seconds
                    } else {
                        updateStatus('Connection timeout. Please try again.');
                        setTimeout(() => {
                            window.location.href = '{{ route('contacts') }}';
                        }, 3000);
                    }
                    
                } else if (data.status === 'error') {
                    updateStatus('Error checking integration status. Please try again.');
                    setTimeout(() => {
                        window.location.href = '{{ route('contacts') }}';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                updateStatus('Connection error. Please try again.');
                setTimeout(() => {
                    window.location.href = '{{ route('contacts') }}';
                }, 3000);
            });
        }

        // Start checking after 3 seconds (give time for user to complete auth)
        setTimeout(checkIntegrationStatus, 3000);
    </script>
</body>
</html>
