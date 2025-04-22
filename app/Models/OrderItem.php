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
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];
    
    // Relationship with order
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    
    // Relationship with product
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
    
    // Calculate subtotal
    public function calculateSubtotal()
    {
        return $this->price * $this->quantity;
    }
}
