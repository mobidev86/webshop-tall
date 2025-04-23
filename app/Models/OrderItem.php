<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property int $quantity
 * @property float $price
 * @property float $subtotal
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class OrderItem extends Model
{
    /** @use HasFactory<\Database\Factories\OrderItemFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relationship with order
     * 
     * @return BelongsTo<Order, OrderItem>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with product
     * 
     * @return BelongsTo<Product, OrderItem>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate subtotal
     */
    public function calculateSubtotal(): float
    {
        return (float) ($this->price * $this->quantity);
    }

    /**
     * Update subtotal when saving a new record
     */
    protected static function booted(): void
    {
        static::creating(function (OrderItem $orderItem) {
            if (empty($orderItem->subtotal)) {
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });

        static::updating(function (OrderItem $orderItem) {
            if ($orderItem->isDirty(['price', 'quantity'])) {
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return '$' . number_format((float) $this->price, 2);
    }

    /**
     * Get formatted subtotal
     */
    public function getFormattedSubtotal(): string
    {
        return '$' . number_format((float) $this->subtotal, 2);
    }
}
