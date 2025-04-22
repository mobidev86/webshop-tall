<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

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
    
    // Relationship with parent category
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    // Relationship with child categories
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    
    // Relationship with products (BelongsToMany)
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }
    
    // Helper method to get all active categories
    public static function getActive()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Get all descendants (nested subcategories) recursively
     * 
     * @param bool $activeOnly Filter only active categories
     * @return Collection
     */
    public function getAllDescendants(bool $activeOnly = false): Collection
    {
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
    }
    
    /**
     * Get all ancestors (parent chain) recursively
     * 
     * @param bool $activeOnly Filter only active categories
     * @return Collection
     */
    public function getAllAncestors(bool $activeOnly = false): Collection
    {
        $ancestors = collect();
        
        $parent = $activeOnly 
            ? $this->parent()->where('is_active', true)->first() 
            : $this->parent;
            
        if ($parent) {
            $ancestors->push($parent);
            $ancestors = $ancestors->merge($parent->getAllAncestors($activeOnly));
        }
        
        return $ancestors;
    }
    
    /**
     * Check if category has any descendants
     * 
     * @return bool
     */
    public function hasDescendants(): bool
    {
        return $this->children()->exists();
    }
    
    /**
     * Get nested tree hierarchy of the category with its descendants
     * Returns an array with the category and a 'children' key containing subcategories
     * 
     * @param bool $activeOnly Filter only active categories
     * @return array
     */
    public function getNestedTree(bool $activeOnly = false): array
    {
        $children = $activeOnly 
            ? $this->children()->where('is_active', true)->get() 
            : $this->children()->get();
            
        $result = $this->toArray();
        $result['children'] = [];
        
        foreach ($children as $child) {
            $result['children'][] = $child->getNestedTree($activeOnly);
        }
        
        return $result;
    }
    
    /**
     * Get all root categories (categories without parents)
     * 
     * @param bool $activeOnly Filter only active categories
     * @return Collection
     */
    public static function getRootCategories(bool $activeOnly = false): Collection
    {
        $query = static::whereNull('parent_id');
        
        if ($activeOnly) {
            $query->where('is_active', true);
        }
        
        return $query->get();
    }
}
