<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ProductListing extends Component
{
    use WithPagination;

    // Public properties without URL tracking for AJAX-based filtering
    public string $search = '';

    public ?int $selectedCategory = null;

    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public int $perPage = 12;

    public string $searchPlaceholder = 'Search by product name, description, or category...';

    public bool $isLoading = false;

    /**
     * Initialize the component
     */
    public function mount(): void
    {
        $this->normalizeSelectedCategory();
    }

    /**
     * Before any update is performed, set loading state
     */
    public function updating($name, $value): void
    {
        $this->isLoading = true;
    }

    /**
     * After any property is updated, reset loading state
     */
    public function updated($name, $value): void
    {
        $this->isLoading = false;
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
            $this->selectedCategory = (int) $this->selectedCategory;
        }
    }

    /**
     * Select a category for filtering using AJAX
     */
    public function selectCategory($categoryId): void
    {
        $this->isLoading = true;
        $this->selectedCategory = is_numeric($categoryId) ? (int) $categoryId : null;
        $this->resetPage();
        $this->isLoading = false;
    }

    /**
     * Clear category filter using AJAX
     */
    public function clearCategory(): void
    {
        $this->isLoading = true;
        $this->selectedCategory = null;
        $this->resetPage();
        $this->isLoading = false;
    }

    /**
     * Update the sort direction using AJAX
     */
    public function toggleSortDirection(): void
    {
        $this->isLoading = true;
        $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->resetPage();
        $this->isLoading = false;
    }

    /**
     * Update sort field using AJAX
     */
    public function updateSort(string $field): void
    {
        $this->isLoading = true;
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
        $this->isLoading = false;
    }

    /**
     * Reset all filters and sorting options using AJAX
     */
    public function resetFilters(): void
    {
        $this->isLoading = true;
        $this->reset(['search', 'selectedCategory', 'sortBy', 'sortDirection']);
        $this->resetPage();
        $this->isLoading = false;
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
            $message .= ' matching "' . htmlspecialchars($this->search, ENT_QUOTES, 'UTF-8') . '"';
        }

        if ($this->selectedCategory) {
            $category = Category::find($this->selectedCategory);
            if ($category) {
                // Use htmlspecialchars_decode to convert any HTML entities back to their character equivalents
                $categoryName = htmlspecialchars_decode($category->name);
                $message .= ' in the "' . $categoryName . '" category';
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
            'noResultsMessage' => $this->noResultsMessage,
        ]);
    }
}
