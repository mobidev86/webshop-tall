<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ShopController extends Controller
{
    /**
     * Display the shop homepage with product listing.
     * This now serves as the default homepage.
     */
    public function index(): View
    {
        // Get featured products using optimized caching method
        $featuredProducts = Product::getFeaturedProducts(6);

        // Get products on sale
        $saleProducts = Product::getOnSaleProducts(4);

        // Get newest products
        $newProducts = Product::getNewestProducts(8);

        return view('shop.index', compact('featuredProducts', 'saleProducts', 'newProducts'));
    }
}
