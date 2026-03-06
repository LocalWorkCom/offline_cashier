<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DirectSupplyIssueTypeResource  extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');

       return [
            'id' => $this->id,
            'name_ar' => $this->title_ar,
            'name_en' => $this->title_en,
            'name' => $this->name,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'status' => (bool) $this->status,
        ];
    }
}
