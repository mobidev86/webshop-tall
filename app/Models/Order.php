<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Order extends Model
{
    use HasFactory;
    
    // Order status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_DECLINED = 'declined';
    const STATUS_CANCELLED = 'cancelled';
    
    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'total_amount',
        'payment_method',
        'shipping_method',
        'notes',
        // Shipping information
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_zip',
        'shipping_country',
        // Billing information
        'billing_name',
        'billing_email',
        'billing_phone',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_zip',
        'billing_country',
    ];
    
    protected $casts = [
        'total_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Generate unique order number
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), 0, 8));
        } while (self::where('order_number', $orderNumber)->exists());
        
        return $orderNumber;
    }
    
    // Relationship with user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    // Relationship with order items
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Get order items count
    public function itemsCount()
    {
        return $this->items()->sum('quantity');
    }
    
    // Method to calculate the total amount from all order items
    public function calculateTotalAmount()
    {
        // Log the calculation starting
        \Illuminate\Support\Facades\Log::debug("Calculating total amount for order {$this->order_number}");
        
        // Use a direct database query for maximum reliability
        $total = $this->items()->sum('subtotal');
        
        // Make sure it's a float
        $total = (float)$total;
        
        // Log the result for debugging
        \Illuminate\Support\Facades\Log::debug("Order {$this->order_number} total calculated", [
            'items_count' => $this->items()->count(),
            'calculated_total' => $total
        ]);
        
        // Update the attribute
        $this->total_amount = $total;
        
        // Save to database
        $saved = $this->save();
        
        // Log the save result
        \Illuminate\Support\Facades\Log::debug("Order {$this->order_number} save result: " . ($saved ? 'success' : 'failed'));
        
        return $total;
    }
    
    // Check if the order can be cancelled
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }
    
    // Cancel the order
    public function cancel()
    {
        if (!$this->canBeCancelled()) {
            return false;
        }
        
        $this->status = self::STATUS_CANCELLED;
        $this->save();
        
        // Restore product stock
        foreach ($this->items as $item) {
            if ($item->product) {
                $item->product->stock += $item->quantity;
                $item->product->save();
            }
        }
        
        return true;
    }
    
    // Get formatted shipping address
    public function getFormattedShippingAddress()
    {
        $parts = array_filter([
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_zip,
            $this->shipping_country
        ]);
        
        return implode(', ', $parts);
    }
    
    // Get formatted billing address
    public function getFormattedBillingAddress()
    {
        $parts = array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_state,
            $this->billing_zip,
            $this->billing_country
        ]);
        
        return implode(', ', $parts);
    }
    
    // Get status label
    public function getStatusLabel()
    {
        return ucfirst($this->status);
    }
}
