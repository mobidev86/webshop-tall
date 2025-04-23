<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Cache;

class ProductListing extends Component
{
    use WithPagination;
    
    // Using public properties with proper type declarations
    public string $search = '';
    public ?int $selectedCategory = null;
    public string $sortBy = 'name';
    public string $sortDirection = 'asc';
    public int $perPage = 12;
    public string $searchPlaceholder = 'Search by product name, description, or category...';
    
    // Track component state for better UX
    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => null],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc']
    ];
    
    /**
     * Initialize the component
     */
    public function mount(): void
    {
        $this->normalizeSelectedCategory();
    }
    
    /**
     * Reset pagination when search is updated
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }
    
    /**
     * Reset pagination when category is updated
     */
    public function updatedSelectedCategory(): void
    {
        $this->resetPage();
    }
    
    /**
     * Reset pagination when sort parameters change
     */
    public function updatedSortBy(): void
    {
        $this->resetPage();
    }
    
    /**
     * Reset pagination when sort direction changes
     */
    public function updatedSortDirection(): void
    {
        $this->resetPage();
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
    
    /**
     * Select a category for filtering
     */
    public function selectCategory($categoryId): void
    {
        $this->selectedCategory = is_numeric($categoryId) ? (int)$categoryId : null;
        $this->resetPage();
    }
    
    /**
     * Clear category filter
     */
    public function clearCategory(): void
    {
        $this->selectedCategory = null;
        $this->resetPage();
    }
    
    /**
     * Update the sort direction
     */
    public function toggleSortDirection(): void
    {
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
    }
    
    /**
     * Reset all filters and sorting options
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'selectedCategory', 'sortBy', 'sortDirection']);
        $this->resetPage();
    }
    
    /**
     * Get computed property for products
     */
    #[Computed]
    public function products()
    {
        return $this->productsQuery()->paginate($this->perPage);
    }
    
    /**
     * Get computed property for categories
     */
    #[Computed]
    public function categories()
    {
        return Cache::remember('active-categories', 600, function () {
            return Category::where('is_active', true)
                ->withCount('products')
                ->orderBy('name')
                ->get();
        });
    }
    
    /**
     * Get computed property for no results message
     */
    #[Computed]
    public function noResultsMessage(): string
    {
        $message = 'No products found';
        
        if ($this->search) {
            $message .= " matching \"" . htmlspecialchars($this->search, ENT_QUOTES, 'UTF-8') . "\"";
        }
        
        if ($this->selectedCategory) {
            $category = Category::find($this->selectedCategory);
            if ($category) {
                // Use htmlspecialchars_decode to convert any HTML entities back to their character equivalents
                $categoryName = htmlspecialchars_decode($category->name);
                $message .= " in the \"" . $categoryName . "\" category";
            }
        }
        
        return $message;
    }
    
    /**
     * Highlight search terms in product names
     */
    public function highlightSearchTerm(string $text): string
    {
        if (empty($this->search) || empty($text)) {
            return e($text);
        }
        
        $search = preg_quote($this->search, '/');
        return preg_replace('/(' . $search . ')/i', '<span class="bg-yellow-100 font-semibold">$1</span>', e($text));
    }
    
    /**
     * Build query for products based on filters
     */
    private function productsQuery(): Builder
    {
        $query = Product::query()
            ->where('is_active', true);
            
        // Apply search filter
        if ($this->search !== '') {
            $searchTerm = '%' . $this->search . '%';
            
            $query->where(function (Builder $query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm)
                    ->orWhereHas('categories', function (Builder $subQuery) use ($searchTerm) {
                        $subQuery->where('name', 'like', $searchTerm);
                    });
            });
        }
        
        // Apply category filter
        if ($this->selectedCategory) {
            $query->whereHas('categories', function (Builder $query) {
                $query->where('categories.id', $this->selectedCategory);
            });
        }
        
        // Apply sorting
        return $query->orderBy($this->sortBy, $this->sortDirection);
    }
    
    /**
     * Render the component
     */
    public function render()
    {
        return view('livewire.shop.product-listing', [
            'products' => $this->products,
            'categories' => $this->categories,
            'noResultsMessage' => $this->noResultsMessage
        ]);
    }
}

