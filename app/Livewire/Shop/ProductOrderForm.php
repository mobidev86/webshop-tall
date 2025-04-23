<?php

namespace App\Livewire\Shop;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductOrderForm extends Component
{
    public Product $product;
    
    public int $quantity = 1;
    
    public bool $showForm = false;
    public bool $orderComplete = false;
    public string $orderNumber = '';
    public bool $processingOrder = false;
    public bool $loginRequired = false;
    public bool $showEmailForm = false;
    public string $guestEmail = '';
    
    protected array $rules = [
        'quantity' => 'required|integer|min:1',
        'guestEmail' => 'sometimes|required|email',
    ];
    
    public function mount(Product $product): void
    {
        $this->product = $product;
    }
    
    public function incrementQuantity(): void
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }
    
    public function decrementQuantity(): void
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    public function toggleForm(): void
    {
        // Check if user is logged in
        if (!Auth::check()) {
            // Show email form instead of requiring login
            $this->showEmailForm = true;
            $this->loginRequired = false;
            return;
        }
        
        $this->showForm = !$this->showForm;
        $this->orderComplete = false;
        $this->loginRequired = false;
        
        // Reset validation errors when toggling form
        $this->resetValidation();
    }
    
    public function updated($propertyName): void
    {
        // Validate field as it's updated
        $this->validateOnly($propertyName);
        
        // Enforce quantity constraints
        if ($propertyName === 'quantity') {
            $this->normalizeQuantity();
        }
    }
    
    /**
     * Ensure quantity is within valid bounds
     */
    private function normalizeQuantity(): void
    {
        // Don't allow quantity to exceed available stock
        if ($this->quantity > $this->product->stock) {
            $this->quantity = $this->product->stock;
        }
        
        // Don't allow negative or zero quantity
        if ($this->quantity < 1) {
            $this->quantity = 1;
        }
    }
    
    public function submitOrder(): void
    {
        // Allow guest checkout with email
        if (!Auth::check() && $this->showEmailForm) {
            $this->validate([
                'guestEmail' => 'required|email',
                'quantity' => 'required|integer|min:1',
            ]);
        } 
        // Require login for normal flow
        else if (!Auth::check()) {
            $this->loginRequired = true;
            return;
        }
        
        // Prevent double submission
        if ($this->processingOrder) {
            return;
        }
        
        $this->processingOrder = true;
        
        try {
            // Check if product is in stock
            if (!$this->product->isInStock()) {
                $this->addError('quantity', 'This product is out of stock.');
                $this->processingOrder = false;
                return;
            }
            
            // Check if requested quantity is available
            if ($this->quantity > $this->product->stock) {
                $this->addError('quantity', "Only {$this->product->stock} units available.");
                $this->quantity = $this->product->stock;
                $this->processingOrder = false;
                return;
            }
            
            // Validate form fields
            $this->validate();
            
            $this->createOrderWithTransaction();
            
        } catch (\Exception $e) {
            $this->handleOrderError($e);
        } finally {
            $this->processingOrder = false;
        }
    }
    
    /**
     * Create order using a database transaction
     */
    private function createOrderWithTransaction(): void
    {
        DB::beginTransaction();
        
        try {
            // Double-check product stock in transaction to prevent race conditions
            $freshProduct = Product::lockForUpdate()->find($this->product->id);
            
            if (!$freshProduct || $freshProduct->stock < $this->quantity) {
                throw new \Exception("Insufficient stock available. Only {$freshProduct->stock} units left.");
            }
            
            // Handle guest checkout with email or normal user checkout
            if (!Auth::check() && !empty($this->guestEmail)) {
                $order = $this->createGuestOrder($freshProduct);
            } else {
                // Get current user
                $user = Auth::user();
                
                if (!$user) {
                    throw new \Exception("User not authenticated.");
                }
                
                // Create order
                $order = $this->createOrder($user, $freshProduct);
            }
            
            // Create order item
            $this->createOrderItem($order, $freshProduct);
            
            // Update product stock
            $freshProduct->stock -= $this->quantity;
            $freshProduct->save();
            
            DB::commit();
            
            // Update local product stock to reflect the change
            $this->product = $freshProduct;
            
            // For guest users, show confirmation without redirecting to account page
            if (!Auth::check()) {
                $this->handleOrderSuccess($order->order_number);
                return;
            }
            
            // Redirect to orders page for logged in users
            redirect()->route('customer.orders')->with('success', "Order #{$order->order_number} placed successfully!");
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Create a guest order with just email
     */
    private function createGuestOrder(Product $product): Order
    {
        // Check if this email belongs to an existing user
        $existingUser = User::where('email', $this->guestEmail)->first();
        
        return Order::create([
            // Link to existing user if email matches
            'user_id' => $existingUser ? $existingUser->id : null,
            'order_number' => Order::generateOrderNumber(),
            'status' => Order::STATUS_PENDING,
            'total_amount' => $product->getCurrentPrice() * $this->quantity,
            
            // Set email (and minimal shipping info)
            'shipping_email' => $this->guestEmail,
            'shipping_name' => $existingUser ? $existingUser->name : '',
            'shipping_phone' => $existingUser ? ($existingUser->phone ?? '') : '',
            'shipping_address' => $existingUser ? ($existingUser->address ?? '') : '',
            'shipping_city' => $existingUser ? ($existingUser->city ?? '') : '',
            'shipping_state' => $existingUser ? ($existingUser->state ?? '') : '',
            'shipping_zip' => $existingUser ? ($existingUser->zip_code ?? '') : '',
            'shipping_country' => $existingUser ? ($existingUser->country ?? '') : '',
            
            // Set billing to match shipping
            'billing_email' => $this->guestEmail,
            'billing_name' => $existingUser ? $existingUser->name : '',
            'billing_phone' => $existingUser ? ($existingUser->phone ?? '') : '',
            'billing_address' => $existingUser ? ($existingUser->address ?? '') : '',
            'billing_city' => $existingUser ? ($existingUser->city ?? '') : '',
            'billing_state' => $existingUser ? ($existingUser->state ?? '') : '',
            'billing_zip' => $existingUser ? ($existingUser->zip_code ?? '') : '',
            'billing_country' => $existingUser ? ($existingUser->country ?? '') : '',
        ]);
    }
    
    /**
     * Create a new order
     */
    private function createOrder(User $user, Product $product): Order
    {
        return Order::create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => Order::STATUS_PENDING,
            'total_amount' => $product->getCurrentPrice() * $this->quantity,
            
            // Set shipping info from user
            'shipping_name' => $user->name,
            'shipping_email' => $user->email,
            'shipping_phone' => $user->phone ?? '',
            'shipping_address' => $user->address ?? '',
            'shipping_city' => $user->city ?? '',
            'shipping_state' => $user->state ?? '',
            'shipping_zip' => $user->zip_code ?? '',
            'shipping_country' => $user->country ?? '',
            
            // Set billing info from user
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? '',
            'billing_address' => $user->address ?? '',
            'billing_city' => $user->city ?? '',
            'billing_state' => $user->state ?? '',
            'billing_zip' => $user->zip_code ?? '',
            'billing_country' => $user->country ?? '',
        ]);
    }
    
    /**
     * Create an order item
     */
    private function createOrderItem(Order $order, Product $product): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $this->quantity,
            'price' => $product->getCurrentPrice(),
            'subtotal' => $product->getCurrentPrice() * $this->quantity,
        ]);
    }
    
    /**
     * Handle a successful order - no longer used as we redirect directly
     */
    private function handleOrderSuccess(string $orderNumber): void
    {
        $this->reset(['quantity', 'showForm', 'showEmailForm', 'guestEmail']);
        $this->quantity = 1;
        $this->orderComplete = true;
        $this->orderNumber = $orderNumber;
    }
    
    /**
     * Handle order creation error
     */
    private function handleOrderError(\Exception $e): void
    {
        Log::error('Order creation failed: ' . $e->getMessage(), [
            'product_id' => $this->product->id,
            'quantity' => $this->quantity,
            'user_id' => Auth::id(),
            'guest_email' => $this->guestEmail ?? null,
        ]);
        
        // Add error message to the form
        $this->addError('general', 'Failed to create order: ' . $e->getMessage());
    }
    
    /**
     * Direct order with default quantity
     */
    public function directOrder(): void
    {
        // Check if user is logged in
        if (!Auth::check()) {
            // Show email form for direct orders too
            $this->showEmailForm = true;
            return;
        }
        
        $this->submitOrder();
    }
    
    public function render()
    {
        return view('livewire.shop.product-order-form');
    }
}
