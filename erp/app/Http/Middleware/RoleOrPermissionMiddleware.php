<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleOrPermissionMiddleware
{
    public function handle($request, Closure $next, $roleOrPermission)
    {


        $user = auth('admin')->user();
        if ($user->hasRole($roleOrPermission, 'admin') || $user->hasPermissionTo($roleOrPermission, 'admin')) {
            return $next($request);
        }

        // Redirect to a 403 error page if the user doesn't have the required role or permission
        return redirect()->route('dashboard.error.403');
    }
}
