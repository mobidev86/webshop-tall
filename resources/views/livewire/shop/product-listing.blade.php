<div>
    <div class="bg-gradient-to-b from-slate-50 to-white" 
        x-data="{ 
            isLoading: @entangle('isLoading')
        }"
    >
        <!-- Global loading overlay -->
        <div 
            wire:loading.delay.longer
            class="fixed inset-0 bg-slate-900/20 backdrop-blur-sm z-50 flex items-center justify-center"
        >
            <div class="bg-white p-4 rounded-lg shadow-lg flex items-center space-x-4">
                <svg class="animate-spin h-8 w-8 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-slate-800 font-medium">Loading...</span>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Hero section with search -->
            <div class="pt-20 pb-10">
                <h1 class="text-4xl font-serif font-bold tracking-tight text-gray-900 mb-2 text-center">Premium Collection</h1>
                <p class="text-center text-gray-600 mb-10 max-w-2xl mx-auto">Discover our curated selection of exceptional products crafted with the finest materials and attention to detail.</p>
                
                <!-- Main search bar -->
                <div class="max-w-2xl mx-auto mb-12">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="block w-full rounded-md border-0 py-3 pl-11 pr-20 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-slate-600 sm:text-md shadow-sm"
                            placeholder="{{ $searchPlaceholder }}"
                            autocomplete="off"
                        >
                        <!-- Search indicator -->
                        <div class="absolute inset-y-0 right-0 left-auto flex items-center pr-3">
                            <div wire:loading wire:target="search">
                                <svg class="animate-spin h-5 w-5 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <button 
                                wire:click="$set('search', '')" 
                                class="{{ $search ? 'visible' : 'invisible' }} ml-2 text-gray-400 hover:text-gray-500"
                                type="button"
                            >
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @if($search)
                        <div class="mt-2 text-sm text-gray-600 text-center">
                            Searching for: <span class="font-medium">{{ $search }}</span>
                            <span wire:loading wire:target="search" class="ml-1 text-slate-600">(searching...)</span>
                        </div>
                    @endif
                </div>
                
                <!-- Filters control bar -->
                <div class="flex flex-wrap items-center justify-between border-b border-gray-200 pb-6">
                    <div class="flex items-center flex-wrap gap-3">
                        <button 
                            wire:click="resetFilters" 
                            class="text-sm text-slate-600 hover:text-slate-800 flex items-center space-x-1 transition duration-150"
                            wire:loading.class="opacity-50 cursor-wait"
                            wire:target="resetFilters"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span>Reset Filters</span>
                        </button>
                        
                        <!-- Show selected category badge -->
                        @if($selectedCategory)
                            <div class="inline-flex items-center gap-1 px-3 py-1 bg-slate-100 text-slate-800 text-xs font-medium rounded-full">
                                <span>
                                    {!! html_entity_decode($categories->firstWhere('id', $selectedCategory)->name) !!}
                                </span>
                                <button 
                                    wire:click="clearCategory" 
                                    class="text-slate-600 hover:text-slate-800"
                                    wire:loading.class="opacity-50"
                                    wire:target="clearCategory"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700">Sort by:</span>
                        <div class="relative inline-block">
                            <select 
                                wire:change="updateSort($event.target.value)" 
                                id="sort-by" 
                                class="rounded-md border-gray-200 py-1.5 pl-3 pr-10 text-sm focus:border-slate-500 focus:outline-none focus:ring-slate-500"
                            >
                                <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                                <option value="price" {{ $sortBy === 'price' ? 'selected' : '' }}>Price</option>
                                <option value="created_at" {{ $sortBy === 'created_at' ? 'selected' : '' }}>Newest</option>
                            </select>
                        </div>
                        
                        <button 
                            wire:click="toggleSortDirection" 
                            type="button" 
                            class="p-1.5 text-gray-600 hover:text-gray-800 bg-white rounded-md border border-gray-200 shadow-sm transition duration-150 hover:border-gray-300"
                        >
                            @if($sortDirection === 'asc')
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            @endif
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-6 pb-24">
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-5">
                    <!-- Filters sidebar -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-6 bg-white rounded-md shadow-sm border border-gray-100 overflow-hidden">
                            <div class="bg-slate-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                                <h3 class="text-lg font-medium text-gray-900">Categories</h3>
                                <div wire:loading wire:target="selectedCategory, clearCategory, selectCategory" class="text-xs text-slate-600">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </div>
                            </div>
                            <ul class="p-4 space-y-1.5">
                                <li wire:key="category-all">
                                    <button 
                                        wire:click="clearCategory" 
                                        class="flex items-center w-full py-2 px-3 rounded-md transition-colors duration-150 {{ !$selectedCategory ? 'bg-slate-100 text-slate-900 font-medium' : 'text-gray-700 hover:bg-gray-50' }}"
                                        wire:loading.class="opacity-50 cursor-wait"
                                        wire:target="clearCategory"
                                    >
                                        All Categories
                                    </button>
                                </li>
                                @foreach($categories as $category)
                                    <li wire:key="category-{{ $category->id }}">
                                        <button 
                                            wire:click="selectCategory({{ $category->id }})" 
                                            class="flex items-center justify-between w-full py-2 px-3 rounded-md transition-colors duration-150 {{ $selectedCategory == $category->id ? 'bg-slate-100 text-slate-900 font-medium' : 'text-gray-700 hover:bg-gray-50' }}"
                                            wire:loading.class="opacity-50 cursor-wait"
                                            wire:target="selectCategory({{ $category->id }})"
                                        >
                                            <span>{!! html_entity_decode($category->name) !!}</span>
                                            @if($category->products_count)
                                                <span class="text-xs font-medium {{ $selectedCategory == $category->id ? 'bg-slate-200 text-slate-800' : 'bg-gray-100 text-gray-600' }} rounded-full px-2.5 py-0.5">{{ $category->products_count }}</span>
                                            @endif
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <!-- Product grid -->
                    <div class="lg:col-span-4">
                        <!-- Product grid content -->
                        <div>
                            @if($products->count())
                                <div class="grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:gap-x-10">
                                    @foreach($products as $product)
                                        <div wire:key="product-{{ $product->id }}" class="group relative bg-white overflow-hidden rounded-md shadow-sm transition-all duration-300 hover:shadow-md border border-gray-100">
                                            <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden bg-gray-50 xl:aspect-h-8 xl:aspect-w-7">
                                                @if($product->image)
                                                    <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="h-56 w-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                                                @else
                                                    <div class="h-56 w-full flex items-center justify-center text-gray-400 bg-gray-50">
                                                        <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                @endif
                                                
                                                <!-- Status badges -->
                                                <div class="absolute top-3 right-3 flex flex-col space-y-1.5">
                                                    @if($product->isOnSale())
                                                        <span class="inline-flex items-center rounded-sm bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-700 border border-red-100">
                                                            Sale
                                                        </span>
                                                    @endif
                                                    
                                                    @if(!$product->isInStock())
                                                        <span class="inline-flex items-center rounded-sm bg-gray-50 px-2.5 py-0.5 text-xs font-medium text-gray-700 border border-gray-100">
                                                            Out of Stock
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="p-6">
                                                <!-- Categories pills -->
                                                @if($product->categories->count())
                                                    <div class="mb-3 flex flex-wrap gap-1.5">
                                                        @foreach($product->categories->take(3) as $category)
                                                            <span class="inline-flex items-center rounded-sm bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-700 border border-slate-100">
                                                                {!! html_entity_decode($category->name) !!}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                
                                                <!-- Product name -->
                                                <h3 class="text-base font-medium text-gray-900 mb-3 font-serif">
                                                    @if($search)
                                                        {!! $this->highlightSearchTerm($product->name) !!}
                                                    @else
                                                        {{ $product->name }}
                                                    @endif
                                                </h3>
                                                
                                                <!-- Price display -->
                                                <div class="mb-5">
                                                    @if($product->isOnSale())
                                                        <div class="flex items-baseline">
                                                            <span class="text-lg font-semibold text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                                                            <span class="ml-2 text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-lg font-semibold text-gray-900">${{ number_format($product->price, 2) }}</span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Order form component -->
                                                <livewire:shop.product-order-form :product="$product" :wire:key="'order-form-'.$product->id" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <!-- Pagination -->
                                <div class="mt-12">
                                    {{ $products->links() }}
                                </div>
                            @else
                                <!-- No results state -->
                                <div class="text-center py-16 bg-white rounded-md shadow-sm border border-gray-200">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="mt-3 text-lg font-medium text-gray-900 font-serif">{!! $noResultsMessage !!}</h3>
                                    <div class="mt-8 flex flex-wrap justify-center gap-3">
                                        @if($search)
                                            <button 
                                                type="button" 
                                                wire:click="$set('search', '')" 
                                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-slate-800 hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500"
                                            >
                                                Reset Search
                                            </button>
                                        @endif
                                        
                                        @if($selectedCategory)
                                            <button 
                                                type="button" 
                                                wire:click="clearCategory" 
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-500"
                                            >
                                                Clear Category Filter
                                            </button>
                                        @endif
                                        
                                        @if(!$search && !$selectedCategory)
                                            <div class="text-sm text-gray-600">
                                                Try adjusting your search or filters to find products.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 