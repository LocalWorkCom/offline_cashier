<?php

namespace App\Http\Resources;

use App\Models\BranchMenu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CuisineCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
                            "name" => $this->dish_category->name.'-'.$this->cuisine->name,

            // "dish_category" => [
            //     "id" => $this->dish_category_id,
            //     "name" => $this->dish_category->name,
            // ],
            // "cuisine" => [
            //     "id" => $this->cuisine_id,
            //     "name" => $this->cuisine->name,
            // ],
        ];
    }
}
