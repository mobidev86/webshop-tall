<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        return view('products.index');
    }

    /**
     * Display the specified product.
     */
    public function show(Request $request, Product $product)
    {
        return view('products.show', [
            'product' => $product,
        ]);
    }
}
