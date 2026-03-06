<?php

namespace App\Services\StoreServices;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class BrandService
{

    public function index(Request $request, $checkToken)
    {

        $lang = app()->getLocale();
        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }
        $brands = Brand::all();


        if (!$checkToken) {
            $brands = $brands->makeVisible(['name_en', 'name_ar', 'logo_path', 'description_ar', 'description_en']);
        }

        return ResponseWithSuccessData($lang, $brands, 1);
    }
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'required|boolean',
            'logo_path' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
        ]);

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }
        if (CheckExistColumnValue('brands', 'name_ar', $request->name_ar) || CheckExistColumnValue('brands', 'name_en', $request->name_en)) {
            return RespondWithBadRequest($lang, 9);
        }

        // $GetLastID = GetLastID('brands');
        // dd($GetLastID);

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $description_ar = $request->description_ar;
        $description_en = $request->description_en;
        $is_active = $request->is_active;

        $logo_path = $request->file('logo_path');  // Handle file upload if necessary

        $brand = new Brand();
        $brand->name_ar = $name_ar;
        $brand->name_en =  $name_en;
        $brand->description_ar = $description_ar;
        $brand->description_en =  $description_en;
        $brand->is_active = $is_active;
        $brand->created_by = Auth::guard('admin')->user()->id;

        $brand->save();
        if ($request->hasFile('logo_path')) {

            UploadFile('images/brands', 'logo_path', $brand, $logo_path);
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
        if($request->hasFile('logo_path')){
            $validator = Validator::make($request->all(), [
               'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'logo_path' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ]);
        }else{
            $validator = Validator::make($request->all(), [
               'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
            ]);
        }

        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Retrieve the category by ID, or throw an exception if not found
        $brand = Brand::find($id);
        if (!$brand) {
            return  RespondWithBadRequestData($lang, 8);
        }
        // dd($category, $request);
        if ($brand->name_ar == $request->name_ar
            && $brand->name_en == $request->name_en
            &&  $brand->description_ar == $request->description_ar
            &&  $brand->description_en == $request->description_en
            && $brand->is_active == $request->is_active
            && $brand->logo_path == $request->file('logo_path') ) {
            return  RespondWithBadRequestData($lang,10);
        }
        $modified_by  = Auth::guard('admin')->user()->id;

        // Assign the updated values to the brand model
        $brand->name_ar = $request->name_ar;
        $brand->name_en = $request->name_en;
        $brand->description_ar = $request->description_ar;
        $brand->description_en = $request->description_en;
        $brand->is_active = $request->is_active;
        $brand->modified_by  = $modified_by ;
        // Handle file upload for the image if provided
        if ($request->hasFile('logo_path')) {
            $logo_path = $request->file('logo_path');
            DeleteFile('images/brands', $brand->logo_path);
            UploadFile('images/brands', 'logo_path', $brand, $logo_path);
        }

        // Update the brand in the database
        $brand->save();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }

    public function destroy(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();

        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }
        // Find the brand by ID, or throw a 404 if not found
        $brand = Brand::find($id);
        if (!$brand) {
            return  RespondWithBadRequestData($lang, 8);
        }

        // Check if there are any products associated with this brand
        if ($brand->productsBrand()->count() > 0) {
            return CustomRespondWithBadRequest(__('brand.The brand have relation'));
        }
        // Handle deletion of associated logo_path if it exists
        if ($brand->logo_path) {
            $imagePath = public_path('images/brands/' . $brand->logo_path);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        // Delete the brand
        $brand->delete();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $brand = Brand::withTrashed()->findOrFail($id);
            $brand->restore();

            return ResponseWithSuccessData($lang, $brand, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
