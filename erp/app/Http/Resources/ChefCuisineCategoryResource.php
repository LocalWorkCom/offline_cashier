<?php

namespace App\Http\Resources;

use App\Models\BranchMenu;
use App\Models\Dish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChefCuisineCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $lang = $request->header('lang', 'en'); // default en
        return [
            'employee' => $this->chef ? [
                'id' => $this->chef->id,
                'name' => $this->chef->first_name . ' ' . $this->chef->last_name . '-' . $this->chef->flag,
                'country_code' => $this->chef->country_code,
                'phone' => $this->chef->phone_number,
                'national_id' => $this->chef->national_id,

                'employee_code' => $this->chef->employee_code,
                'branch' => $this->chef->relationLoaded('branch')
                    ? new BranchResource($this->chef->branch)
                    : null,
            ] : null,

            // single relation, safe to use whenLoaded
            'cuisineCategory' => $this->relationLoaded('cuisineCategory')
                ? new CuisineCategoryResource($this->cuisineCategory)
                : null,

            // dishes is a column, not a relation
            'dishes' => $this->when(true, function () use ($lang) {

                $nameColumn = $lang === 'ar' ? 'name_ar' : 'name_en';

                // Case: all dishes
                if (is_array($this->dishes) && count($this->dishes) === 1 && $this->dishes[0] == -1) {
                    return $lang === 'ar' ? ' جميع الأطباق' : 'All Dishes';
                }

                // Case: selected dishes
                // Case: selected dishes
                if (is_array($this->dishes) && count($this->dishes) > 0) {
                    return Dish::whereIn('id', $this->dishes)
                        ->get(['id', $nameColumn])
                        ->map(function ($dish, $key) use ($nameColumn) {
                            return [
                                'id' => $dish->id,
                                'name' => $dish->{$nameColumn},
                            ];
                        });
                }


                // Case: no dishes assigned
                return [];
            }),

        ];
    }
}
