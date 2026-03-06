<?php

namespace App\Services\SettingsServices;


use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class AreaService
{
    protected $auth_id;
    protected $lang;

    public function __construct()
    {
        // Initialize $auth_id with the current authenticated user's ID
        $this->auth_id = auth('admin')->id();
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index(Request $request, $city)
    {
        $lang = $request->header('lang', 'ar');
        $areas = Area::with('city')->whereNull('deleted_at')
            ->orderBy( 'created_at', 'desc');

        return $areas;
    }

    public function show_all($city_id)
    {
        try {
            $cities = Area::where('city_id', $city_id)->get();
            return ResponseWithSuccessData($this->lang, $cities, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('areas')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('areas')->whereNull('deleted_at')
            ],
            'city_id' => 'required|exists:cities,id',
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
        $region = new Area();
        $region->name_ar = $request->name_ar;
        $region->name_en = $request->name_en;
        $region->city_id = $request->city_id;

        $region->created_by = authActionSave()['by'];
        $region->created_by_type = authActionSave()['type'];

        $region->save();
        return $region;
    }
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        // Validate the input
        $validator = Validator::make($request->all(), [
            "name_ar" => "required",
            "name_en" => "required",
            'city_id' => 'required',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        // Retrieve the category by ID, or throw an exception if not found
        $region = Area::find($id);

        if (!$region) {
            return  RespondWithBadRequestData($lang, 8);
        }
        $region->name_ar = $request->name_ar;
        $region->name_en = $request->name_en;
        $region->city_id = $request->city_id;
        $region->updated_by = $this->auth_id;
        $region->save();
        return RespondWithSuccessRequest($lang, 1);
    }
    public function destroy( $id)
    {
        $lang = app()->getLocale();

        // Find the country
        $region = Area::find($id);
        if (!$region) {
            return RespondWithBadRequestData($lang, 8);
        }
        // $product_exists = Product::where('currency_code',$country->currency_code)->exists();
        // if ($product_exists) {

        //     return RespondWithBadRequest($lang, 6);
        // }
        // Delete the country
        $region->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

}
