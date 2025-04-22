<?php

namespace App\Livewire\Shop;

use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ProductOrderForm extends Component
{
    public Product $product;
    
    public $quantity = 1;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $city = '';
    public $state = '';
    public $zip = '';
    public $country = '';
    
    public $showForm = false;
    public $orderComplete = false;
    public $orderNumber = '';
    public $processingOrder = false;
    
    protected $rules = [
        'quantity' => 'required|integer|min:1',
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'city' => 'nullable|string|max:100',
        'state' => 'nullable|string|max:100',
        'zip' => 'nullable|string|max:20',
        'country' => 'nullable|string|max:100',
    ];
    
    public function mount(Product $product)
    {
        $this->product = $product;
        
        // Pre-fill fields if user is logged in
        if (auth()->check()) {
            $user = auth()->user();
            $this->name = $user->name;
            $this->email = $user->email;
            $this->phone = $user->phone ?? '';
            $this->address = $user->address ?? '';
            $this->city = $user->city ?? '';
            $this->state = $user->state ?? '';
            $this->zip = $user->zip_code ?? '';
            $this->country = $user->country ?? '';
        }
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
        $this->showForm = !$this->showForm;
        $this->orderComplete = false;
        
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
            
            // Find or create customer user
            $customer = User::firstOrCreate(
                ['email' => $this->email],
                [
                    'name' => $this->name,
                    'password' => bcrypt(uniqid()), // Generate random password
                    'role' => User::ROLE_CUSTOMER,
                ]
            );
            
            // Update customer info if fields are provided
            $customerUpdated = false;
            $customerData = [];
            
            if ($this->phone && $customer->phone !== $this->phone) {
                $customerData['phone'] = $this->phone;
                $customerUpdated = true;
            }
            
            if ($this->address && $customer->address !== $this->address) {
                $customerData['address'] = $this->address;
                $customerUpdated = true;
            }
            
            if ($this->city && $customer->city !== $this->city) {
                $customerData['city'] = $this->city;
                $customerUpdated = true;
            }
            
            if ($this->state && $customer->state !== $this->state) {
                $customerData['state'] = $this->state;
                $customerUpdated = true;
            }
            
            if ($this->zip && $customer->zip_code !== $this->zip) {
                $customerData['zip_code'] = $this->zip;
                $customerUpdated = true;
            }
            
            if ($this->country && $customer->country !== $this->country) {
                $customerData['country'] = $this->country;
                $customerUpdated = true;
            }
            
            if ($customerUpdated) {
                $customer->update($customerData);
            }
            
            // Create order
            $order = Order::create([
                'user_id' => auth()->id() ?? $customer->id, // Use logged in user ID or the customer we just created
                'order_number' => Order::generateOrderNumber(),
                'status' => Order::STATUS_PENDING,
                'total_amount' => $freshProduct->getCurrentPrice() * $this->quantity,
                
                // Set shipping/billing info from form
                'shipping_name' => $this->name,
                'shipping_email' => $this->email,
                'shipping_phone' => $this->phone,
                'shipping_address' => $this->address,
                'shipping_city' => $this->city,
                'shipping_state' => $this->state,
                'shipping_zip' => $this->zip,
                'shipping_country' => $this->country,
                
                'billing_name' => $this->name,
                'billing_email' => $this->email,
                'billing_phone' => $this->phone,
                'billing_address' => $this->address,
                'billing_city' => $this->city,
                'billing_state' => $this->state,
                'billing_zip' => $this->zip,
                'billing_country' => $this->country,
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
                'user_email' => $this->email
            ]);
            
            session()->flash('error', 'An error occurred while placing your order: ' . $e->getMessage());
            $this->addError('general', 'Order processing failed. Please try again.');
        } finally {
            $this->processingOrder = false;
        }
    }
    
    public function render()
    {
        return view('livewire.shop.product-order-form');
    }
}
