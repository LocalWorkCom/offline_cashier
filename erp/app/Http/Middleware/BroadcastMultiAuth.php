<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BroadcastMultiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle($request, Closure $next)
    {
        if (Auth::guard('admin')->check()) {
            Auth::shouldUse('admin');
        } elseif (Auth::guard('employee')->check()) {
            Auth::shouldUse('employee');
        } else {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
