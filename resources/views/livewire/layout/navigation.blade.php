<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

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
                        <span class="ml-2 text-xl font-bold text-gray-900">WebShop</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')" wire:navigate class="text-sm font-medium">
                        {{ __('Shop') }}
                    </x-nav-link>
                    
                    @auth
                        @if(Auth::user()->isAdmin())
                            <x-nav-link :href="route('filament.admin.pages.dashboard')" :active="request()->routeIs('filament.admin.pages.dashboard')" class="text-sm font-medium">
                                {{ __('Admin Dashboard') }}
                            </x-nav-link>
                        @else
                            <x-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard')" wire:navigate class="text-sm font-medium">
                                {{ __('Dashboard') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Navigation Right Side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 space-x-4">
                <!-- Search Component -->
                <livewire:shop.search-bar />
                
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
                                @if(!Auth::user()->isAdmin())
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition duration-150 ease-in-out" wire:navigate>
                                    Your Profile
                                </a>
                                @endif
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
        <!-- Mobile Search -->
        <div class="pt-2 px-4">
            <form wire:submit.prevent="$dispatch('search-submitted', {search: $event.target.search.value})">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input 
                        name="search"
                        type="text" 
                        class="block w-full rounded-md border-0 py-2 pl-10 pr-16 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                        placeholder="Search products..."
                    >
                    <div class="absolute inset-y-0 right-0 flex py-1.5 pr-1.5">
                        <button 
                            type="submit" 
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium text-white bg-indigo-600 hover:bg-indigo-700"
                        >
                            Search
                        </button>
                    </div>
                </div>
                <div class="mt-1 text-xs text-gray-500">
                    Leave empty to show all products
                </div>
            </form>
        </div>
        
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.index')" wire:navigate>
                {{ __('Shop') }}
            </x-responsive-nav-link>
            
            @auth
                @if(Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('filament.admin.pages.dashboard')" :active="request()->routeIs('filament.admin.pages.dashboard')">
                        {{ __('Admin Dashboard') }}
                    </x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('customer.dashboard')" :active="request()->routeIs('customer.dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-responsive-nav-link>
                @endif
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
                    @if(!Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>
                    @endif

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
