<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmployeeAuthMiddleware
{

    public function handle($request, Closure $next)
    {
        $lang = $request->header('lang', 'ar');
        if (!auth()->guard('employee')->check()) {
            return RespondWithBadRequest($lang, 4);

            // return response()->json(['message' => 'Unauthorized'], 401);
        }
        // if( CheckTokenEmployee() !== true) {
        //     $lang = $request->header('lang', 'ar');
        //     return RespondWithBadRequest($lang, 4);
        //     // return response()->json(['message' => 'Unauthorized'], 401);
        // }

        return $next($request);
    }
}
