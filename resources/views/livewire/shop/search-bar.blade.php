<div class="relative" x-data="{ focused: false }">
    
    <!-- Search toggle button -->
    <button 
        wire:click="toggleSearch" 
        type="button" 
        class="text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        :class="{ 'hidden': $wire.isActive }"
    >
        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <span class="sr-only">Search</span>
    </button>
    
    <!-- Search input -->
    <div 
        x-show="$wire.isActive"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95"
        x-transition:enter-end="opacity-100 transform scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100"
        x-transition:leave-end="opacity-0 transform scale-95"
        class="absolute right-0 top-0 mt-8 w-64 sm:w-80 z-10"
        @click.away="$wire.isActive = false"
    >
        <form wire:submit.prevent="submitSearch" class="relative">
            <div class="overflow-hidden rounded-lg shadow-lg ring-1 ring-black ring-opacity-5">
                <div class="relative bg-white p-2">
                    <div class="flex items-center">
                        <div class="w-full">
                            <div class="flex items-center relative rounded-md shadow-sm">
                                <!-- Search icon on left -->
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                
                                <!-- Input field -->
                                <input 
                                    wire:model.live.debounce.300ms="search" 
                                    type="text" 
                                    class="block w-full rounded-md border-0 py-2 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm" 
                                    placeholder="Search products..."
                                    @focus="focused = true"
                                    @blur="focused = false"
                                    x-ref="searchInput"
                                    x-init="$nextTick(() => { $refs.searchInput.focus() })"
                                    autocomplete="off"
                                >
                                
                                <!-- Right side actions container -->
                                <div class="absolute inset-y-0 right-0 flex items-center space-x-1 pr-2 left-auto">
                                    <!-- Loading indicator -->
                                    <div wire:loading wire:target="search">
                                        <svg class="animate-spin h-4 w-4 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                    
                                    <!-- Clear button -->
                                    <button 
                                        wire:loading.remove
                                        wire:target="search"
                                        wire:click="clearSearch" 
                                        type="button"
                                        class="text-gray-400 hover:text-gray-500 focus:outline-none left-auto {{ empty($search) ? 'opacity-0' : 'opacity-100' }}"
                                    >
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit button -->
                        <button 
                            type="submit" 
                            class="ml-2 flex-shrink-0 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                        >
                            Search
                        </button>
                    </div>
                    
                    <!-- Search hint -->
                    <div class="mt-2 text-xs text-gray-500">
                        Search by product name, description, or category
                    </div>
                </div>
            </div>
        </form>
    </div>
</div> 