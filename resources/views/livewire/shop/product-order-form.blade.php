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
    @elseif($showEmailForm)
        <div class="bg-white p-4 border border-gray-200 rounded-md mt-4">
            <h4 class="text-sm font-medium text-gray-900 mb-3">Register and Checkout</h4>
            
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
                    <!-- Email input for guest users -->
                    <div>
                        <label for="guestEmail" class="block text-sm font-medium leading-6 text-gray-900">Email Address</label>
                        <div class="mt-1 relative">
                            <input 
                                type="email" 
                                wire:model.live="guestEmail" 
                                id="guestEmail"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset 
                                    @error('guestEmail') ring-red-300 text-red-900 focus:ring-red-500 @else ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 @enderror
                                    sm:text-sm sm:leading-6"
                                placeholder="your@email.com"
                                required
                            >
                            @error('guestEmail') 
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-5a.75.75 0 01.75.75v4.5a.75.75 0 01-1.5 0v-4.5A.75.75 0 0110 5zm0 10a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @elseif($emailValid && $guestEmail)
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0116 0zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            @enderror
                        </div>
                        @error('guestEmail') 
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @if(str_contains($message, 'already registered'))
                                <p class="mt-1 text-sm text-gray-600">
                                    <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                                        Click here to login
                                    </a>
                                </p>
                            @endif
                        @enderror
                        
                        @if($emailValid && $guestEmail)
                            <p class="mt-2 text-sm text-green-600">Email address is available</p>
                        @endif
                    </div>
                    
                    <!-- Name input -->
                    <div>
                        <label for="guestName" class="block text-sm font-medium leading-6 text-gray-900">Full Name</label>
                        <div class="mt-1">
                            <input 
                                type="text" 
                                wire:model.live="guestName" 
                                id="guestName"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="John Doe"
                                required
                            >
                        </div>
                        @error('guestName') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Phone input -->
                    <div>
                        <label for="guestPhone" class="block text-sm font-medium leading-6 text-gray-900">Phone Number (optional)</label>
                        <div class="mt-1">
                            <input 
                                type="tel" 
                                wire:model.live="guestPhone" 
                                id="guestPhone"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                placeholder="+1 (555) 123-4567"
                            >
                        </div>
                        @error('guestPhone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Password input -->
                    <div>
                        <label for="guestPassword" class="block text-sm font-medium leading-6 text-gray-900">Password</label>
                        <div class="mt-1">
                            <input 
                                type="password" 
                                wire:model.live="guestPassword" 
                                id="guestPassword"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                required
                            >
                        </div>
                        @error('guestPassword') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    
                    <!-- Password Confirmation input -->
                    <div>
                        <label for="guestPasswordConfirmation" class="block text-sm font-medium leading-6 text-gray-900">Confirm Password</label>
                        <div class="mt-1">
                            <input 
                                type="password" 
                                wire:model.live="guestPasswordConfirmation" 
                                id="guestPasswordConfirmation"
                                class="block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                                required
                            >
                        </div>
                        @error('guestPasswordConfirmation') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                        
                    <div class="mt-2 text-xs text-gray-500 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>Enter your details to create an account and place your order</span>
                    </div>
                    
                    <div class="mt-2 text-xs">
                        <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-500">Already have an account? Sign in</a>
                    </div>
                    
                    <div>
                        <label for="quantity" class="block text-sm font-medium leading-6 text-gray-900 mt-4">Quantity</label>
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
                    
                    <div class="mt-6">
                        <p class="text-sm font-medium text-gray-700 mb-4">
                            Total: ${{ number_format($product->getCurrentPrice() * $quantity, 2) }}
                        </p>
                        
                        <div class="flex justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="toggleForm"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            
                            <button 
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(!$product->isInStock() || $processingOrder) disabled @endif
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="submitOrder">Register & Checkout</span>
                                <span wire:loading wire:target="submitOrder">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
                        <label for="quantity" class="block text-sm font-medium leading-6 text-gray-900 mt-4">Quantity</label>
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
                    
                    <div class="mt-6">
                        <p class="text-sm font-medium text-gray-700 mb-4">
                            Total: ${{ number_format($product->getCurrentPrice() * $quantity, 2) }}
                        </p>
                        
                        <div class="flex justify-end space-x-3">
                            <button 
                                type="button"
                                wire:click="toggleForm"
                                class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            
                            <button 
                                type="submit"
                                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                                @if(!$product->isInStock() || $processingOrder) disabled @endif
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                            >
                                <span wire:loading.remove wire:target="submitOrder">Register & Checkout</span>
                                <span wire:loading wire:target="submitOrder">Processing...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @elseif($orderComplete)
        <div class="rounded-md bg-green-50 p-4 mt-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 0116 0zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">Order placed successfully!</h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>Your order #{{ $orderNumber }} has been placed. We've sent a confirmation to your email.</p>
                        @if(!Auth::check())
                            <p class="mt-2">
                                A new account has been created for you. You can <a href="{{ route('login') }}" class="font-medium underline hover:text-green-900">login</a> 
                                with your email and the password you provided.
                            </p>
                        @elseif($userLoggedIn)
                            <p class="mt-2">
                                You've been automatically logged in to your new account. Go to <a href="{{ route('customer.orders') }}" class="font-medium underline hover:text-green-900">My Orders</a> 
                                to track your order.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
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
                    Register & Checkout
                @else
                    Out of Stock
                @endif
            </button>
        @endif
    @endif
</div>
