<?php

namespace App\Livewire\Products;

use App\Models\Product;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;

    public $quantity = 1;

    public function mount(Product $product)
    {
        $this->product = $product;
    }

    public function incrementQuantity()
    {
        if ($this->quantity < $this->product->stock) {
            $this->quantity++;
        }
    }

    public function decrementQuantity()
    {
        if ($this->quantity > 1) {
            $this->quantity--;
        }
    }

    public function render()
    {
        // Eager load categories
        $this->product->load('categories');

        // Get related products from the same categories
        $relatedProducts = Product::query()
            ->where('id', '!=', $this->product->id)
            ->where('is_active', true)
            ->whereHas('categories', function ($query) {
                $query->whereIn('categories.id', $this->product->categories->pluck('id'));
            })
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('livewire.products.product-show', [
            'product' => $this->product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    // Format price with 2 decimal places
    public function formatPrice($price)
    {
        return number_format($price, 2, '.', ',');
    }

    // Calculate total price based on quantity
    public function getTotalPrice()
    {
        $price = $this->product->isOnSale() ? $this->product->sale_price : $this->product->price;

        return $this->formatPrice($price * $this->quantity);
    }
}
