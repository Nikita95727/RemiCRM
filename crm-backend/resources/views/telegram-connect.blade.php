@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 p-8">
    <div class="max-w-2xl mx-auto">
        <h1 class="text-3xl font-bold text-slate-900 mb-8">Connect Telegram Account</h1>
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-100 border border-green-200 rounded-xl text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-100 border border-red-200 rounded-xl text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-2xl mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-900 mb-2">Connect Your Telegram</h2>
                <p class="text-slate-600">We'll use Unipile's Hosted Auth Wizard to securely connect your Telegram account</p>
            </div>

            <form action="{{ route('telegram.connect') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="text-center">
                    <button type="submit" 
                            class="inline-flex items-center px-8 py-4 text-lg font-bold text-white bg-gradient-to-r from-blue-500 to-cyan-600 hover:from-blue-600 hover:to-cyan-700 rounded-2xl shadow-xl hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-300 transform hover:scale-105">
                        <svg class="w-6 h-6 mr-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Connect Telegram Account
                    </button>
                </div>

                <div class="text-center text-sm text-slate-500">
                    <p>You'll be redirected to Unipile's secure authentication page</p>
                </div>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-200">
                <a href="{{ route('contacts') }}" 
                   class="inline-flex items-center text-slate-600 hover:text-slate-900 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to Contacts
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
