<?php

namespace App\Http\Controllers\Api\DeliveryAPIs;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class DeliveryAddressesController extends Controller
{
  public function getCitiesAndAreas(Request $request)
{
    $lang = $request->header('lang', 'ar');
    App::setLocale($lang);

    $user = auth('employee')->user() ?? auth('api')->user();
    $countryId = null;

    if ($user && $user->flag === 'driver') {
        // Authenticated driver — use their country_id
        $countryId = $user->country_id;
    } else {
        // Not a driver or not authenticated — require input
        $countryId = $request->input('country_id');
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

    return ResponseWithSuccessData($lang, $cities->isEmpty() ? null : $cities, 1);
}


}
