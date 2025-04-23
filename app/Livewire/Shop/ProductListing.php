<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;

class ProductListing extends Component
{
    use WithPagination;
    
    #[Url]
    public $search = '';
    
    /**
     * Selected category ID (integer) or null for all categories
     * @var int|null
     */
    #[Url]
    public $selectedCategory = null;
    
    #[Url]
    public $sortBy = 'name';
    
    #[Url]
    public $sortDirection = 'asc';
    
    public $perPage = 12;
    public $searchPlaceholder = 'Search by product name, description, or category...';
    
    /**
     * Initialize the component
     */
    public function mount()
    {
        // Ensure selectedCategory is properly initialized
        if ($this->selectedCategory === '') {
            $this->selectedCategory = null;
        } elseif (is_numeric($this->selectedCategory)) {
            $this->selectedCategory = (int)$this->selectedCategory;
        }
    }
    
    /**
     * Called after the component is hydrated but before an action is performed
     */
    public function hydrate()
    {
        // Ensure proper type handling after hydration
        if ($this->selectedCategory === '') {
            $this->selectedCategory = null;
        } elseif (is_numeric($this->selectedCategory)) {
            $this->selectedCategory = (int)$this->selectedCategory;
        }
    }
    
    #[On('search-submitted')]
    public function updateSearch($search)
    {
        $this->search = $search;
        $this->resetPage();
    }
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedCategory($value)
    {
        // Ensure empty string is treated as null
        if ($value === '') {
            $this->selectedCategory = null;
        }
        
        $this->resetPage();
    }
    
    public function sortBy($field)
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
        return Category::where('is_active', true)
            ->orderBy('name')
            ->get();
    }
    
    public function getNoResultsMessageProperty()
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
    
    public function highlightSearchTerm($text)
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
            ->where('is_active', true)
            ->orderBy($this->sortBy, $this->sortDirection);
            
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
        if ($this->selectedCategory && is_numeric($this->selectedCategory)) {
            $category = Category::find($this->selectedCategory);
            
            if ($category) {
                $categoryIds = collect([$category->id]);
                $descendants = $category->getAllDescendants();
                
                if ($descendants->count() > 0) {
                    $categoryIds = $categoryIds->merge($descendants->pluck('id'));
                }
                
                $query->whereHas('categories', function (Builder $query) use ($categoryIds) {
                    $query->whereIn('categories.id', $categoryIds);
                });
            }
        }
        
        return $query;
    }
    
    /**
     * Set the selected category and reset pagination
     */
    public function selectCategory($categoryId)
    {
        // Convert to integer if numeric
        $this->selectedCategory = is_numeric($categoryId) ? (int)$categoryId : $categoryId;
        $this->resetPage();
    }
    
    /**
     * Clear the selected category and reset pagination
     */
    public function clearCategory()
    {
        // Force type conversion to ensure proper null/empty state
        $this->selectedCategory = null;
        
        // Reset page to avoid pagination issues
        $this->resetPage();
        
        // Refresh the component to ensure proper re-rendering
        $this->dispatch('refresh');
    }
    
    /**
     * Toggle sort direction between asc and desc
     */
    public function toggleSortDirection()
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
