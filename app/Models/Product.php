<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'features',
        'price',
        'sale_price',
        'stock',
        'sku',
        'is_active',
        'is_featured',
        'image',
    ];
    
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];
    
    // Relationship with categories (BelongsToMany)
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }
    
    // Relationship with order items
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    
    // Check if product is on sale
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }
    
    // Get current price (sale price if on sale, regular price otherwise)
    public function getCurrentPrice()
    {
        return $this->isOnSale() ? $this->sale_price : $this->price;
    }
    
    // Check if product is in stock
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }
    
    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    // Scope for featured products
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
}
