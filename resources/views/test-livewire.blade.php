@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Livewire Test Page</h1>
        
        <!-- Test Basic Livewire -->
        <div class="bg-white rounded-xl p-6 mb-6 shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Test 1: Basic Livewire Component</h2>
            <livewire:contact.contacts-list />
        </div>

        <!-- Test Connect Modal -->
        <div class="bg-white rounded-xl p-6 mb-6 shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Test 2: Connect Account Modal</h2>
            
            <div class="mb-4 p-4 bg-gray-100 rounded">
                <p><strong>Component Test:</strong></p>
                <livewire:integration.connect-account wire:id="connect-account-component" />
                <p class="text-green-600">✅ ConnectAccount component should be above</p>
                
                <!-- Debug: Check if modal exists -->
                <div class="mt-2 p-2 bg-yellow-100 rounded text-xs">
                    <p><strong>Debug:</strong> Check browser inspector for modal HTML. Modal should be hidden by default.</p>
                    <p><strong>showModal value:</strong> Check Livewire component state in browser devtools.</p>
                </div>
            </div>
            
            <button onclick="window.Livewire.dispatch('openConnectModal')" 
                    class="mt-4 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Test Open Modal (JavaScript)
            </button>
            
            <button wire:click="$dispatch('openConnectModal')" 
                    class="mt-4 ml-4 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Test Open Modal (Livewire)
            </button>
            
            <!-- Direct component method test -->
            <button onclick="window.Livewire.find('connect-account-component')?.call('openModal')" 
                    class="mt-4 ml-4 px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                Direct Method Call
            </button>
            
            <!-- JavaScript test function -->
            <button onclick="window.testDispatch()" 
                    class="mt-4 ml-4 px-6 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                Test JS Function
            </button>
        </div>

        <!-- Debug Info -->
        <div class="bg-white rounded-xl p-6 shadow-lg">
            <h2 class="text-xl font-semibold mb-4">Debug Info</h2>
            <div class="space-y-2 text-sm">
                <p><strong>Livewire loaded:</strong> <span id="livewire-status">Checking...</span></p>
                <p><strong>Alpine loaded:</strong> <span id="alpine-status">Checking...</span></p>
                <p><strong>User ID:</strong> {{ auth()->id() }}</p>
                <p><strong>CSRF Token:</strong> {{ csrf_token() }}</p>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Check Livewire
        if (typeof window.Livewire !== 'undefined') {
            document.getElementById('livewire-status').textContent = '✅ Loaded';
            document.getElementById('livewire-status').className = 'text-green-600';
        } else {
            document.getElementById('livewire-status').textContent = '❌ Not loaded';
            document.getElementById('livewire-status').className = 'text-red-600';
        }
        
        // Check Alpine
        if (typeof window.Alpine !== 'undefined') {
            document.getElementById('alpine-status').textContent = '✅ Loaded';
            document.getElementById('alpine-status').className = 'text-green-600';
        } else {
            document.getElementById('alpine-status').textContent = '❌ Not loaded';
            document.getElementById('alpine-status').className = 'text-red-600';
        }
        
        // Debug Livewire events
        console.log('=== Livewire Debug ===');
        console.log('Livewire object:', window.Livewire);
        
        // Test event dispatch
        window.testDispatch = function() {
            console.log('Testing Livewire.dispatch...');
            try {
                window.Livewire.dispatch('openConnectModal');
                console.log('✅ Event dispatched successfully');
            } catch (e) {
                console.error('❌ Event dispatch failed:', e);
            }
        };
        
        // Find all Livewire components
        if (window.Livewire && window.Livewire.all) {
            console.log('All Livewire components:', window.Livewire.all());
        }
    });
</script>
@endsection
