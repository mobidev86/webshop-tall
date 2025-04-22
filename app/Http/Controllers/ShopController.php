<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the shop homepage with product listing.
     * This now serves as the default homepage.
     */
    public function index()
    {
        return view('shop.index');
    }
}
