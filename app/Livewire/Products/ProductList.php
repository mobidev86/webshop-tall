<?php

namespace App\Livewire\Products;

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    #[Url]
    public $search = '';

    #[Url]
    public $categorySlug = '';

    #[Url]
    public $sortBy = 'newest'; // Options: newest, price_asc, price_desc

    public $perPage = 12;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategorySlug()
    {
        $this->resetPage();
    }

    public function updatedSortBy()
    {
        $this->resetPage();
    }

    public function setCategory($slug = '')
    {
        $this->categorySlug = $slug;
        $this->resetPage();
    }

    public function render()
    {
        $productsQuery = Product::query()
            ->where('is_active', true);

        if ($this->search) {
            $productsQuery->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categorySlug) {
            $category = Category::where('slug', $this->categorySlug)->first();
            if ($category) {
                $productsQuery->whereHas('categories', function ($query) use ($category) {
                    $query->where('categories.id', $category->id);
                });
            }
        }

        switch ($this->sortBy) {
            case 'price_asc':
                $productsQuery->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $productsQuery->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $productsQuery->orderBy('created_at', 'desc');
                break;
        }

        $products = $productsQuery->paginate($this->perPage);

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->where('is_active', true);
            }])
            ->get();

        return view('livewire.products.product-list', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    // Format price with 2 decimal places
    public function formatPrice($price)
    {
        return number_format($price, 2, '.', ',');
    }
}
