<div>
    <div class="container mx-auto px-4 py-8">
        <!-- Filters and Search -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="w-full md:w-1/3">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" id="search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Search products..." type="search">
                    </div>
                </div>
                
                <div class="w-full md:w-1/3">
                    <label for="sortBy" class="sr-only">Sort by</label>
                    <select wire:model.live="sortBy" id="sortBy" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        <option value="newest">Newest</option>
                        <option value="price_asc">Price: Low to High</option>
                        <option value="price_desc">Price: High to Low</option>
                    </select>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex flex-col md:flex-row">
            <!-- Categories Sidebar -->
            <div class="w-full md:w-1/4 lg:w-1/5 mb-6 md:mb-0 md:pr-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <button 
                            wire:click="setCategory('')"
                            class="text-gray-700 hover:text-indigo-600 {{ $categorySlug === '' ? 'font-semibold text-indigo-600' : '' }}"
                        >
                            All Products
                        </button>
                    </li>
                    
                    @foreach($categories as $category)
                        <li class="mb-2">
                            <button 
                                wire:click="setCategory('{{ $category->slug }}')"
                                class="text-gray-700 hover:text-indigo-600 {{ $categorySlug === $category->slug ? 'font-semibold text-indigo-600' : '' }}"
                            >
                                {{ $category->name }}
                            </button>
                            
                            @if($category->children->count() > 0)
                                <ul class="ml-4 mt-2 space-y-1">
                                    @foreach($category->children as $child)
                                        <li>
                                            <button 
                                                wire:click="setCategory('{{ $child->slug }}')"
                                                class="text-gray-600 hover:text-indigo-600 text-sm {{ $categorySlug === $child->slug ? 'font-semibold text-indigo-600' : '' }}"
                                            >
                                                {{ $child->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
            
            <!-- Products Grid -->
            <div class="w-full md:w-3/4 lg:w-4/5">
                @if($products->isEmpty())
                    <div class="bg-white p-6 rounded-lg shadow-sm text-center">
                        <p class="text-gray-500">No products found. Try adjusting your search or filters.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($products as $product)
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                                <a href="{{ route('products.show', $product->slug) }}" class="block">
                                    <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                                        @if($product->image)
                                            <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="p-4">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $product->name }}</h3>
                                        
                                        <div class="mt-2 flex justify-between items-center">
                                            <div>
                                                @if($product->isOnSale())
                                                    <span class="text-gray-500 line-through text-sm">${{ $this->formatPrice($product->price) }}</span>
                                                    <span class="text-indigo-600 font-medium">${{ $this->formatPrice($product->sale_price) }}</span>
                                                @else
                                                    <span class="text-indigo-600 font-medium">${{ $this->formatPrice($product->price) }}</span>
                                                @endif
                                            </div>
                                            
                                            @if($product->stock > 0)
                                                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">In Stock</span>
                                            @else
                                                <span class="text-xs bg-red-100 text-red-800 px-2 py-1 rounded">Out of Stock</span>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
