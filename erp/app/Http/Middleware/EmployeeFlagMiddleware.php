<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeFlagMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $flag)
    {
        $lang = $request->header('lang', 'ar');

        $user = auth()->guard('employee')->user();

        $allowedFlags = explode('|', $flag);
        if (!$user  ||!in_array($user->flag, $allowedFlags)) {
            return RespondWithBadRequest($lang, 102);
        }

        return $next($request);
    }
}
