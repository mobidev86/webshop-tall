<?php

namespace App\Livewire\Shop;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder;

class ProductListing extends Component
{
    use WithPagination;
    
    public $search = '';
    public $selectedCategory = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';
    public $perPage = 12;
    
    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => ''],
        'sortBy' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 12],
    ];
    
    public function updatingSearch()
    {
        $this->resetPage();
    }
    
    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }
    
    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        
        $this->sortBy = $field;
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
    
    private function productsQuery(): Builder
    {
        $query = Product::query()
            ->where('is_active', true)
            ->orderBy($this->sortBy, $this->sortDirection);
            
        if ($this->search) {
            $query->where(function (Builder $query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->selectedCategory) {
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
    
    public function render()
    {
        return view('livewire.shop.product-listing', [
            'products' => $this->products,
            'categories' => $this->categories,
        ]);
    }
}
