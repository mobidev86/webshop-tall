<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $features
 * @property float $price
 * @property float|null $sale_price
 * @property int $stock
 * @property string|null $sku
 * @property bool $is_active
 * @property bool $is_featured
 * @property string|null $image
 * @property string|null $image_url
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
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

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    /**
     * Get the URL for the product image
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return asset('storage/' . $this->image);
    }

    /**
     * Relationship with categories (BelongsToMany)
     * 
     * @return BelongsToMany<Category>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Relationship with order items
     * 
     * @return HasMany<OrderItem>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if product is on sale
     */
    public function isOnSale(): bool
    {
        return $this->sale_price !== null && $this->sale_price < $this->price;
    }

    /**
     * Get current price (sale price if on sale, regular price otherwise)
     */
    public function getCurrentPrice(): float
    {
        return $this->isOnSale() ? (float) $this->sale_price : (float) $this->price;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * Get the discount percentage if on sale
     */
    public function getDiscountPercentage(): ?int
    {
        if (! $this->isOnSale()) {
            return null;
        }

        $regularPrice = (float) $this->price;
        $salePrice = (float) $this->sale_price;

        if ($regularPrice <= 0) {
            return null;
        }

        return (int) round(100 - ($salePrice / $regularPrice * 100));
    }

    /**
     * Scope for active products
     * 
     * @param Builder<Product> $query
     * @return Builder<Product>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for featured products
     * 
     * @param Builder<Product> $query
     * @return Builder<Product>
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for products on sale
     * 
     * @param Builder<Product> $query
     * @return Builder<Product>
     */
    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereNotNull('sale_price')
            ->whereRaw('sale_price < price');
    }

    /**
     * Scope for products in stock
     * 
     * @param Builder<Product> $query
     * @return Builder<Product>
     */
    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope for products in a specific category
     * 
     * @param Builder<Product> $query
     * @param int $categoryId
     * @return Builder<Product>
     */
    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $query) use ($categoryId) {
            $query->where('categories.id', $categoryId);
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Get related products in the same categories
     *
     * @param int $limit Maximum number of related products to return
     * @return Collection<int, Product> Related products
     */
    public function getRelatedProducts(int $limit = 4): Collection
    {
        $cacheKey = "product-{$this->id}-related-{$limit}";

        return Cache::remember($cacheKey, 3600, function () use ($limit) {
            $categoryIds = $this->categories->pluck('id')->toArray();

            if (empty($categoryIds)) {
                return self::active()->inStock()->where('id', '!=', $this->id)
                    ->latest()->take($limit)->get();
            }

            return self::active()->inStock()
                ->where('id', '!=', $this->id)
                ->whereHas('categories', function (Builder $query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                })
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get featured products with caching
     *
     * @param int $limit Maximum number of featured products to return
     * @param bool $inStockOnly Whether to show only in-stock products
     * @return Collection<int, Product> Featured products
     */
    public static function getFeaturedProducts(int $limit = 6, bool $inStockOnly = true): Collection
    {
        $cacheKey = "products-featured-{$limit}-" . ($inStockOnly ? 'instock' : 'all');

        return Cache::remember($cacheKey, 3600, function () use ($limit, $inStockOnly) {
            $query = self::active()->featured();

            if ($inStockOnly) {
                $query->inStock();
            }

            return $query->with('categories')
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get products on sale with caching
     *
     * @param int $limit Maximum number of sale products to return
     * @return Collection<int, Product> Products on sale
     */
    public static function getOnSaleProducts(int $limit = 8): Collection
    {
        return Cache::remember("products-sale-{$limit}", 3600, function () use ($limit) {
            return self::active()
                ->inStock()
                ->onSale()
                ->with('categories')
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Get newest products with caching
     *
     * @param int $limit Maximum number of products to return
     * @return Collection<int, Product> Newest products
     */
    public static function getNewestProducts(int $limit = 8): Collection
    {
        return Cache::remember("products-newest-{$limit}", 3600, function () use ($limit) {
            return self::active()
                ->inStock()
                ->with('categories')
                ->latest()
                ->take($limit)
                ->get();
        });
    }

    /**
     * Clear product cache when model is updated
     */
    protected static function booted(): void
    {
        static::saved(function ($product) {
            // Clear individual product caches
            Cache::forget("product-{$product->id}-related-4");

            // Clear collection caches
            Cache::forget('products-featured-6-instock');
            Cache::forget('products-featured-6-all');
            Cache::forget('products-sale-8');
            Cache::forget('products-newest-8');
        });

        static::deleted(function ($product) {
            // Clear individual product caches
            Cache::forget("product-{$product->id}-related-4");

            // Clear collection caches
            Cache::forget('products-featured-6-instock');
            Cache::forget('products-featured-6-all');
            Cache::forget('products-sale-8');
            Cache::forget('products-newest-8');
        });
    }
}
