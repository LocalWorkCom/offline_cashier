<?php

namespace App\Services\SettingsServices;

use App\Models\Country;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class CountryService
{
    public function index(Request $request, $checkToken)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $counties = Country::whereNull('deleted_at')->where('active_show', 1)
            ->orderBy('order', 'Asc');

        return $counties;
    }

      public function producrIndex(Request $request, $checkToken)
    {

        $lang = $request->header('lang', 'ar');
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

         $counties = Country::whereNull('deleted_at')->where('active_show', 1)
            ->orderBy('order', 'Asc')
            ->get();

        if (!$checkToken) {
            $counties = $counties->makeVisible(['name_en', 'name_ar', 'image', 'description_ar', 'description_en']);
        }

        return ResponseWithSuccessData($lang, $counties, 1);
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        App::setLocale($lang);

        $messages = [
            "name_ar.required" => __('validation.EnterArabicName'),
            "name_en.required" => __('validation.EnterEnglishName'),
            "currency_ar.required" => __('validation.ArabicCurrency'),
            "currency_en.required" => __('validation.EnglishCurrency'),
            "currency_code.required" => __('validation.CurrencyCode'),
            "CurrencySymbole.required" => __('validation.CurrencySymbole'),
            "code.required" => __('validation.EnterCode'),
            "phone_code.required" => __('validation.phonecode'),
            "length.required" => __('validation.length'),
            "flag.required" => __('validation.flag'),
            "flag.image" => __('validation.image'),
        ];

        $validator = Validator::make($request->all(), [
            "name_ar" => "required",
            "name_en" => "required",
            'currency_ar' => 'required',
            'currency_en' => 'required',
            'currency_code' => 'required',
            'CurrencySymbole' => 'required',
            'code' => 'required',
            'phone_code' => 'required',
            'order' => 'required',
            'active_show' => 'required|boolean',
            'length' => 'required',
            'flag' => 'required|image|mimes:jpeg,png,jpg,gif',
        ], $messages);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        Country::where('order', $request->order)->update(['order' => 0]);

        $counties = new Country();
        $counties->name_ar = $request->name_ar;
        $counties->name_en = $request->name_en;
        $counties->currency_ar = $request->currency_ar;
        $counties->currency_en = $request->currency_en;
        $counties->code = $request->code;
        $counties->currency_code = $request->currency_code;
        $counties->currency_symbol = $request->CurrencySymbole;
        $counties->phone_code = $request->phone_code;
        $counties->length = $request->length;
        $counties->order = $request->order;
        $counties->active_show = $request->active_show;
        $counties->created_by = auth('admin')->id() ?? 1;
        $counties->save();
        if ($request->hasFile('flag')) {
            $image = $request->file('flag');
            UploadFile('images/countries', 'flag', $counties, $image);
        }
        return RespondWithSuccessRequest($lang, 1);
    }
    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        // Validate the input
        $validator = Validator::make($request->all(), [
            "name_ar" => "required",
            "name_en" => "required",
            'currency_ar' => 'required',
            'currency_en' => 'required',
            'currency_code' => 'required',
            'CurrencySymbole' => 'required',
            'code' => 'required',
            'phone_code' => 'required',
            'length' => 'required',
            'order' => 'nullable',
            'active_show' => 'required|boolean',
            'flag' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the category by ID, or throw an exception if not found
        $country = Country::find($id);
        if (!$country) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // $modify_by = auth()->id;
        Country::where('order', $request->order)->update(['order' => 0]);
        $country->name_ar = $request->name_ar;
        $country->name_en = $request->name_en;
        $country->currency_ar = $request->currency_ar;
        $country->currency_en = $request->currency_en;
        $country->code = $request->code;
        $country->currency_code = $request->currency_code;
        $country->currency_symbol = $request->CurrencySymbole;
        $country->phone_code = $request->phone_code;
        $country->length = $request->length;
        $country->order = $request->order;
        $country->active_show = $request->active_show;

        $country->modified_by = authActionSave()['by'];
        // Update the category in the database
        $country->save();
        if ($request->hasFile('flag')) {
            $image = $request->file('flag');
            UploadFile('images/countries', 'flag', $country, $image);
        }
        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
    public function destroy(Request $request, $id, $checkToken)
    {

        $lang = app()->getLocale();

        // Find the country
        $country = Country::find($id);
        if (!$country) {
            return RespondWithBadRequestData($lang, 8);
        }

        // Check if the country has related users
        if ($country->users()->count() > 0) {

            return RespondWithBadRequest($lang, 6);
        }
        // $product_exists = Product::where('currency_code', $country->currency_code)->exists();
        // if ($product_exists) {

        //     return RespondWithBadRequest($lang, 6);
        // }
        // Delete the country
        $country->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function show($id)
    {
        $lang = session()->get('locale');
        try {
            $branch = Country::findOrFail($id);
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function showList($id)
    {
        $lang = session()->get('locale');
        try {
            $branch = Country::where('active_show', 1)->get();
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
