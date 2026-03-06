<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        if($lang == 'en') {
            $discount_type = $this->discount_type == 'fixed' ? 'fixed' : 'percentage';
            $name = $this->name_en;
            $description=$this->description_en;
            $image=$this->image_en;
        }
        else{
            $discount_type = $this->discount_type == 'fixed' ? 'نسبة ثابتة' : 'نسبة مئوية';
            $name = $this->name_ar;
            $description=$this->description_ar;
            $image=$this->image_ar;
        }
        $is_active= $this->is_active == 0 ? false : true;
        $active= $this->is_active == 0 ? ($lang == 'en' ? 'inactive' : 'غير نشط') : ($lang == 'en' ? 'active' : 'نشط');

        return [
            'id' => $this->id,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch,
            'branch_selection' => $this->branch_id == "-1" ? ($lang == 'en' ? 'all' : 'الكل') : ($lang == 'en' ? 'specific' : 'محدد'),
            'discount_type' => $discount_type,
            'discount_value' => $this->discount_value,
            'name' => $name,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'description' => $description,
            'image' => $image,
            'image_ar' => $this->image_ar,
            'image_en' => $this->image_en,
            'is_active' => $is_active,
            'active' => $active,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'details' => OfferDetailResource::collection($this->details),
        ];
    }
}
