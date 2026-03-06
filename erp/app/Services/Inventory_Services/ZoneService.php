<?php


namespace App\Services\Inventory_Services;

use App\Models\Store;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ZoneService
{

    public function index(Request $request)
    {
        // Return query builder for pagination
        return Zone::with(['warehouse']);
    }

    public function show(Request $request, $id)
    {
        $lang = app()->getLocale();

        $zone = Zone::with(['warehouse'])->find($id);
        if (!$zone) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        // Return success response
        return ResponseWithSuccessData($lang, $zone, 1);
    }

    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            // Return array instead of response to avoid nesting
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('zones')
                    ->where('store_id', $request->store_id)
                    ->whereNull('deleted_at'),
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('zones')
                    ->where('store_id', $request->store_id)
                    ->whereNull('deleted_at'),
            ],
            'description_ar' => [
                'nullable',
                'string',
            ],
            'description_en' => [
                'nullable',
                'string',
            ],
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'storage_location_id' => [
                'required',
                'array',
            ],
            'storage_location_id.*' => [
                'integer',
                'exists:storage_locations,id',
            ],
            // 'min_temperature' => [
            //     'required',
            //     'integer',
            // ],
            // 'max_temperature' => [
            //     'required',
            //     'integer',
            //     'gt:min_temperature',
            // ],
        ]);

        if ($validator->fails()) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $zone = new Zone();
        $zone->name_ar = $request->name_ar;
        $zone->name_en = $request->name_en;
        $zone->description_ar = $request->description_ar ?? null;
        $zone->description_en = $request->description_en ?? null;
        $zone->store_id = $request->store_id;
        $zone->storage_location_id = $request->storage_location_id;
        // $zone->min_temperature = $request->min_temperature;
        // $zone->max_temperature = $request->max_temperature;
        $zone->created_by = authActionSave()['by'];
        $zone->created_by_type = authActionSave()['type'];

        $zone->save();

        // Load the relationships
        $zone->load('warehouse');

        // Prepare storage locations data using the accessor
        $storageLocationsData = [];
        if ($zone->storage_locations_data && $zone->storage_locations_data->count() > 0) {
            foreach ($zone->storage_locations_data as $location) {
                $storageLocationsData[] = [
                    'id' => $location->id,
                    'name_en' => $location->name_en,
                    'name_ar' => $location->name_ar,
                    'description_ar' => $location->description_ar,
                    'description_en' => $location->description_en,
                    'name' => $lang == 'en' ? $location->name_en : $location->name_ar,
                    'description' => $lang == 'en' ? $location->description_en : $location->description_ar,
                ];
            }
        }

        // Prepare warehouse data
        $warehouseData = null;
        if ($zone->warehouse) {
            $warehouseData = [
                'id' => $zone->warehouse->id,
                'branch_id' => $zone->warehouse->branch_id,
                'name_en' => $zone->warehouse->name_en,
                'name_ar' => $zone->warehouse->name_ar,
                'description_en' => $zone->warehouse->description_en,
                'description_ar' => $zone->warehouse->description_ar,
                'is_kitchen' => $zone->warehouse->is_kitchen,
                'code' => $zone->warehouse->code,
                'country_id' => $zone->warehouse->country_id,
                'city_id' => $zone->warehouse->city_id,
                'area_id' => $zone->warehouse->area_id,
                'street' => $zone->warehouse->street,
                'building' => $zone->warehouse->building,
                'phone' => $zone->warehouse->phone,
                'status' => $zone->warehouse->status,
                'min_storage' => $zone->warehouse->min_storage,
                'max_storage' => $zone->warehouse->max_storage,
                'name' => $lang == 'en' ? $zone->warehouse->name_en : $zone->warehouse->name_ar,
            ];
        }

        // Prepare response with name and description based on locale
        $responseData = [
            'id' => $zone->id,
            'name' => $lang == 'en' ? $zone->name_en : $zone->name_ar,
            'description' => $lang == 'en' ? $zone->description_en : $zone->description_ar,
            'name_ar' => $zone->name_ar,
            'name_en' => $zone->name_en,
            'description_ar' => $zone->description_ar,
            'description_en' => $zone->description_en,
            'warehouse_id' => $zone->store_id,
            'storage_location_id' => $zone->storage_location_id,
            'storage_locations_data' => $storageLocationsData,
            'warehouse' => $warehouseData,
            'created_by' => $zone->created_by,
            'created_by_type' => $zone->created_by_type,
            'created_at' => $zone->created_at,
            'updated_at' => $zone->updated_at,
        ];

        return $responseData;
    }
    public function getZonesByStore(Request $request, $storeId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Validate store exists
        $storeExists = Store::where('id', $storeId)->exists();
        if (!$storeExists) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Store not found',
                'data' => null,
            ];
        }

        // Fetch zones for the store (with related storage locations + warehouse)
        $zones = Zone::where('store_id', $storeId)
            ->get();

        // Format zones data
        $data = $zones->map(function ($zone) use ($lang) {
            return [
                'id' => $zone->id,
                'name' => $lang == 'en' ? $zone->name_en : $zone->name_ar,
                'description' => $lang == 'en' ? $zone->description_en : $zone->description_ar,
            ];
        });

        return [
            'code' => 200,
            'status' => true,
            'message' => 'Zones fetched successfully',
            'data' => $data,
        ];
    }


    public function update(Request $request, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            // Return array for error instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Token validation failed',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        $zone = Zone::find($id);
        if (!$zone) {
            // Return array for error instead of response
            return [
                'code' => 404,
                'status' => false,
                'message' => 'zone not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('zones')
                    ->where('store_id', $request->store_id)
                    ->whereNull('deleted_at')->ignore($id),
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('zones')
                    ->where('store_id', $request->store_id)
                    ->whereNull('deleted_at')->ignore($id),
            ],
            'description_ar' => [
                'nullable',
                'string',
            ],
            'description_en' => [
                'nullable',
                'string',
            ],
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'storage_location_id' => [
                'required',
                'array',
            ],
            'storage_location_id.*' => [
                'integer',
                'exists:storage_locations,id',
            ],
            // 'min_temperature' => [
            //     'required',
            //     'integer',
            // ],
            // 'max_temperature' => [
            //     'required',
            //     'integer',
            //     'gt:min_temperature',
            // ],
        ]);

        if ($validator->fails()) {
            // Return array for validation errors instead of response
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // $zone = zone::find($id);
        // // Check if names changed
        // if ($zone->name_ar == $request->name_ar && $zone->name_en == $request->name_en) {
        //     return [
        //         'code' => 400,
        //         'status' => false,
        //         'message' => 'zone not change',
        //         'data' => null,
        //         'errorData' => $validator->errors(),
        //         'validation_type' => true
        //     ];
        // }

        // Check uniqueness but exclude the current record
        $exists_ar = zone::where('id', '!=', $id)->where('name_ar', $request->name_ar)->exists();
        $exists_en = zone::where('id', '!=', $id)->where('name_en', $request->name_en)->exists();

        if ($exists_ar || $exists_en) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Zone not change',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        $zone->name_ar = $request->name_ar;
        $zone->name_en = $request->name_en;
        $zone->description_ar = $request->description_ar ?? null;
        $zone->description_en = $request->description_en ?? null;
        $zone->store_id = $request->store_id;
        $zone->storage_location_id = $request->storage_location_id;
        // $zone->min_temperature = $request->min_temperature;
        // $zone->max_temperature = $request->max_temperature;
        $zone->modified_by = authActionSave()['by'];
        $zone->modified_by_type = authActionSave()['type'];

        $zone->save();

        // Refresh to get updated relationships
        $zone->load('warehouse');

        // Prepare storage locations data using the accessor
        $storageLocationsData = [];
        if ($zone->storage_locations_data && $zone->storage_locations_data->count() > 0) {
            foreach ($zone->storage_locations_data as $location) {
                $storageLocationsData[] = [
                    'id' => $location->id,
                    'name_en' => $location->name_en,
                    'name_ar' => $location->name_ar,
                    'description_ar' => $location->description_ar,
                    'description_en' => $location->description_en,
                    'name' => $lang == 'en' ? $location->name_en : $location->name_ar,
                    'description' => $lang == 'en' ? $location->description_en : $location->description_ar,
                ];
            }
        }

        // Prepare warehouse data
        $warehouseData = null;
        if ($zone->warehouse) {
            $warehouseData = [
                'id' => $zone->warehouse->id,
                'branch_id' => $zone->warehouse->branch_id,
                'name_en' => $zone->warehouse->name_en,
                'name_ar' => $zone->warehouse->name_ar,
                'description_en' => $zone->warehouse->description_en,
                'description_ar' => $zone->warehouse->description_ar,
                'is_kitchen' => $zone->warehouse->is_kitchen,
                'code' => $zone->warehouse->code,
                'country_id' => $zone->warehouse->country_id,
                'city_id' => $zone->warehouse->city_id,
                'area_id' => $zone->warehouse->area_id,
                'street' => $zone->warehouse->street,
                'building' => $zone->warehouse->building,
                'phone' => $zone->warehouse->phone,
                'status' => $zone->warehouse->status,
                'min_storage' => $zone->warehouse->min_storage,
                'max_storage' => $zone->warehouse->max_storage,
                'name' => $lang == 'en' ? $zone->warehouse->name_en : $zone->warehouse->name_ar,
            ];
        }

        // Prepare response with name and description based on locale
        $responseData = [
            'id' => $zone->id,
            'name' => $lang == 'en' ? $zone->name_en : $zone->name_ar,
            'description' => $lang == 'en' ? $zone->description_en : $zone->description_ar,
            'name_ar' => $zone->name_ar,
            'name_en' => $zone->name_en,
            'description_ar' => $zone->description_ar,
            'description_en' => $zone->description_en,
            'warehouse_id' => $zone->store_id,
            'storage_location_id' => $zone->storage_location_id,
            'storage_locations_data' => $storageLocationsData,
            'warehouse' => $warehouseData,
            'created_by' => $zone->created_by,
            'created_by_type' => $zone->created_by_type,
            'modified_by' => $zone->modified_by,
            'modified_by_type' => $zone->modified_by_type,
            'created_at' => $zone->created_at,
            'updated_at' => $zone->updated_at,
        ];

        return $responseData;
    }

    public function archive(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        $zone = Zone::find($id);
        if (!$zone) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        if ($zone->productStores()->exists()) {
            return respondError(__('zone.has_product_stores'), 400);
        }

        // Check audits relation
        if ($zone->audits()->exists()) {
            return respondError(__('zone.has_audits'), 400);
        }

        $zone->deleted_by = authActionSave()['by'];
        $zone->deleted_by_type = authActionSave()['type'];
        $zone->save();
        $zone->delete();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        app()->setLocale($lang);
        $zone = Zone::onlyTrashed()->find($id);
        if (!$zone) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $zone->deleted_by = null;
        $zone->deleted_by_type = null;
        $zone->modified_by = authActionSave()['by'];
        $zone->modified_by_type = authActionSave()['type'];
        $zone->save();
        $zone->restore();
        return RespondWithSuccessRequest($lang, 1);
    }
}
