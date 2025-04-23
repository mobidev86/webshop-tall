<div>
    {{-- In work, do what you enjoy. --}}
</div>

<div>
    <div class="container mx-auto px-4 py-8">
        <!-- Breadcrumbs -->
        <nav class="mb-6">
            <ol class="flex space-x-2 text-sm text-gray-500">
                <li>
                    <a href="{{ route('home') }}" class="hover:text-indigo-600">Home</a>
                </li>
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li>
                    <a href="{{ route('products.index') }}" class="hover:text-indigo-600">Products</a>
                </li>
                
                @if($product->categories->isNotEmpty())
                    <li class="flex items-center">
                        <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li>
                        <a href="{{ route('products.index', ['categorySlug' => $product->categories->first()->slug]) }}" class="hover:text-indigo-600">
                            {{ $product->categories->first()->name }}
                        </a>
                    </li>
                @endif
                
                <li class="flex items-center">
                    <svg class="w-4 h-4 mx-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </li>
                <li class="text-gray-700 font-medium">{{ $product->name }}</li>
            </ol>
        </nav>
        
        <div class="flex flex-col lg:flex-row -mx-4">
            <!-- Product Image -->
            <div class="w-full lg:w-1/2 px-4 mb-8 lg:mb-0">
                <div class="sticky top-6">
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden">
                            @if($product->image)
                                <img src="{{ $product->imageUrl }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                    <svg class="w-24 h-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details -->
            <div class="w-full lg:w-1/2 px-4">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                    
                    <!-- Categories -->
                    @if($product->categories->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mb-4">
                            @foreach($product->categories as $category)
                                <a href="{{ route('products.index', ['categorySlug' => $category->slug]) }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 hover:bg-gray-200">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                    
                    <!-- Price -->
                    <div class="mb-4">
                        @if($product->isOnSale())
                            <div class="flex items-baseline">
                                <span class="text-gray-500 line-through text-lg mr-2">${{ $this->formatPrice($product->price) }}</span>
                                <span class="text-3xl font-bold text-indigo-600">${{ $this->formatPrice($product->sale_price) }}</span>
                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">Sale</span>
                            </div>
                        @else
                            <span class="text-3xl font-bold text-indigo-600">${{ $this->formatPrice($product->price) }}</span>
                        @endif
                    </div>
                    
                    <!-- Stock Status -->
                    <div class="mb-6">
                        @if($product->stock > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-green-100 text-green-800">
                                <svg class="-ml-0.5 mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8">
                                    <circle cx="4" cy="4" r="3" />
                                </svg>
                                In Stock ({{ $product->stock }} available)
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-sm font-medium bg-red-100 text-red-800">
                                Out of Stock
                            </span>
                        @endif
                    </div>
                    
                    <!-- Description -->
                    <div class="prose prose-sm text-gray-700 mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Description</h3>
                        <div class="whitespace-pre-line">{{ $product->description }}</div>
                    </div>
                    
                    <!-- Quantity and Add to Cart -->
                    @if($product->stock > 0)
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Quantity</h3>
                            <div class="flex items-center mb-4">
                                <button wire:click="decrementQuantity" type="button" class="text-gray-500 focus:outline-none focus:text-gray-600 p-1">
                                    <svg class="h-6 w-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M20 12H4"></path>
                                    </svg>
                                </button>
                                <input wire:model="quantity" type="number" min="1" max="{{ $product->stock }}" class="mx-2 border text-center w-16 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" readonly>
                                <button wire:click="incrementQuantity" type="button" class="text-gray-500 focus:outline-none focus:text-gray-600 p-1">
                                    <svg class="h-6 w-6" fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M12 4v16m8-8H4"></path>
                                    </svg>
                                </button>
                            </div>
                            
                            <div class="mt-2">
                                <span class="text-gray-700">Subtotal: <span class="font-semibold">${{ $this->getTotalPrice() }}</span></span>
                            </div>
                            
                            <button type="button" class="mt-4 w-full bg-indigo-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add to Cart
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        @if($relatedProducts->isNotEmpty())
            <div class="mt-16">
                <h2 class="text-xl font-bold text-gray-900 mb-6">Related Products</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedProducts as $relatedProduct)
                        <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-300">
                            <a href="{{ route('products.show', $relatedProduct->slug) }}" class="block">
                                <div class="aspect-w-1 aspect-h-1 bg-gray-200">
                                    @if($relatedProduct->image)
                                        <img src="{{ $relatedProduct->imageUrl }}" alt="{{ $relatedProduct->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="text-sm font-medium text-gray-900">{{ $relatedProduct->name }}</h3>
                                    
                                    <div class="mt-2 flex justify-between items-center">
                                        <div>
                                            @if($relatedProduct->isOnSale())
                                                <span class="text-gray-500 line-through text-sm">${{ $this->formatPrice($relatedProduct->price) }}</span>
                                                <span class="text-indigo-600 font-medium">${{ $this->formatPrice($relatedProduct->sale_price) }}</span>
                                            @else
                                                <span class="text-indigo-600 font-medium">${{ $this->formatPrice($relatedProduct->price) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        
        @if(auth()->check())
        <div 
            x-data="{{ json_encode(['name' => auth()->user()->name]) }}" 
            x-text="name" 
            x-on:profile-updated.window="name = $event.detail.name"
            class="mt-8 text-sm text-gray-500">
            Logged in as: 
        </div>
        @endif
    </div>
</div>
