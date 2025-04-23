<div>
    <div class="bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Main search bar at the top -->
            <div class="pt-24 pb-6">
                <div class="relative mb-6">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input 
                            wire:model.live.debounce.300ms="search" 
                            type="text" 
                            class="block w-full rounded-lg border-0 py-3 pl-10 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-lg"
                            placeholder="{{ $searchPlaceholder }}"
                        >
                        <!-- Search indicator -->
                        <div class="absolute inset-y-0 right-0 left-auto flex items-center pr-3">
                            <div wire:loading.delay wire:target="search">
                                <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                        <div class="mt-2 text-sm text-gray-600">
                            Searching for: <span class="font-medium">{{ $search }}</span>
                        </div>
                    @endif
                </div>
                
                <div class="flex items-baseline justify-between border-b border-gray-200 pb-6">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">Our Products</h1>
                    
                    <div class="flex items-center">
                        <div class="relative inline-block text-left">
                            <div>
                                <select wire:model.live="sortBy" id="sort-by" class="rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500">
                                    <option value="name">Name</option>
                                    <option value="price">Price</option>
                                    <option value="created_at">Newest</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="ml-2">
                            <button wire:click="$set('sortDirection', '{{ $sortDirection === 'asc' ? 'desc' : 'asc' }}')" type="button" class="p-2 text-gray-400 hover:text-gray-500">
                                @if($sortDirection === 'asc')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="pt-6 pb-24">
                <div class="grid grid-cols-1 gap-x-8 gap-y-10 lg:grid-cols-4">
                    <!-- Filters -->
                    <div class="lg:col-span-1">
                        <div class="space-y-6">
                            <!-- Category Filter -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Categories</h3>
                                <ul class="mt-4 space-y-3">
                                    <li>
                                        <button 
                                            wire:click="clearCategory" 
                                            class="flex items-center {{ !$selectedCategory ? 'text-indigo-600 font-medium' : 'text-gray-500' }}"
                                        >
                                            All Categories
                                        </button>
                                    </li>
                                    @foreach($categories as $category)
                                        <li>
                                            <button 
                                                wire:click="selectCategory({{ $category->id }})" 
                                                class="flex items-center {{ $selectedCategory == $category->id ? 'text-indigo-600 font-medium' : 'text-gray-500' }}"
                                            >
                                                {{ $category->name }}
                                                @if($category->products_count)
                                                    <span class="ml-1 text-xs text-gray-400">({{ $category->products_count }})</span>
                                                @endif
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Product grid -->
                    <div class="lg:col-span-3">
                        <!-- Loading indicator for the entire grid -->
                        <div wire:loading.delay class="w-full">
                            <div class="flex justify-center items-center py-10">
                                <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <div wire:loading.delay.remove>
                            @if($products->count())
                                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 xl:gap-x-8">
                                    @foreach($products as $product)
                                        <div class="group relative bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                                            @if($product->image)
                                                <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden bg-gray-200 xl:aspect-h-8 xl:aspect-w-7">
                                                    <img src="{{ $product->image }}" alt="{{ $product->name }}" class="h-48 w-full object-cover object-center">
                                                </div>
                                            @else
                                                <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden bg-gray-100 xl:aspect-h-8 xl:aspect-w-7">
                                                    <div class="h-48 w-full flex items-center justify-center text-gray-500">
                                                        <svg class="h-12 w-12" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            @endif
                                            <div class="p-4">
                                                <h3 class="text-base font-semibold text-gray-900">
                                                    @if($search)
                                                        {!! $this->highlightSearchTerm($product->name) !!}
                                                    @else
                                                        {{ $product->name }}
                                                    @endif
                                                </h3>
                                                
                                                @if($product->categories->count())
                                                    <div class="mt-1">
                                                        @foreach($product->categories->take(3) as $category)
                                                            <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10 mr-1">
                                                                {{ $category->name }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                
                                                <div class="mt-2 mb-4 flex items-center justify-between">
                                                    <div>
                                                        @if($product->isOnSale())
                                                            <span class="text-lg font-medium text-gray-900">${{ number_format($product->sale_price, 2) }}</span>
                                                            <span class="ml-2 text-sm text-gray-500 line-through">${{ number_format($product->price, 2) }}</span>
                                                        @else
                                                            <span class="text-lg font-medium text-gray-900">${{ number_format($product->price, 2) }}</span>
                                                        @endif
                                                    </div>
                                                    
                                                    <div>
                                                        @if($product->isInStock())
                                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                                In Stock
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                                                Out of Stock
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                                
                                                <livewire:shop.product-order-form :product="$product" :wire:key="'order-form-'.$product->id" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                
                                <div class="mt-8">
                                    {{ $products->links() }}
                                </div>
                            @else
                                <div class="text-center py-12 bg-white rounded-lg shadow-sm border border-gray-200">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                                    </svg>
                                    <h3 class="mt-2 text-lg font-medium text-gray-900">{{ $noResultsMessage }}</h3>
                                    <div class="mt-6">
                                        <button 
                                            type="button" 
                                            wire:click="$set('search', '')" 
                                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                        >
                                            Reset Search
                                        </button>
                                        
                                        @if($selectedCategory)
                                            <button 
                                                type="button" 
                                                wire:click="clearCategory" 
                                                class="ml-3 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                Clear Category Filter
                                            </button>
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