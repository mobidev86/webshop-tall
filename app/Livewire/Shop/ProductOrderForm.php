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
    
    protected array $rules = [
        'quantity' => 'required|integer|min:1',
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
            // Set flag to show login required message
            $this->loginRequired = true;
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
        // Check if user is logged in
        if (!Auth::check()) {
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
            
            // Get current user
            $user = Auth::user();
            
            if (!$user) {
                throw new \Exception("User not authenticated.");
            }
            
            // Create order
            $order = $this->createOrder($user, $freshProduct);
            
            // Create order item
            $this->createOrderItem($order, $freshProduct);
            
            // Update product stock
            $freshProduct->stock -= $this->quantity;
            $freshProduct->save();
            
            DB::commit();
            
            // Update local product stock to reflect the change
            $this->product = $freshProduct;
            
            // Reset form
            $this->handleOrderSuccess($order->order_number);
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
     * Handle a successful order
     */
    private function handleOrderSuccess(string $orderNumber): void
    {
        $this->reset(['quantity', 'showForm']);
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
            'user_id' => Auth::id()
        ]);
        
        session()->flash('error', 'An error occurred while placing your order: ' . $e->getMessage());
        $this->addError('general', 'Order processing failed. Please try again.');
    }
    
    public function directOrder(): void
    {
        // Only allow logged-in users to place orders
        if (!Auth::check()) {
            $this->loginRequired = true;
            return;
        }
        
        // Set default quantity to 1 and submit the order
        $this->quantity = 1;
        $this->submitOrder();
    }
    
    public function render()
    {
        return view('livewire.shop.product-order-form');
    }
}
