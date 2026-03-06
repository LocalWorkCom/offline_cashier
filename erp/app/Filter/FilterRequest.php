<?php

namespace App\Filter;

use Closure;
use Illuminate\Support\Str;

class FilterRequest
{
    public function handle($request, Closure $next)
    {
        $filter = class_basename($this); //SubjectIdFilter
        $filter = str_replace('Filter', '', $filter); //SubjectId
        $filter = Str::snake($filter); //subject_id
//        dd($filter);
        if(request()->filled($filter)){
            return $next($request)->where($filter,request($filter));
        }
        return $next($request);
    }

}
