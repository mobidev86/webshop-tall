<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has customer role
        if (auth()->check() && auth()->user()->role === User::ROLE_CUSTOMER) {
            return $next($request);
        }

        // If not customer, redirect with error message
        if (auth()->user() && auth()->user()->role === User::ROLE_ADMIN) {
            // For admin users, redirect to admin dashboard
            return redirect()->route('filament.admin.pages.dashboard')->with('error', 'Admin users cannot access the customer dashboard.');
        }

        // For other users or guests
        return redirect()->route('shop.index')->with('error', 'You do not have permission to access the customer area.');
    }
} 