<?php

use App\Http\Controllers\ShopController;
use App\Livewire\Customer\Dashboard as CustomerDashboard;
use App\Livewire\Customer\OrderManagement;
use App\Livewire\Customer\OrderDetail;
use App\Livewire\Customer\ProfileManagement;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CustomerAccessMiddleware;

// Make shop the default homepage
Route::get('/', [ShopController::class, 'index'])->name('shop.index');

// Customer routes protected by auth, verified, and customer middleware
Route::middleware(['auth', 'verified', CustomerAccessMiddleware::class])->group(function () {
    // Customer dashboard routes
    Route::get('/customer/dashboard', CustomerDashboard::class)->name('customer.dashboard');
    Route::get('/customer/orders', OrderManagement::class)->name('customer.orders');
    Route::get('/customer/orders/{orderId}', OrderDetail::class)->name('customer.order.detail');
    Route::get('/customer/profile', ProfileManagement::class)->name('customer.profile');
});

// Default dashboard route - will redirect to appropriate dashboard based on role
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('filament.admin.pages.dashboard');
        } else {
            return redirect()->route('customer.dashboard');
        }
    })->name('dashboard');
});

Route::view('profile', 'profile')
    ->middleware('auth')
    ->middleware(\App\Http\Middleware\AdminProfileRestrictMiddleware::class)
    ->name('profile');

require __DIR__.'/auth.php';
