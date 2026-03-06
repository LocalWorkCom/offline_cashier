<?php

namespace App\Filter;
use Closure;

class StartDateFilter
{
    public function handle($request, Closure $next)
    {
        if(request()->filled('start_date')){
            $request->where('created_at', '>=',request('start_date'));
        }
        return $next($request);
    }

}
