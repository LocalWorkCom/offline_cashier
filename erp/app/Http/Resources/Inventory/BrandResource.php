<?php

namespace App\Http\Resources\Inventory;

use Carbon\Carbon;
use App\Models\Brand;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BrandResource extends JsonResource
{
    public function toArray($request)
    {
        $lang = $request->header('lang', 'ar');

        return [
            'id' => $this->id,
            'name' => $lang === 'en' ? $this->name_en : $this->name_ar,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'description' => $lang === 'en' ? $this->description_en : $this->description_ar,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'is_active' => (int) $this->is_active,
            'logo_path' => $this->logo_path,

            'created_at' => formatDateTime($this->created_at, $lang, 'date'),
            'updated_at' => formatDateTime($this->updated_at, $lang, 'date'),

            'created_by' => $this->createdBy
                ? $this->createdBy->first_name . ' ' . $this->createdBy->last_name
                : null,

            'modify_by' => $this->modifiedBy
                ? $this->modifiedBy->first_name . ' ' . $this->modifiedBy->last_name
                : null,
        ];
    }
}
