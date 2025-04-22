<?php

use App\Http\Controllers\ShopController;
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\OrderManagement;
use App\Livewire\Customer\OrderDetail;
use App\Livewire\Customer\ProfileManagement;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');

// Customer routes protected by auth middleware
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard routes
    Route::get('/customer/dashboard', CustomerDashboard::class)->name('customer.dashboard');
    Route::get('/customer/orders', OrderManagement::class)->name('customer.orders');
    Route::get('/customer/orders/{orderId}', OrderDetail::class)->name('customer.order.detail');
    Route::get('/customer/profile', ProfileManagement::class)->name('customer.profile');
    
    // Default dashboard route
    Route::get('/dashboard', function () {
        return redirect()->route('customer.dashboard');
    })->name('dashboard');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
