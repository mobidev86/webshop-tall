<div>
    @if($loginRequired)
        <div class="rounded-md bg-amber-50 p-4 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-amber-800">Login required</h3>
                    <div class="mt-2 text-sm text-amber-700">
                        <p>You must be logged in to place an order. <a href="{{ route('login') }}" class="font-medium text-amber-800 underline">Click here to login</a>.</p>
                    </div>
                </div>
            </div>
        </div>
    @elseif($showForm)
        <div class="bg-white p-4 border border-gray-200 rounded-md mt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Confirm your order</h4>
            
            <form wire:submit.prevent="submitOrder">
                @if(session()->has('error'))
                    <div class="rounded-md bg-red-50 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ session('error') }}</h3>
                            </div>
                        </div>
                    </div>
                @endif
                
                @error('general')
                    <div class="rounded-md bg-red-50 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">{{ $message }}</h3>
                            </div>
                        </div>
                    </div>
                @enderror
                
                <div class="space-y-4">
                    <div>
                        <label for="quantity" class="block text-sm font-medium leading-6 text-gray-900">Quantity</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <button 
                                type="button"
                                wire:click="decrementQuantity"
                                class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-l-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                -
                            </button>
                            <input 
                                type="number" 
                                wire:model.live="quantity" 
                                id="quantity"
                                min="1"
                                max="{{ $product->stock }}"
                                class="block w-full border-0 py-1.5 pl-4 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 sm:text-sm sm:leading-6 text-center"
                            >
                            <button 
                                type="button"
                                wire:click="incrementQuantity"
                                class="relative -ml-px inline-flex items-center gap-x-1.5 rounded-r-md px-3 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                +
                            </button>
                        </div>
                        @error('quantity') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="mt-4">
                        <div class="rounded-md bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-blue-700">Your order will be shipped to your account address.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between space-x-2">
                        <p class="text-sm font-medium text-gray-700">
                            Total: ${{ number_format($product->getCurrentPrice() * $quantity, 2) }}
                        </p>
                        
                        <div class="flex space-x-2">
                            <button 
                                type="button"
                                wire:click="toggleForm"
                                class="rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            
                            <button 
                                type="submit"
                                class="rounded-md bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(!$product->isInStock() || $processingOrder) disabled @endif
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="submitOrder">Place Order</span>
                                <span wire:loading wire:target="submitOrder">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @else
        @if(Auth::check())
            <div class="mt-4">
                <div class="flex items-center justify-between">
                    <label for="product-quantity-{{ $product->id }}" class="block text-sm font-medium text-gray-700">Quantity</label>
                    <span class="text-sm text-gray-500">{{ $product->stock }} available</span>
                </div>
                <div class="mt-1 flex rounded-md shadow-sm">
                    <button 
                        type="button"
                        wire:click="decrementQuantity"
                        class="relative inline-flex items-center gap-x-1.5 rounded-l-md px-2 py-1.5 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        @if(!$product->isInStock()) disabled @endif
                    >
                        -
                    </button>
                    <input 
                        type="number" 
                        wire:model.live="quantity" 
                        id="product-quantity-{{ $product->id }}"
                        min="1"
                        max="{{ $product->stock }}"
                        class="block w-full border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 sm:text-sm sm:leading-6 text-center"
                        @if(!$product->isInStock()) disabled @endif
                    >
                    <button 
                        type="button"
                        wire:click="incrementQuantity"
                        class="relative inline-flex items-center gap-x-1.5 rounded-r-md px-2 py-1.5 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        @if(!$product->isInStock()) disabled @endif
                    >
                        +
                    </button>
                </div>
                @error('quantity') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            
            <button 
                wire:click="directOrder"
                type="button"
                class="mt-3 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                @if(!$product->isInStock()) disabled @endif
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="directOrder">
                    @if($product->isInStock())
                        Order Now
                    @else
                        Out of Stock
                    @endif
                </span>
                <span wire:loading wire:target="directOrder">Processing...</span>
            </button>
        @else
            <button 
                wire:click="toggleForm"
                type="button"
                class="mt-2 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                @if(!$product->isInStock()) disabled @endif
            >
                @if($product->isInStock())
                    Order Now - Sign In Required
                @else
                    Out of Stock
                @endif
            </button>
        @endif
    @endif
</div>
