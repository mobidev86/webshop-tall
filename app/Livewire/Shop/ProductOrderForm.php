<?php

namespace App\Livewire\Shop;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
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

    public string $guestName = '';

    public string $guestPhone = '';

    public string $guestPassword = '';

    public string $guestPasswordConfirmation = '';

    public bool $userLoggedIn = false;

    public bool $emailValid = false;

    protected array $rules = [
        'quantity' => 'required|integer|min:1',
        'guestEmail' => 'required_if:showEmailForm,true|email',
        'guestName' => 'required_if:showEmailForm,true|string|max:255',
        'guestPhone' => 'nullable|string|max:20',
        'guestPassword' => 'required_if:showEmailForm,true|min:8',
        'guestPasswordConfirmation' => 'required_if:showEmailForm,true|same:guestPassword',
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
        if (! Auth::check()) {
            // Show email form instead of requiring login
            $this->showEmailForm = true;
            $this->loginRequired = false;

            return;
        }

        $this->showForm = ! $this->showForm;
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

        // Validate email format and check if it already exists
        if ($propertyName === 'guestEmail') {
            if (empty($this->guestEmail)) {
                $this->resetErrorBag('guestEmail');
                $this->emailValid = false;
            } else {
                $this->validateEmail();
            }
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

    /**
     * Validate email in real-time
     */
    private function validateEmail(): void
    {
        // Reset validation state
        $this->emailValid = false;

        // First check basic email format
        if (! filter_var($this->guestEmail, FILTER_VALIDATE_EMAIL)) {
            $this->addError('guestEmail', 'Please enter a valid email address.');

            return;
        }

        // Check if email exists in the database
        $existingUser = User::where('email', $this->guestEmail)->first();
        if ($existingUser) {
            $this->addError('guestEmail', 'This email is already registered. Please login or use a different email.');

            return;
        }

        // Email is valid and not taken
        $this->emailValid = true;
    }

    public function submitOrder(): void
    {
        // For guest users, validate all required guest fields
        if (! Auth::check() && $this->showEmailForm) {
            $this->validate([
                'guestEmail' => 'required|email',
                'guestName' => 'required|string|max:255',
                'guestPhone' => 'nullable|string|max:20',
                'guestPassword' => 'required|min:8',
                'guestPasswordConfirmation' => 'required|same:guestPassword',
                'quantity' => 'required|integer|min:1',
            ]);
        }
        // For authenticated users, just validate quantity
        elseif (Auth::check()) {
            $this->validate([
                'quantity' => 'required|integer|min:1',
            ]);
        }
        // If not authenticated and not showing email form, require login
        else {
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
            if (! $this->product->isInStock()) {
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

            if (! $freshProduct) {
                throw new \Exception("Product not found or has been removed.");
            }
            
            if ($freshProduct->stock < $this->quantity) {
                throw new \Exception("Insufficient stock available. Only {$freshProduct->stock} units left.");
            }

            // Handle guest checkout with email or normal user checkout
            if (! Auth::check() && ! empty($this->guestEmail)) {
                // Revalidate guest fields before proceeding
                $this->validate([
                    'guestEmail' => 'required|email',
                    'guestName' => 'required|string|max:255',
                    'guestPassword' => 'required|min:8',
                ]);
                
                $order = $this->createGuestOrder($freshProduct);
            } else {
                // Get current user
                $user = Auth::user();

                if (! $user) {
                    throw new \Exception('User not authenticated.');
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

            // For guest users who just registered and logged in
            if (Auth::check() && ! empty($this->guestEmail)) {
                // Redirect to orders page for newly registered and logged in users
                redirect()->route('customer.orders')->with('success', "Order #{$order->order_number} placed successfully!");
                return;
            }

            // For guest users who are still not logged in
            if (! Auth::check()) {
                $this->handleOrderSuccess($order->order_number);
                return;
            }

            // For regular logged in users (not new registrations)
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
        $newUserCreated = false;

        // Create a temporary user if the email doesn't exist
        if (! $existingUser) {
            try {
                $existingUser = User::create([
                    'name' => $this->guestName,
                    'email' => $this->guestEmail,
                    'phone' => $this->guestPhone,
                    'password' => bcrypt($this->guestPassword),
                    'role' => User::ROLE_CUSTOMER,
                    'is_temporary' => false,  // No longer temporary since we have complete info
                    'is_active' => true,
                ]);
                $newUserCreated = true;
            } catch (\Exception $e) {
                throw new \Exception('Failed to create user account: ' . $e->getMessage());
            }
        }

        // Create the order
        $order = Order::create([
            // Link to existing or newly created temporary user
            'user_id' => $existingUser->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => Order::STATUS_PENDING,
            'total_amount' => $product->getCurrentPrice() * $this->quantity,

            // Set minimal shipping info
            'shipping_email' => $this->guestEmail,
            'shipping_name' => $existingUser->name,
            'shipping_phone' => $existingUser->phone ?? '',

            // Set minimal billing info
            'billing_email' => $this->guestEmail,
            'billing_name' => $existingUser->name,
            'billing_phone' => $existingUser->phone ?? '',
        ]);

        // Log in the newly created user
        if ($newUserCreated) {
            Auth::login($existingUser);
            $this->userLoggedIn = true;
        }

        return $order;
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

            // Set minimal shipping info
            'shipping_name' => $user->name,
            'shipping_email' => $user->email,
            'shipping_phone' => $user->phone ?? '',

            // Set minimal billing info
            'billing_name' => $user->name,
            'billing_email' => $user->email,
            'billing_phone' => $user->phone ?? '',
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
        // Remember the logged in flag
        $wasUserLoggedIn = $this->userLoggedIn;

        // Reset all form fields but keep logged in status
        $this->reset(['quantity', 'showForm', 'showEmailForm', 'guestEmail', 'guestName', 'guestPhone', 'guestPassword', 'guestPasswordConfirmation']);

        // Restore the logged in status
        $this->userLoggedIn = $wasUserLoggedIn;

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
        if (! Auth::check()) {
            // Show email form for direct orders
            $this->showEmailForm = true;
            return;
        }

        // Validate quantity only for logged-in users
        $this->validate([
            'quantity' => 'required|integer|min:1',
        ]);
        
        $this->submitOrder();
    }

    public function render()
    {
        return view('livewire.shop.product-order-form');
    }
}
