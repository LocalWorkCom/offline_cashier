<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('admin')->user();

        // Allow access if the user is authenticated and has 'admin' flag
        if ($user && $user->flag === 'admin') {
            return $next($request);
        }
        if ($request->is('broadcasting/auth')) {
            return $request;
        }
        // If the request expects JSON (e.g., API request), return a JSON response
        if ($request->expectsJson()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized, admin access required'
            ], 403);
        }

        // Redirect to the admin login page for web requests
        return redirect()->route('dashboard.login');
    }
}
