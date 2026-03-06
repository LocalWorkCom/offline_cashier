<?php

namespace App\Http\Resources\Inventory;

use Carbon\Carbon;
use App\Models\Category;
use App\Models\ProductBrand;
use App\Models\ProductTransaction;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryResource2 extends ResourceCollection
{  protected $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    public function toArray($request)
    {
        return [
            'id'         => $this->id,
            'name'       => $this->lang == 'ar' ? $this->name_ar : $this->name_en,
            'active'     => (bool) $this->active,
            'parent_id'  => $this->parent_id,

            // Nested children recursively
            'children'   => CategoryResource2::collection($this->whenLoaded('children'))
                                ->additional(['lang' => $this->lang]),

            'children_count' => $this->whenLoaded('children', fn () => $this->children->count()),

            'custom_fields_count' => $this->custom_fields_count ?? 0,
            'products_count'      => $this->products_count ?? 0,
        ];
    }
}