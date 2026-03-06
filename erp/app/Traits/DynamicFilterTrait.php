<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

trait DynamicFilter
{
    /**
     * Apply filters dynamically based on the request.
     */
    // public function scopeApplyFilters(Builder $query, array $filters = [])
    // {
    //     foreach ($filters as $key => $value) {
    //         if (empty($value) && $value !== 0) continue;

    //         // handle relations like branch.name or country.id
    //         if (str_contains($key, '.')) {
    //             [$relation, $column] = explode('.', $key, 2);

    //             $query->whereHas($relation, function ($q) use ($column, $value) {
    //                 $q->where($column, $value);
    //             });
    //         } else {
    //             // direct column filter
    //             if (schema()->hasColumn($query->getModel()->getTable(), $key)) {
    //                 $query->where($key, $value);
    //             }
    //         }
    //     }

    //     return $query;
    // }

    public function scopeApplyFilters(Builder $query, array $filters = [])
    {
        foreach ($filters as $key => $value) {
            if (empty($value) && $value !== '0') continue;

            if (str_contains($key, '.')) {
                [$relation, $column] = explode('.', $key, 2);
                $query->whereHas($relation, function ($q) use ($column, $value) {
                    $q->where($column, $value);
                });
            } else {
                $table = $query->getModel()->getTable();
                if (Schema::hasColumn($table, $key)) {
                    $query->where($key, $value);
                }
            }
        }

        return $query;
    }

    /**
     * Apply a free-text search across multiple fields & relations.
     */
    public function scopeSearch(Builder $query, ?string $term, array $columns = [])
    {
        if (!$term) return $query;

        $query->where(function ($q) use ($term, $columns) {
            foreach ($columns as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relCol] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($relQ) use ($relCol, $term) {
                        $relQ->where($relCol, 'like', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'like', "%{$term}%");
                }
            }
        });

        return $query;
    }




    public function data()
    {
        //1
        $chefQuery = Employee::where('flag', 'chef');
        $assignedQuery = ChefCuisineCategory::with([
            'chef',
            'chef.branch',
            'cuisineCategory.dish_category',
            'cuisineCategory.cuisine'
        ])->whereHas('chef');

        //2
        if (auth('admin')->user() && auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $chefQuery->whereHas('branch', fn($q) => $q->where('id', $branch_id));
                $assignedQuery->whereHas('chef.branch', fn($q) => $q->where('id', $branch_id));
            }
        } elseif (auth('employee')->user() && auth('employee')->user()->hasRole('Branch_Manager')) {
            $chefId = auth('employee')->id();
            $chefQuery->where('id', $chefId);
            $assignedQuery->where('employee_id', $chefId);
        }

        //3
        $filters = $request->only(['chef.branch.id', 'cuisineCategory.id']);
        $searchTerm = $request->input('search'); // user can type 'مقبلات' or chef name
        $assignedQuery
            ->applyFilters($filters)
            ->search($searchTerm, [
                'chef.name',                  // search by chef name
                'chef.branch.name',           // search by branch name
                'cuisineCategory.name',       // search by cuisine category
                'cuisineCategory.cuisine.name'// search by cuisine name
            ]);

        //4
        $assignedCuisines = $assignedQuery->paginate(10);
        $chefs = $chefQuery->get();
        $cuisineCategories = CuisineCategory::all();
        return [
            'success' => true,
            'data' => [
                'assignedCuisines' => $assignedCuisines,
                'chefs' => $chefs,
                'cuisineCategories' => $cuisineCategories
            ]
        ];
    }
}