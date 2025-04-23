<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminProfileRestrictMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        // Check if user is authenticated and has admin role
        if ($user && $user->isAdmin()) {
            return redirect()->route('filament.admin.pages.dashboard')
                ->with('error', 'Admin users cannot access the profile page.');
        }

        return $next($request);
    }
}
