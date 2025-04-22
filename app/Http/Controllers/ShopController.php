<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display the shop homepage with product listing.
     */
    public function index()
    {
        return view('shop.index');
    }
}
