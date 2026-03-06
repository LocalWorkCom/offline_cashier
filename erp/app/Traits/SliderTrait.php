<?php

namespace App\Traits;

use App\Models\Slider;
use Illuminate\Http\Request;

trait SliderTrait
{
    protected $lang;
    public function getSlider(Request $request){
        $this->lang = $request->header('lang', 'ar');
        $sliders = Slider::with(['dish', 'offer'])->get();

        foreach ($sliders as $slider) {
            if ($slider->dish) {
                $slider->dish->makeHidden(['name_site', 'description_site']);
            }

            $slider->makeHidden(['name_ar', 'description_ar', 'name_en', 'description_en']);

            if ($this->lang === 'en') {
                $slider['name'] = $slider->name_en ?? '';
                $slider['description'] = $slider->description_en ?? '';
            } else {
                $slider['name'] = $slider->name_ar ?? '';
                $slider['description'] = $slider->description_ar ?? '';
            }

            if ($slider->offer) {
                $slider->offer->makeHidden(['name_ar', 'description_ar', 'name_en', 'description_en']);

                if ($this->lang === 'en') {
                    $slider->offer['name'] = $slider->offer->name_en ?? '';
                    $slider->offer['description'] = $slider->offer->description_en ?? '';
                } else {
                    $slider->offer['name'] = $slider->offer->name_ar ?? '';
                    $slider->offer['description'] = $slider->offer->description_ar ?? '';
                }
            }

            if ($slider->discount) {
//                dd($slider->discount->dish->name_en);
                $slider->discount->makeHidden(['discount','dish']);
                if ($this->lang === 'en') {
                    $slider->discount['dish_name'] = $slider->discount->dish->name_en ?? '';
                    $slider->discount['value'] = $slider->discount->discount->value ?? ''; // Assuming `value` holds the discount amount
                    $slider->discount['type'] = $slider->discount->discount->type ?? '';  // Assuming `type` holds the discount type
                } else {
                    $slider->discount['dish_name'] = $slider->discount->dish->name_ar ?? '';
                    $slider->discount['value'] = $slider->discount->discount->value ?? '';
                    $slider->discount['type'] = $slider->discount->discount->type ?? '';
                }
            }
        }

        if (!$sliders) {
            return RespondWithBadRequestData($this->lang, 2);
        }

        return ResponseWithSuccessData($this->lang, $sliders, 1);
    }
}
