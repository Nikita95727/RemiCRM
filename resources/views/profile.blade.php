<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- User Information -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                    <div class="space-y-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name</label>
                            <p class="mt-1 text-sm text-gray-900">{{ auth()->user()->name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <p class="mt-1 text-sm text-gray-900">{{ auth()->user()->email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email Verified</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ auth()->user()->email_verified_at ? 'Yes' : 'No' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Two-Factor Authentication</h3>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">2FA Status</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if(auth()->user()->hasEnabledTwoFactorAuthentication())
                                    <span class="text-green-600 font-medium">Enabled</span>
                                @else
                                    <span class="text-red-600 font-medium">Disabled</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">2FA Confirmed</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if(auth()->user()->hasConfirmedTwoFactorAuthentication())
                                    <span class="text-green-600 font-medium">Confirmed</span>
                                @else
                                    <span class="text-red-600 font-medium">Not Confirmed</span>
                                @endif
                            </p>
                        </div>
                        <div class="pt-4">
                            <a href="{{ route('two-factor.index') }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Manage Two-Factor Authentication
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
