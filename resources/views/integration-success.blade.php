@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-50 py-12">
    <div class="max-w-md mx-auto">
        <div class="bg-white/95 backdrop-blur-xl shadow-2xl border border-slate-200/60 rounded-2xl overflow-hidden">
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-emerald-600 via-green-600 to-emerald-700 px-6 py-8 text-center">
                <div class="w-16 h-16 mx-auto bg-white/20 rounded-2xl flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Account Connected!</h1>
                <p class="text-emerald-100">Your Telegram account has been successfully connected to Remi CRM.</p>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex items-center p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                        <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold text-emerald-900">Telegram</h3>
                            <p class="text-sm text-emerald-700">Ready for contact synchronization</p>
                        </div>
                    </div>

                    <div class="text-center space-y-4">
                        <p class="text-slate-600">
                            Your contacts will be synchronized automatically. You can now close this window and return to your CRM.
                        </p>
                        
                        <a href="{{ route('contacts') }}" 
                           class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                            </svg>
                            Back to Contacts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
