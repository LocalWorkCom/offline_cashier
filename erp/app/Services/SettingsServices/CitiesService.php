<?php

namespace App\Services\SettingsServices;

use App\Models\City;
use App\Models\Country; // Your Eloquent model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CitiesService
{
    protected $auth_id;
    private $lang;

    public function __construct()
    {
        // Initialize $auth_id with the current authenticated user's ID
        $this->auth_id = auth('admin')->id();
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index(Request $request, $country)
    {

        $lang = $request->header('lang', 'ar');
        $cities = City::with('country')->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        return $cities;
    }
    public function getCountryWithCities($countryId, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        return Country::with(['cities' => function ($query) {
            $query->whereNull('deleted_at')
                ->orderBy('created_at', 'desc');
        }])
            ->find($countryId);
    }
    public function show_all($country_id)
    {
        try {
            $cities = City::where('country_id', $country_id)->get();
            return ResponseWithSuccessData($this->lang, $cities, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('cities')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('cities')->whereNull('deleted_at')
            ],
            'country_id' => 'required|exists:countries,id',
        ]);
        if ($validator->fails()) {
            // Return array instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $city = new city();
        $city->name_ar = $request->name_ar;
        $city->name_en = $request->name_en;
        $city->country_id = $request->country_id;
        $city->created_by = authActionSave()['by'];
        $city->created_by_type = authActionSave()['type'];

        $city->save();

        return $city;
    }
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        // Validate the input
        $validator = Validator::make($request->all(), [
            "name_ar" => "required",
            "name_en" => "required",
            'country_id' => 'required',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the category by ID, or throw an exception if not found
        $city = City::find($id);
        if (!$city) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $city->name_ar = $request->name_ar;
        $city->name_en = $request->name_en;
        $city->country_id = $request->country_id;
        $city->updated_by = $this->auth_id;
        $city->save();
        return RespondWithSuccessRequest($lang, 1);
    }
    public function destroy($id)
    {

        $lang = app()->getLocale();

        // Find the country
        $city = City::find($id);
        if (!$city) {
            return RespondWithBadRequestData($lang, 8);
        }
        // $product_exists = Product::where('currency_code',$country->currency_code)->exists();
        // if ($product_exists) {

        //     return RespondWithBadRequest($lang, 6);
        // }
        // Delete the country
        $city->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
}
