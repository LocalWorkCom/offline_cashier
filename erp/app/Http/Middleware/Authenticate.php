<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->is('broadcasting/auth')) {
            return null;
        }
        if ($request->expectsJson()) {
            return null;
        }
        $currentRouteName = Route::currentRouteName();
        $currentRoute = Route::current();
        $currentUri = $currentRoute ? $currentRoute->uri() : 'Unknown';
        // Check if the request is for the dashboard
        if ($currentUri == 'dashboard') {
            return route('dashboard.login'); // Redirect to the dashboard login page
        } else {
            return route('home');
        }

        // Default fallback
    }
}
