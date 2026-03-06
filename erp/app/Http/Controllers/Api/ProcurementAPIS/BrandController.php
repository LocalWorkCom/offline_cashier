<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;


use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Inventory\BrandResource;
use App\Models\ProductBrand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BrandController extends Controller
{
    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
        return $employee;
    }
    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $brands = Brand::with(['createdBy', 'modifiedBy'])
                ->orderByDesc('updated_at')->orderByDesc('created_at');

            // 🧩 Apply filters
            $brands->when($request->brand_id, function ($query) use ($request) {
                $query->where('id', $request->brand_id);
            });

            if ($request->filled('name')) {
                $searchTerm = $request->name;
                $brands->where(function ($q) use ($searchTerm) {
                    $q->where('name_ar', 'LIKE', "%{$searchTerm}%")
                        ->orWhere('name_en', 'LIKE', "%{$searchTerm}%");
                });
            }
            if ($request->filled('from')) {
                $brands->where('created_at', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $brands->where('created_at', '<=', $request->to);
            }

            if ($request->has('is_active')) {
                $active = $request->boolean('is_active');
                $brands->where('is_active', $active);
            }

            // 📅 Date filter (today, yesterday, before_yesterday, this_week, this_month)
            $brands = applyDateFilter($brands, $request->date_filter);

            // 📄 Pagination or all results
            $brands = paginateOrGetAll($brands, $request, null, null);

            // 🧾 Transform the response
            if (isset($brands['data'])) {
                $brands['data'] = new BrandResource(collect($brands['data']), $lang);
            } else {
                $brands = new BrandResource($brands->get(), $lang);
            }

            return ResponseWithSuccessDataPaginated($lang, $brands, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve brands',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $brandExists = Brand::where('id', $id)->exists();

            if (!$brandExists) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
            }

            $brand = Brand::with(['createdBy', 'modifiedBy'])
                ->findOrFail($id);

            // Use the same Resource as index method
            $brandCollection = collect([$brand]);
            $responseData = new BrandResource($brandCollection, $lang);

            // Since it's a single Brand, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve Brand',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $lang=$request->header('lang', 'ar');
            app()->setLocale($lang);
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'name_ar')->whereNull('deleted_at'),
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'name_en')->whereNull('deleted_at'),
                ],

                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'logo_path' => 'nullable|image|mimes:jpg,png,jpeg|max:5000',
            ]);

            if ($validator->fails()) {
                return respondError(__('validation.error'), 400, $validator->errors());
            }

            $user = auth('employee')->user();

            $brand = new Brand();
            $brand->name_ar = $request->name_ar;
            $brand->name_en = $request->name_en;
            $brand->description_ar = $request->description_ar;
            $brand->description_en = $request->description_en;
            $brand->is_active = $request->boolean('is_active');
            $brand->created_by = $user->id;

            if ($request->hasFile('logo_path')) {
                $logoPath = $request->file('logo_path');
                UploadFile('images/brands', 'logo_path', $brand, $logoPath);
            }
            $brand->save();

            $brand->load(['createdBy', 'modifiedBy']);

            $brandCollection = collect([$brand]);
            $responseData = new BrandResource($brandCollection, $lang);

            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating brand and custom fields',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            app()->setLocale($lang);
            $brand = Brand::where('id', $id)->first();
            if (!$brand) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
            }

            // ✅ Validate request
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'name_ar')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],
                'name_en' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('brands', 'name_en')
                        ->ignore($id)
                        ->whereNull('deleted_at'),
                ],

                'description_ar' => 'nullable|string',
                'description_en' => 'nullable|string',
                'is_active' => 'required|boolean',
                'logo_path' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return respondError(__('validation.error'), 400, $validator->errors());
            }

            $user = auth('employee')->user();

            // ✅ Update main brand data with boolean is_active
            $brand->update([
                'name_ar' => $request->name_ar,
                'name_en' => $request->name_en,
                'description_ar' => $request->description_ar,
                'description_en' => $request->description_en,
                'is_active' => $request->boolean('is_active'), // Use boolean() method
                'modify_by' => $user->id,
            ]);
            if ($request->hasFile('logo_path')) {
                $logoPath = $request->file('logo_path');
                UploadFile('images/brands', 'logo_path', $brand, $logoPath);
            }

            $brand->refresh();
            $brand->load(['createdBy', 'modifiedBy']);

            $brandCollection = collect([$brand]);
            $responseData = new BrandResource($brandCollection, $lang);

            // Since it's a single brand, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update brand',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id = null)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        //  Collect IDs from route OR request body
        $ids = collect(
            $id ? [$id] : $request->input('ids', [])
        )->unique()->values();

        // Validate IDs
        if ($ids->isEmpty()) {
            return respondError(
                $lang === 'ar' ? 'لم يتم إرسال أي معرف.' : 'No brand IDs provided.',
                422
            );
        }

        $brands = Brand::whereIn('id', $ids)->get();

        if ($brands->count() !== $ids->count()) {
            return respondError(
                $lang === 'ar' ? 'بعض البراندات غير موجودة.' : 'Some brands were not found.',
                404
            );
        }

        DB::beginTransaction();

        try {
            foreach ($brands as $brand) {
                $hasProducts = ProductBrand::where('brand_id',  $brand->id)
                ->whereNull('deleted_at')
                ->exists();

                if ($hasProducts) {
                    // Update is_active to 0 instead of deleting
                    $brand->update(['is_active' => 0]);

                    return respondError(
                        $lang == 'en'
                            ? 'This brand is now inactive. Existing products remain linked, but it cannot be used for new assignments.'
                            : 'هذا البراند غير نشط الآن. المنتجات الحالية تظل مرتبطة، لكن لا يمكن استخدامها في التعيينات الجديدة.',
                        400
                    );
                } else {
                        $brand->delete();
                }
            }

            DB::commit();

            return ResponseWithSuccessData($lang, [
                'deleted_ids' => $ids
            ], 1);

        } catch (\Throwable $e) {
            DB::rollBack();

            return respondError(
                $lang === 'ar'
                    ? 'حدث خطأ أثناء حذف البراندات.'
                    : 'Failed to delete brands.',
                500,
                $e->getMessage()
            );
        }
    }

//    public function destroy(Request $request, $id)
//    {
//        $lang = $request->header('lang', 'ar');
//
//        try {
//            App::setLocale($lang);
//
//            $brand = Brand::find($id);
//
//            if (!$brand) {
//                return respondError($lang === 'ar' ? 'لم يتم العثور علي البراند.' : 'brand id not found', 404);
//            }
//
//
//            // 🔹 Check if this brand has any products
//            $hasProducts = ProductBrand::where('brand_id', $id)
//                ->whereNull('deleted_at') // Only check active products if soft delete is used
//                ->exists();
//
//            if ($hasProducts) {
//                // 🔹 Update is_active to 0 instead of deleting
//                $brand->update(['is_active' => 0]);
//
//                return respondError(
//                    $lang == 'en'
//                        ? 'This brand is now inactive. Existing products remain linked, but it cannot be used for new assignments.'
//                        : 'هذا البراند غير نشط الآن. المنتجات الحالية تظل مرتبطة، لكن لا يمكن استخدامها في التعيينات الجديدة.',
//                    400
//                );
//            }
//
//            // 🔹 If safe, delete brand
//            $brand->delete();
//
//            return RespondWithSuccessRequest($lang, 1);
//        } catch (\Exception $e) {
//            return RespondWithBadRequestData($lang, 2);
//        }
//    }
}
