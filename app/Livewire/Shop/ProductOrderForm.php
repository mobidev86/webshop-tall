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
    
    public $quantity = 1;
    
    public $showForm = false;
    public $orderComplete = false;
    public $orderNumber = '';
    public $processingOrder = false;
    public $loginRequired = false;
    
    protected $rules = [
        'quantity' => 'required|integer|min:1',
    ];
    
    public function mount(Product $product)
    {
        $this->product = $product;
    }
    
    public function incrementQuantity()
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }
    
    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }
    
    public function toggleForm()
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
    
    public function updated($propertyName)
    {
        // Validate field as it's updated
        $this->validateOnly($propertyName);
        
        // Enforce quantity constraints
        if ($propertyName === 'quantity') {
            // Don't allow quantity to exceed available stock
            if ($this->quantity > $this->product->stock) {
                $this->quantity = $this->product->stock;
            }
            
            // Don't allow negative or zero quantity
            if ($this->quantity < 1) {
                $this->quantity = 1;
            }
        }
    }
    
    public function submitOrder()
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
        
        try {
            DB::beginTransaction();
            
            // Double-check product stock in transaction to prevent race conditions
            $freshProduct = Product::lockForUpdate()->find($this->product->id);
            
            if (!$freshProduct || $freshProduct->stock < $this->quantity) {
                throw new \Exception("Insufficient stock available. Only {$freshProduct->stock} units left.");
            }
            
            // Get current user
            $user = Auth::user();
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'total_amount' => $freshProduct->getCurrentPrice() * $this->quantity,
                
                // Set shipping/billing info from user
                'shipping_name' => $user->name,
                'shipping_email' => $user->email,
                'shipping_phone' => $user->phone ?? '',
                'shipping_address' => $user->address ?? '',
                'shipping_city' => $user->city ?? '',
                'shipping_state' => $user->state ?? '',
                'shipping_zip' => $user->zip_code ?? '',
                'shipping_country' => $user->country ?? '',
                
                'billing_name' => $user->name,
                'billing_email' => $user->email,
                'billing_phone' => $user->phone ?? '',
                'billing_address' => $user->address ?? '',
                'billing_city' => $user->city ?? '',
                'billing_state' => $user->state ?? '',
                'billing_zip' => $user->zip_code ?? '',
                'billing_country' => $user->country ?? '',
            ]);
            
            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $freshProduct->id,
                'product_name' => $freshProduct->name,
                'quantity' => $this->quantity,
                'price' => $freshProduct->getCurrentPrice(),
                'subtotal' => $freshProduct->getCurrentPrice() * $this->quantity,
            ]);
            
            // Update product stock
            $freshProduct->stock -= $this->quantity;
            $freshProduct->save();
            
            DB::commit();
            
            // Update local product stock to reflect the change
            $this->product = $freshProduct;
            
            // Reset form
            $this->reset(['quantity', 'showForm']);
            $this->quantity = 1;
            $this->orderComplete = true;
            $this->orderNumber = $order->order_number;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage(), [
                'product_id' => $this->product->id,
                'quantity' => $this->quantity,
                'user_id' => Auth::id()
            ]);
            
            session()->flash('error', 'An error occurred while placing your order: ' . $e->getMessage());
            $this->addError('general', 'Order processing failed. Please try again.');
        } finally {
            $this->processingOrder = false;
        }
    }
    
    public function directOrder()
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
