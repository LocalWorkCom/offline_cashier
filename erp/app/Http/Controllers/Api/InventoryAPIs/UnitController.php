<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Models\Unit;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use App\Models\SupplyOrderItem;
use Illuminate\Validation\Rule;
use App\Models\ProductBrandUnit;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequestItem;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\PurchaseInvoicesDetails;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Inventory\UnitResource;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        // $data = Unit::query();
        $data = Unit::with(['createdBy', 'modifiedBy'])
            ->orderByDesc('updated_at');
        // Apply filters
        if ($request->has('active')) {
            $active = $request->boolean('active');
            $data->where('active', $active);
        }

        if ($request->filled('from')) {
            $data->where('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $data->where('created_at', '<=', $request->to);
        }

        // Apply date filter
        $data = applyDateFilter($data, $request->date_filter);

        $fields = ['name', 'description'];
        $visibles = ['name_en', 'name_ar', 'description_en', 'description_ar'];
        $response = paginateOrGetAll($data, $request, $fields, $visibles);

        $response = paginateOrGetAll($data, $request, $fields, $visibles);

        // 🧾 Transform the response
        if (isset($response['data'])) {
            $response['data'] = new UnitResource(collect($response['data']), $lang);
        } else {
            $response = new UnitResource($response->get(), $lang);
        }
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('units', 'name_ar')->whereNull('deleted_at'),
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('units', 'name_en')->whereNull('deleted_at'),
            ],
            'description_ar' => [
                'nullable',
                Rule::unique('units', 'description_ar')->whereNull('deleted_at'),
            ],
            'description_en' => [
                'nullable',
                Rule::unique('units', 'description_en')->whereNull('deleted_at'),
            ],
            'active' => ['nullable', Rule::in([true, false, 'true', 'false', 1, 0, '1', '0'])],
            'abbreviation' => 'required|string|max:20|unique:units,abbreviation',

        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $craeted = authActionSave();
        $created_by = $craeted['by'];
        $created_type = $craeted['type'];

        $unit = new Unit();
        $unit->name_ar = $request->name_ar;
        $unit->name_en = $request->name_en;
        $unit->description_ar = $request->description_ar;
        $unit->description_en = $request->description_en;
        $unit->abbreviation = $request->abbreviation;

        $unit->active = $request->has('active') ? $request->boolean('active') : true;
        $unit->created_by = $created_by;
        $unit->created_type = $created_type;
        $unit->save();


        $unit->load(['createdBy', 'modifiedBy']);

        $unitCollection = collect([$unit]);
        $responseData = new UnitResource($unitCollection, $lang);

        $formattedData = $responseData->toArray($request)[0] ?? [];

        return ResponseWithSuccessData($lang, $formattedData, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                Rule::unique('units', 'name_ar')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'name_en' => [
                'required',
                Rule::unique('units', 'name_en')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description_ar' => [
                'nullable',
                Rule::unique('units', 'description_ar')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'description_en' => [
                'nullable',
                Rule::unique('units', 'description_en')
                    ->ignore($id)
                    ->whereNull('deleted_at'),
            ],
            'active' => 'boolean',
            'abbreviation' => 'required|string|max:20|unique:units,abbreviation,' . $id,

        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $unit = Unit::find($id);
        if (!$unit) {
            return respondErrorData(($lang == 'en' ? ['Not existing any more'] : ['غير موجود']), 400, $lang == 'en' ? ['This item is not existing any more'] : ['هذا العنصر غير موجود']);
        }

        $craeted = authActionSave();
        $modify_by = $craeted['by'];
        $modified_type = $craeted['type'];

        $unit->name_ar = $request->name_ar;
        $unit->name_en = $request->name_en;
        $unit->description_ar = $request->description_ar;
        $unit->description_en = $request->description_en;
        $unit->abbreviation = $request->abbreviation;

        $unit->active = $request->has('active') ? $request->boolean('active') : $unit->active;
        $unit->modify_by = $modify_by;
        $unit->modified_type = $modified_type;
        $unit->save();

        $unit->refresh();
        $unit->load(['createdBy', 'modifiedBy']);

        $unitCollection = collect([$unit]);
        $responseData = new UnitResource($unitCollection, $lang);

        // Since it's a single unit, get the first item from the collection
        $formattedData = $responseData->toArray($request)[0] ?? [];

        return ResponseWithSuccessData($lang, $formattedData, 1);
    }

    public function show($id, Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $unit = Unit::find($id);

            if (!$unit) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Unit not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }


            // Use the same Resource as index method
            $unitCollection = collect([$unit]);
            $responseData = new UnitResource($unitCollection, $lang);

            // Since it's a single unit, get the first item from the collection
            $formattedData = $responseData->toArray($request)[0] ?? [];

            return ResponseWithSuccessData($lang, $formattedData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching Unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $unit = Unit::find($id);
            App::setLocale($lang);

            if (!$unit) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // 🔹 Check if this unit is used as first_unit_id in product_brand_units
            $hasFirstUnitProducts = ProductBrandUnit::where('first_unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used as second_unit_id in product_brand_units
            $hasSecondUnitProducts = ProductBrandUnit::where('second_unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used as default_unit_id in product_brands
            $hasDefaultUnitProducts = ProductBrand::where('default_unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used as base_unit_id in product_brands
            $hasBaseUnitProducts = ProductBrand::where('base_unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used in PurchaseInvoicesDetails
            $hasPurchaseInvoiceDetails = PurchaseInvoicesDetails::where('unit_id', $id)
                ->exists();

            // 🔹 Check if this unit is used in PurchaseOrderItem (both ordered_unit and received_unit)
            $hasPurchaseOrderItems = PurchaseOrderItem::where(function ($query) use ($id) {
                $query->where('ordered_unit', $id)
                    ->orWhere('received_unit', $id);
            })
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used in PurchaseRequestItem
            $hasPurchaseRequestItems = PurchaseRequestItem::where('unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            // 🔹 Check if this unit is used in SupplyOrderItem
            $hasSupplyOrderItems = SupplyOrderItem::where('unit_id', $id)
                ->whereNull('deleted_at')
                ->exists();

            $hasRelatedRecords = $hasFirstUnitProducts || $hasSecondUnitProducts ||
                $hasDefaultUnitProducts || $hasBaseUnitProducts ||
                $hasPurchaseInvoiceDetails || $hasPurchaseOrderItems ||
                $hasPurchaseRequestItems || $hasSupplyOrderItems;

            if ($hasRelatedRecords) {
                // 🔹 Update active to 0 instead of deleting
                $unit->update(['active' => 0]);

                return respondError(
                    $lang == 'en'
                        ? 'This unit is now inactive. Existing products remain linked, but it cannot be used for new assignments.'
                        : 'هذه الوحدة غير نشطة الآن. المنتجات الحالية تظل مرتبطة، لكن لا يمكن استخدامها في التعيينات الجديدة.',
                    400
                );
            }

            $craeted = authActionSave();
            $deleted_by = $craeted['by'];
            $deleted_type = $craeted['type'];
            $unit->deleted_by = $deleted_by;
            $unit->deleted_type = $deleted_type;
            $unit->save();
            $unit->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while deleting Unit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
