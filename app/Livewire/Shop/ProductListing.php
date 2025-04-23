<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Cache;

class ProductListing extends Component
{
    use WithPagination;
    
    #[Url]
    public string $search = '';
    
    /**
     * Selected category ID (integer) or null for all categories
     * @var int|null
     */
    #[Url]
    public ?int $selectedCategory = null;
    
    #[Url]
    public string $sortBy = 'name';
    
    #[Url]
    public string $sortDirection = 'asc';
    
    public int $perPage = 12;
    public string $searchPlaceholder = 'Search by product name, description, or category...';
    
    /**
     * Initialize the component
     */
    public function mount(): void
    {
        $this->normalizeSelectedCategory();
    }
    
    /**
     * Called after the component is hydrated but before an action is performed
     */
    public function hydrate(): void
    {
        $this->normalizeSelectedCategory();
    }
    
    /**
     * Normalize the selectedCategory to ensure it's properly typed
     */
    private function normalizeSelectedCategory(): void
    {
        if ($this->selectedCategory === '') {
            $this->selectedCategory = null;
        } elseif (is_numeric($this->selectedCategory)) {
            $this->selectedCategory = (int)$this->selectedCategory;
        }
    }
    
    #[On('search-submitted')]
    public function updateSearch(string $search): void
    {
        $this->search = $search;
        $this->resetPage();
    }
    
    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    
    public function updatingSelectedCategory($value): void
    {
        // Ensure empty string is treated as null
        if ($value === '') {
            $this->selectedCategory = null;
        }
        
        $this->resetPage();
    }
    
    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->set('sortDirection', $this->sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            $this->set('sortDirection', 'asc');
        }
        
        $this->set('sortBy', $field);
    }
    
    public function getProductsProperty()
    {
        return $this->productsQuery()
            ->paginate($this->perPage);
    }
    
    public function getCategoriesProperty()
    {
        // Add cache with 10-minute timeout for categories list
        return Cache::remember('active-categories', 600, function () {
            return Category::where('is_active', true)
                ->orderBy('name')
                ->get();
        });
    }
    
    public function getNoResultsMessageProperty(): string
    {
        $message = 'No products found';
        
        if ($this->search) {
            $message .= " matching \"" . e($this->search) . "\"";
        }
        
        if ($this->selectedCategory) {
            $category = Category::find($this->selectedCategory);
            if ($category) {
                $message .= " in the \"" . e($category->name) . "\" category";
            }
        }
        
        return $message;
    }
    
    public function highlightSearchTerm(string $text): string
    {
        if (empty($this->search) || empty($text)) {
            return $text;
        }
        
        $search = preg_quote($this->search, '/');
        return preg_replace('/(' . $search . ')/i', '<span class="bg-yellow-100 font-semibold">$1</span>', e($text));
    }
    
    private function productsQuery(): Builder
    {
        $query = Product::query()
            ->where('is_active', true);
            
        // Apply search filter
        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            
            $query->where(function (Builder $query) use ($searchTerm) {
                // Search in product name and description
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    // Search in categories
                    ->orWhereHas('categories', function (Builder $subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm);
                    });
            });
        }
        
        // Only apply category filter if a valid category is selected
        if ($this->selectedCategory) {
            $categoryIds = $this->getCategoryIdsForFilter($this->selectedCategory);
            
            if (!empty($categoryIds)) {
                $query->whereHas('categories', function (Builder $query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            }
        }
        
        // Apply sorting after all filters
        return $query->orderBy($this->sortBy, $this->sortDirection);
    }
    
    /**
     * Get an array of category IDs to filter by, including descendants
     */
    private function getCategoryIdsForFilter(int $categoryId): array
    {
        // Cache category IDs for 5 minutes
        return Cache::remember("category-ids-{$categoryId}", 300, function () use ($categoryId) {
            $category = Category::find($categoryId);
            
            if (!$category) {
                return [];
            }
            
            $categoryIds = [$category->id];
            $descendants = $category->getAllDescendants();
            
            if ($descendants->count() > 0) {
                $categoryIds = array_merge($categoryIds, $descendants->pluck('id')->toArray());
            }
            
            return $categoryIds;
        });
    }
    
    /**
     * Set the selected category and reset pagination
     */
    public function selectCategory($categoryId): void
    {
        // Convert to integer if numeric
        $this->selectedCategory = is_numeric($categoryId) ? (int)$categoryId : $categoryId;
        $this->resetPage();
    }
    
    /**
     * Clear the selected category and reset pagination
     */
    public function clearCategory(): void
    {
        // Force type conversion to ensure proper null/empty state
        $this->selectedCategory = null;
        
        // Reset page to avoid pagination issues
        $this->resetPage();
    }
    
    /**
     * Toggle sort direction between asc and desc
     */
    public function toggleSortDirection(): void
    {
        $this->set('sortDirection', $this->sortDirection === 'asc' ? 'desc' : 'asc');
        $this->resetPage();
    }
    
    /**
     * Reset all filters and refresh the component
     */
    public function resetFilters()
    {
        // Reset all filterable properties
        $this->reset(['selectedCategory', 'search', 'sortBy', 'sortDirection']);
        $this->resetPage();
        
        // Force a complete component refresh
        return redirect()->to(request()->header('Referer'));
    }
    
    public function render()
    {
        return view('livewire.shop.product-listing', [
            'products' => $this->products,
            'categories' => $this->categories,
            'noResultsMessage' => $this->noResultsMessage
        ]);
    }
}

