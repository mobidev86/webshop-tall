<div>
    @if($orderComplete)
        <div class="rounded-md bg-green-50 p-4 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Order placed successfully</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Thank you for your order! Your order number is: <span class="font-semibold">{{ $orderNumber }}</span></p>
                    </div>
                    <div class="mt-4">
                        <div class="-mx-2 -my-1.5 flex">
                            <button 
                                wire:click="toggleForm" 
                                type="button" 
                                class="rounded-md bg-green-50 px-2 py-1.5 text-sm font-medium text-green-800 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50"
                            >
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($showForm)
        <div class="bg-white p-4 border border-gray-200 rounded-md mt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Order now</h4>
            
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
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium leading-6 text-gray-900">Your Name*</label>
                            <div class="mt-1">
                                <input 
                                    type="text" 
                                    wire:model="name" 
                                    id="name"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email Address*</label>
                            <div class="mt-1">
                                <input 
                                    type="email" 
                                    wire:model="email" 
                                    id="email"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium leading-6 text-gray-900">Phone Number</label>
                        <div class="mt-1">
                            <input 
                                type="tel" 
                                wire:model="phone" 
                                id="phone"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            >
                        </div>
                        @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Shipping Address</label>
                        <div class="mt-1">
                            <input 
                                type="text" 
                                wire:model="address" 
                                id="address"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            >
                        </div>
                        @error('address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="city" class="block text-sm font-medium leading-6 text-gray-900">City</label>
                            <div class="mt-1">
                                <input 
                                    type="text" 
                                    wire:model="city" 
                                    id="city"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('city') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="state" class="block text-sm font-medium leading-6 text-gray-900">State/Province</label>
                            <div class="mt-1">
                                <input 
                                    type="text" 
                                    wire:model="state" 
                                    id="state"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('state') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="zip" class="block text-sm font-medium leading-6 text-gray-900">Postal/ZIP Code</label>
                            <div class="mt-1">
                                <input 
                                    type="text" 
                                    wire:model="zip" 
                                    id="zip"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('zip') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        
                        <div>
                            <label for="country" class="block text-sm font-medium leading-6 text-gray-900">Country</label>
                            <div class="mt-1">
                                <input 
                                    type="text" 
                                    wire:model="country" 
                                    id="country"
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                >
                            </div>
                            @error('country') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
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
        <button 
            wire:click="toggleForm"
            type="button"
            class="mt-2 w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
            @if(!$product->isInStock()) disabled @endif
        >
            @if($product->isInStock())
                Order Now
            @else
                Out of Stock
            @endif
        </button>
    @endif
</div>
