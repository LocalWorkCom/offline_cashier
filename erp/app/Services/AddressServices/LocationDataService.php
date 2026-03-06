<?php


namespace App\Services\AddressServices;

use App\Models\Area;
use App\Models\City;
use App\Models\Hotel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LocationDataService
{

    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getCitiesByCountry($countryId, $language)
    {
        return City::where('country_id', $countryId)
            ->get()
            ->map(function ($city) use ($language) {
                return [
                    'id' => $city->id,
                    'name' => $language === 'ar' ? $city->name_ar : $city->name_en
                ];
            });
    }

    public function getAreasByCity($cityId)
    {
        return Area::where('city_id', $cityId)->get();
    }
       public function all(string $lang = 'ar')
    {
        app()->setLocale($lang);

        return Hotel::query()
            ->get()
            ->map(fn ($hotel) => [
                'id'         => $hotel->id,
                'name'       => $hotel->name,
                'name_ar'       => $hotel->name_ar,
                'name_en'       => $hotel->name_en,
                'branch_ar'       => $hotel->branch_name_ar,
                'branch_en'       => $hotel->branch_name_en,
                'branch_id'       => $hotel->branch_name_en,
                'address'    => $hotel->address,
                'address_ar'    => $hotel->address_ar,
                'address_en'    => $hotel->address_en,
                'building_number'    => $hotel->building_number,
                'phone_number'    => $hotel->phone_number,
                'shiping_cost'    => $hotel->shiping_cost,
                'note'    => $hotel->note,
                'country_id' => $hotel->country_id,
                'city_id'    => $hotel->city_id,
                'area_id'    => $hotel->area_id,
                'status'    => $hotel->status == 1 ? ($lang == 'en' ? 'Active' : 'نشط') : ($lang == 'en' ? 'Not Active' : 'غير نشط'),
            ]);
    }
}
