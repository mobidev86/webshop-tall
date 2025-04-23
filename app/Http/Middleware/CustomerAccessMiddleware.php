<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        // If not authenticated
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to access the customer area.');
        }
        
        // Check if user has customer role
        if ($user->role === User::ROLE_CUSTOMER) {
            return $next($request);
        }

        // For admin users, redirect to admin dashboard
        if ($user->role === User::ROLE_ADMIN) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Admin users cannot access the customer dashboard.');
        }

        // For other users with invalid roles
        return redirect()->route('shop.index')
            ->with('error', 'You do not have permission to access the customer area.');
    }
}
