<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];
    
    protected $casts = [
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];
    
    /**
     * Relationship with order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Relationship with product
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
        static::creating(function ($orderItem) {
            if (empty($orderItem->subtotal)) {
                $orderItem->subtotal = $orderItem->calculateSubtotal();
            }
        });
        
        static::updating(function ($orderItem) {
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
