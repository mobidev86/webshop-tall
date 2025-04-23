<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $order_number
 * @property int $user_id
 * @property string $status
 * @property float $total_amount
 * @property string|null $payment_method
 * @property string|null $shipping_method
 * @property string|null $notes
 * @property string|null $shipping_name
 * @property string|null $shipping_email
 * @property string|null $shipping_phone
 * @property string|null $shipping_address
 * @property string|null $shipping_city
 * @property string|null $shipping_state
 * @property string|null $shipping_zip
 * @property string|null $shipping_country
 * @property string|null $billing_name
 * @property string|null $billing_email
 * @property string|null $billing_phone
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_zip
 * @property string|null $billing_country
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory;

    // Order status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Array of valid order statuses
     */
    public const VALID_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_COMPLETED,
        self::STATUS_DECLINED,
        self::STATUS_CANCELLED,
    ];

    /**
     * Statuses that allow cancellation
     */
    public const CANCELLABLE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
    ];

    /**
     * @var array<int, string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'total_amount' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(substr(uniqid(), 0, 8));
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Relationship with user
     * 
     * @return BelongsTo<User, Order>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship with order items
     * 
     * @return HasMany<OrderItem>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get order items count
     */
    public function itemsCount(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /**
     * Method to calculate the total amount from all order items
     * and update the order's total_amount attribute
     */
    public function calculateTotalAmount(): float
    {
        $total = $this->items()
            ->select(DB::raw('SUM(price * quantity) as total'))
            ->value('total') ?? 0;

        // Convert to float
        $total = (float) $total;

        // Update the order model
        $this->total_amount = $total;
        $this->save();

        return $total;
    }

    /**
     * Check if the order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, self::CANCELLABLE_STATUSES, true);
    }

    /**
     * Cancel the order and restore product stock
     */
    public function cancel(): bool
    {
        if (! $this->canBeCancelled()) {
            return false;
        }

        DB::transaction(function () {
            // Update order status
            $this->status = self::STATUS_CANCELLED;
            $this->save();

            // Restore product stock
            foreach ($this->items as $item) {
                $product = $item->product;
                if ($product !== null) {
                    $product->increment('stock', $item->quantity);
                }
            }
        });

        return true;
    }

    /**
     * Get formatted shipping address
     */
    public function getFormattedShippingAddress(): string
    {
        $parts = array_filter([
            $this->shipping_address,
            $this->shipping_city,
            $this->shipping_state,
            $this->shipping_zip,
            $this->shipping_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get formatted billing address
     */
    public function getFormattedBillingAddress(): string
    {
        $parts = array_filter([
            $this->billing_address,
            $this->billing_city,
            $this->billing_state,
            $this->billing_zip,
            $this->billing_country,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get status label with proper formatting
     */
    public function getStatusLabel(): string
    {
        return ucfirst($this->status);
    }

    /**
     * Scope for finding orders by status
     * 
     * @param Builder<Order> $query
     * @param string $status
     * @return Builder<Order>
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for finding orders by user
     * 
     * @param Builder<Order> $query
     * @param int $userId
     * @return Builder<Order>
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
