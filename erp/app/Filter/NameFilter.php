<?php

namespace App\Filter;
use Closure;

class NameFilter
{
    public function handle($request, Closure $next)
    {
        if(request()->filled('name')){
            $request->where('name', 'like', '%'.request('name').'%');
        }
        return $next($request);
    }

}
