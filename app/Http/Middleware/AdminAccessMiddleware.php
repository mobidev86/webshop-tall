<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccessMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Check if user is authenticated and has admin role
        if ($user && $user->role === User::ROLE_ADMIN) {
            return $next($request);
        }

        // If not admin, redirect to dashboard with error message
        return redirect()->route('dashboard')->with('error', 'You do not have permission to access the admin area.');
    }
}
