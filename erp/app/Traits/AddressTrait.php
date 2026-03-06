<?php

namespace App\Traits;

use App\Models\City;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait AddressTrait
{
    protected $lang;
    public function getCitiesAndAreas(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user() ?? auth('api')->user();

        if (!$user || !in_array($user->flag, ['driver', 'client', 'customer_service'])) {
            return RespondWithBadRequest($lang, 4);
        }

        App::setLocale($lang);
        $countryId = in_array($user->flag, ['driver', 'customer_service'])
            ? $request->input('country_id')
            : $user->country_id;

        if (!$countryId) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                [
                    'errorData' => [
                        'country_id' => __('validation.countryIdRequired'),
                    ]
                ]
            );
        }


        $cities = City::with('areas')
            ->where('country_id', $countryId)
            ->get()
            ->map(function ($city) {
                $areas = optional($city->area)->map(function ($area) {
                    return [
                        'id' => $area->id,
                        'name' => $area->name,
                    ];
                }) ?? [];

                return [
                    'id' => $city->id,
                    'name' => $city->name,
                    'area' => $areas,
                ];
            });

        if ($cities->isEmpty()) {
            $cities = null;
        }

        return ResponseWithSuccessData($lang, $cities, 1);
    }
}
