<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
    ];

    /**
     * Relationship with parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Relationship with child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Relationship with products (BelongsToMany)
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Helper method to get all active categories
     */
    public static function getActive(): Collection
    {
        return Cache::remember('categories-active', 3600, function () {
            return static::where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Get all descendants (nested subcategories) recursively
     *
     * @param  bool  $activeOnly  Filter only active categories
     */
    public function getAllDescendants(bool $activeOnly = false): Collection
    {
        $cacheKey = "category-{$this->id}-descendants" . ($activeOnly ? '-active' : '');

        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $descendants = collect();

            // Get immediate children
            $children = $activeOnly
                ? $this->children()->where('is_active', true)->get()
                : $this->children()->get();

            // Add immediate children to descendants
            $descendants = $descendants->merge($children);

            // Recursively add children's descendants
            foreach ($children as $child) {
                $descendants = $descendants->merge($child->getAllDescendants($activeOnly));
            }

            return $descendants;
        });
    }

    /**
     * Get all ancestors (parent chain) recursively
     *
     * @param  bool  $activeOnly  Filter only active categories
     */
    public function getAllAncestors(bool $activeOnly = false): Collection
    {
        $cacheKey = "category-{$this->id}-ancestors" . ($activeOnly ? '-active' : '');

        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $ancestors = collect();

            $parent = $activeOnly
                ? $this->parent()->where('is_active', true)->first()
                : $this->parent;

            if ($parent) {
                $ancestors->push($parent);
                $ancestors = $ancestors->merge($parent->getAllAncestors($activeOnly));
            }

            return $ancestors;
        });
    }

    /**
     * Check if category has any descendants
     */
    public function hasDescendants(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get nested tree hierarchy of the category with its descendants
     * Returns an array with the category and a 'children' key containing subcategories
     *
     * @param  bool  $activeOnly  Filter only active categories
     */
    public function getNestedTree(bool $activeOnly = false): array
    {
        $cacheKey = "category-{$this->id}-tree" . ($activeOnly ? '-active' : '');

        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $children = $activeOnly
                ? $this->children()->where('is_active', true)->get()
                : $this->children()->get();

            $result = $this->toArray();
            $result['children'] = [];

            foreach ($children as $child) {
                $result['children'][] = $child->getNestedTree($activeOnly);
            }

            return $result;
        });
    }

    /**
     * Get all root categories (categories without parents)
     *
     * @param  bool  $activeOnly  Filter only active categories
     */
    public static function getRootCategories(bool $activeOnly = false): Collection
    {
        $cacheKey = 'categories-root' . ($activeOnly ? '-active' : '');

        return Cache::remember($cacheKey, 3600, function () use ($activeOnly) {
            $query = static::whereNull('parent_id')->orderBy('name');

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            return $query->get();
        });
    }

    /**
     * Scope query to only include active categories
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to only include root categories
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Check if category is a root category
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * Clear category cache when model is updated
     */
    public static function bootCategory(): void
    {
        static::saved(function ($category) {
            Cache::forget("category-{$category->id}-descendants");
            Cache::forget("category-{$category->id}-descendants-active");
            Cache::forget("category-{$category->id}-ancestors");
            Cache::forget("category-{$category->id}-ancestors-active");
            Cache::forget("category-{$category->id}-tree");
            Cache::forget("category-{$category->id}-tree-active");
            Cache::forget('categories-root');
            Cache::forget('categories-root-active');
            Cache::forget('categories-active');
        });

        static::deleted(function ($category) {
            Cache::forget("category-{$category->id}-descendants");
            Cache::forget("category-{$category->id}-descendants-active");
            Cache::forget("category-{$category->id}-ancestors");
            Cache::forget("category-{$category->id}-ancestors-active");
            Cache::forget("category-{$category->id}-tree");
            Cache::forget("category-{$category->id}-tree-active");
            Cache::forget('categories-root');
            Cache::forget('categories-root-active');
            Cache::forget('categories-active');
        });
    }
}
