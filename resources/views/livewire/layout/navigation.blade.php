<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false, userDropdown: false }" class="bg-white border-b border-gray-100 shadow sticky top-0 z-50">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('shop.index') }}" wire:navigate class="flex items-center">
                        <x-application-logo class="block h-9 w-auto fill-current text-indigo-600" />
                        <span class="ml-2 text-xl font-bold text-gray-900">WebShop</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')" wire:navigate class="text-sm font-medium">
                        {{ __('Shop') }}
                    </x-nav-link>
                    
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="text-sm font-medium">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                    @endauth
                </div>
            </div>

            <!-- Navigation Right Side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Search Button -->
                <button class="text-gray-500 hover:text-gray-700 focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                
                @auth
                    <!-- User Account Dropdown -->
                    <div class="ml-3 relative">
                        <div>
                            <button @click="userDropdown = !userDropdown" @click.away="userDropdown = false" class="max-w-xs flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 focus:outline-none transition duration-150 ease-in-out">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                        {{ substr(auth()->user()->name, 0, 1) }}
                                    </div>
                                    <span class="ml-2" x-data="{{ json_encode(['name' => auth()->user() ? auth()->user()->name : 'Guest']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></span>
                                    <svg class="ml-1 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </div>
                        <div x-show="userDropdown" 
                             x-transition:enter="transition ease-out duration-100" 
                             x-transition:enter-start="transform opacity-0 scale-95" 
                             x-transition:enter-end="transform opacity-100 scale-100" 
                             x-transition:leave="transition ease-in duration-75" 
                             x-transition:leave-start="transform opacity-100 scale-100" 
                             x-transition:leave-end="transform opacity-0 scale-95" 
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg">
                            <div class="py-1 rounded-md bg-white shadow-xs">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out" wire:navigate>
                                    Your Profile
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    Settings
                                </a>
                                <button wire:click="logout" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out">
                                    Sign out
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex space-x-4">
                        <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" wire:navigate>
                            {{ __('Login') }}
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-medium text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" wire:navigate>
                            {{ __('Register') }}
                        </a>
                    </div>
                @endauth
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')" wire:navigate>
                {{ __('Shop') }}
            </x-responsive-nav-link>
            
            @auth
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
            @endauth
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="px-4">
                    <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user() ? auth()->user()->name : 'Guest']) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                    <div class="font-medium text-sm text-gray-500">{{ auth()->user() ? auth()->user()->email : '' }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
                </div>
            </div>
        @else
            <div class="pt-4 pb-1 border-t border-gray-200">
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" wire:navigate>
                        {{ __('Login') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" wire:navigate>
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            </div>
        @endauth
    </div>
</nav>
