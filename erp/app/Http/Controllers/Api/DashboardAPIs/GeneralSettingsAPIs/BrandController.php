<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $withTrashed = $request->query('withTrashed', false);

            // $brands = $withTrashed ? Brand::withTrashed() : Brand::query();
            $brands = Brand::query();

            $brands = paginateOrGetAll($brands, $request);
            
            if(isset($brands['data'])) {
                $brands['data']->map(function ($brand) use ($lang) {
                    $brand->is_active = $brand->is_active == 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');
                });
            }

            return ResponseWithSuccessDataPaginated($lang, $brands, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching brands: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            // $brand = Brand::withTrashed()->findOrFail($id);
            // $brand = Brand::findOrFail($id);
            $brand = Brand::where('id', $id)->first();
            if(!$brand) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
            }
            $brand->is_active = $brand->is_active == 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');

            return ResponseWithSuccessData($lang, $brand, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            app()->setLocale($lang);
            // $request->merge(['is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)]);
            if ($request->has('is_active')) {
                $request->merge([
                    'is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)
                ]);
            }

            $validator = Validator::make($request->all(), [
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'logo' => 'required|image|mimes:jpg,png,jpeg|max:5000',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $brandData = [
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'is_active' => $request->is_active,
                'created_by' => auth()->id(),
            ];

            if ($request->hasFile('logo')) {
                $file = $request->file('logo');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/brands'), $filename); 
                $brandData['logo_path'] = url('images/brands/' . $filename);
            }

            $brand = Brand::create($brandData);

            return ResponseWithSuccessData($lang, $brand, 1);
        } catch (\Exception $e) {
            Log::error('Error creating brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $request->merge(['is_active' => filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN)]);

            $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'logo' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ]);

            // $brand = Brand::findOrFail($id);
            $brand = Brand::where('id', $id)->first();
            if(!$brand) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
            }

            $brandData = [
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'is_active' => $request->is_active,
                'modified_by' => auth()->id(),
            ];

            if ($request->hasFile('logo')) {
                if ($brand->logo_path && Storage::exists($brand->logo_path)) {
                    Storage::delete($brand->logo_path);
                }
                $file = $request->file('logo');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('images/brands'), $filename); 
                $brandData['logo_path'] = url('images/brands/' . $filename);
            }

            $brand->update($brandData);

            return ResponseWithSuccessData($lang, $brand, 1);
        } catch (\Exception $e) {
            Log::error('Error updating brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $brand = Brand::where('id', $id)->first();
            if(!$brand) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
            }
            $brand->update(['deleted_by' => auth()->id()]);
            $brand->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
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
