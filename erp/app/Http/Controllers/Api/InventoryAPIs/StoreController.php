<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Api\InventoryAPIs\WarehouseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StoreController extends Controller
{
    protected $zones;

    public function __construct(WarehouseController $zones)
    {
        $this->zones = $zones;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $withTrashed = $request->query('withTrashed', false);

            $stores = $withTrashed
                ? Store::onlyTrashed()->with(['branch', 'creator', 'deleter', 'categories'])
                : Store::with(['branch', 'creator', 'deleter', 'categories', 'zones.rackShelves']);
            if ($request->boolean('check_product')) {
                $stores->whereHas('productBrands');
            }
            $stores = paginateOrGetAll($stores, $request, null, null);

            return ResponseWithSuccessDataPaginated($lang, $stores, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $store = Store::withTrashed()->with(['branch', 'creator', 'deleter', 'categories', 'city', 'area', 'zones.rackShelves', 'productStores.productBrand'])->findOrFail($id);
            return ResponseWithSuccessData($lang, $store, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'branch_id'          => 'required|exists:branches,id',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'street' => 'required',
            'building' => 'required',
            'phone' => 'required',
            'name_ar'            => 'required|string|max:255',
            'name_en'            => 'required|string|max:255',
            'code' => 'required|string|unique:stores,code',
            'min_storage' => 'nullable|integer',
            'max_storage' => 'nullable|integer|gt:min_storage',
            'description_ar'     => 'nullable|string',
            'description_en'     => 'nullable|string',
            'is_kitchen'         => 'required|boolean',
            'zones' => 'nullable|array|min:1',
            'zones.*.name_ar' => 'required_with:zones|string|max:255|distinct',
            'zones.*.name_en' => 'required_with:zones|string|max:255|distinct',
            'zones.*.description_ar' => 'nullable|string',
            'zones.*.description_en' => 'nullable|string',
            'zones.*.max_temperature' => 'required_with:zones|integer',
            'zones.*.min_temperature' => 'required_with:zones|integer',
            'zones.*.storage_location_id' => [
                'required',
                'array',
            ],
            'zones.*.storage_location_id.*' => [
                'integer',
                'exists:storage_locations,id',
            ],
            // 'zones.*.storage_location_id' => 'required_with:zones|exists:storage_locations,id',
            'zones.*.rack_shelves' => 'nullable|array',
            'zones.*.rack_shelves.*.identifier' => 'required_with:zones.*.rack_shelves|string|max:255|distinct',
        ]);

        if ($validator->fails())
            return RespondWithBadRequestWithData($validator->errors());

        DB::beginTransaction();
        try {
            $user = auth('employee')->user();

            $store = Store::create([
                'branch_id'          => null,
                'country_id'          => $request->country_id,
                'area_id'          => $request->area_id,
                'city_id'          => $request->city_id,
                'street'          => $request->street,
                'code'          => $request->code,
                'max_storage'          => $request->max_storage,
                'min_storage'          => $request->min_storage,
                'building'          => $request->building,
                'phone'            => $request->phone,
                'name_ar'            => $request->name_ar,
                'name_en'            => $request->name_en,
                'description_ar'     => $request->description_ar,
                'description_en'     => $request->description_en,
                'is_kitchen'         => $request->is_kitchen,
                'created_by'         => authActionSave()['by'],
                'created_by_type'         => authActionSave()['type'],
            ]);
            // Zones
            $this->zones->store($request, $store);

            // Categories (bulk insert أو attach إذا عندك pivot)
            // foreach ($request->category_ids as $catId) {
            //     StoreCategory::create([
            //         'store_id'    => $store->id,
            //         'category_id' => $catId,
            //     ]);
            // }
            // dd($store->id);

            $store = Store::with(['zones.rackShelves'])->findOrFail($store->id);

            DB::commit();
            return ResponseWithSuccessData($request->header('lang', 'ar'), $store, 1);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return RespondWithBadRequestData($request->header('lang', 'ar'), 2);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'branch_id'          => 'required|exists:branches,id',
            'country_id' => 'required|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'street' => 'required',
            'building' => 'required',
            'phone' => 'nullable',
            'name_ar'            => 'required|string|max:255',
            'name_en'            => 'required|string|max:255',
            'latitude'            => 'nullable|max:255',
            'longitude'            => 'nullable|max:255',
            'code' => [
                'required',
                'string',
                Rule::unique('stores', 'code')->ignore($id, 'id'),
            ],
            'min_storage' => 'nullable|integer',
            'max_storage' => 'nullable|integer|gt:min_storage',
            'description_ar'     => 'nullable|string',
            'description_en'     => 'nullable|string',
            'is_kitchen'         => 'nullable|boolean',
            'zones' => 'nullable|array|min:1',
            'zones.*.name_ar' => 'required_with:zones|string|max:255',
            'zones.*.name_en' => 'required_with:zones|string|max:255',
            'zones.*.description_ar' => 'nullable|string',
            'zones.*.description_en' => 'nullable|string',
            'zones.*.max_temperature' => 'required_with:zones|integer',
            'zones.*.min_temperature' => 'required_with:zones|integer',
            'zones.*.storage_location_id' => [
                'required',
                'array',
            ],
            'zones.*.storage_location_id.*' => [
                'integer',
                'exists:storage_locations,id',
            ],
            // 'zones.*.storage_location_id' => 'required_with:zones|exists:storage_locations,id',
            'zones.*.rack_shelves' => 'nullable|array',
            // 'zones.*.rack_shelves.*.identifier' => 'required_with:zones.*.rack_shelves|string|max:255|distinct',
        ]);

        if ($validator->fails()) {
            return respondError('ValidationError', 400, $validator->errors());
        }
        // return RespondWithBadRequestWithData($validator->errors());

        DB::beginTransaction();
        try {
            $user = auth('employee')->user();

            $store = Store::find($id);
            if (!$store) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $store->update([
                'branch_id'          => $store->branch_id,
                'country_id'          => $request->country_id ?? $store->country_id,
                'area_id'          => $request->area_id ?? $store->area_id,
                'city_id'          => $request->city_id ?? $store->city_id,
                'street'          => $request->street ?? $store->street,
                'building'          => $request->building ?? $store->building,
                'phone'            => $request->phone ?? $store->phone,
                'name_ar'            => $request->name_ar ?? $store->name_ar,
                'name_en'            => $request->name_en ?? $store->name_en,
                'latitude'            => $request->latitude ?? $store->latitude,
                'longitude'            => $request->longitude ?? $store->longitude,
                'description_ar'     => $request->description_ar ?? $store->description_ar,
                'description_en'     => $request->description_en ?? $store->description_en,
                'code'          => $request->code ?? $store->code,
                'max_storage'          => $request->max_storage ?? $store->max_storage,
                'min_storage'          => $request->min_storage ?? $store->min_storage,
                'storage_location_id' => $request->storage_location_id ?? $store->storage_location_id,
                'is_kitchen'         => $request->is_kitchen ?? $store->is_kitchen,
                'modified_by'         => authActionSave()['by'],
                'modified_by_type'         => authActionSave()['type'],
            ]);

            // Zones
            $this->zones->update($request, $store->id, $store);

            // Update Categories
            // StoreCategory::where('store_id', $store->id)->delete();
            // foreach ($request->category_ids as $catId) {
            //     StoreCategory::create([
            //         'store_id'    => $store->id,
            //         'category_id' => $catId,
            //     ]);
            // }

            $store = Store::with(['zones.rackShelves'])->findOrFail($store->id);

            DB::commit();
            return ResponseWithSuccessData($request->header('lang', 'ar'), $store, 1);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e);
            return RespondWithBadRequestData($request->header('lang', 'ar'), 2);
        }
    }


    /**
     * Soft delete the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $store = Store::findOrFail($id);
            $store->update(['deleted_by' => auth()->id()]);
            $store->delete();
            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Restore a soft-deleted store.
     */
    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $store = Store::onlyTrashed()->find($id);
            if (!$store) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $store->deleted_by = null;
            $store->deleted_by_type = null;
            $store->modified_by = authActionSave()['by'];
            $store->modified_by_type = authActionSave()['type'];
            $store->restore();

            return ResponseWithSuccessData($lang, $store, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
