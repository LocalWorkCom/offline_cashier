<?php


namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StoreWarehouseRequest;
use App\Http\Requests\Inventory\UpdateWarehouseRequest;
use App\Models\Warehouse;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $warehouses = Warehouse::with(['zones.rackShelves'])->get();

        $data = $warehouses->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
                'zones' => $warehouse->zones->map(function ($zone) {
                    $zoneData = [
                        'id' => $zone->id,
                        'store_id' => $zone->warehouse_id,
                        'name' => $zone->name,
                        'description' => $zone->description,
                        'storage_location_id' => $zone->storage_location_id,
                    ];
                    if ($zone->rackShelves->isNotEmpty()) {
                        $zoneData['rack_shelves'] = $zone->rackShelves->map(function ($rackShelf) {
                            return [
                                'id' => $rackShelf->id,
                                'zone_id' => $rackShelf->zone_id,
                                'identifier' => $rackShelf->identifier,
                            ];
                        })->toArray();
                    }
                    return $zoneData;
                })->toArray(),
            ];
        })->toArray();

        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function store(Request $request, $warehouse)
    {
        $lang = $request->header('lang', 'ar');
        \Illuminate\Support\Facades\App::setLocale($lang);

        $input = $request->all();

        try {
            DB::beginTransaction();

            // $warehouse = Warehouse::create([
            //     'name' => $input['name'],
            //     'address' => $input['address'] ?? null,
            // ]);

            foreach ($input['zones'] as $zoneData) {
                $zone = Zone::create([
                    'store_id' => $warehouse->id,
                    'name_ar' => $zoneData['name_ar'],
                    'name_en' => $zoneData['name_en'],
                    'description_ar' => $zoneData['description_ar'] ?? null,
                    'description_en' => $zoneData['description_en'] ?? null,
                    'storage_location_id' => $zoneData['storage_location_id'],
                    'max_temperature' => $zoneData['max_temperature'],
                    'min_temperature' => $zoneData['min_temperature'],
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
                // dd($warehouse->id);
                // dd($zone);

                if (isset($zoneData['rack_shelves']) && is_array($zoneData['rack_shelves'])) {
                    foreach ($zoneData['rack_shelves'] as $rackShelfData) {
                        $zone->rackShelves()->create([
                            'identifier' => $rackShelfData['identifier'],
                        ]);
                    }
                }
            }

            DB::commit();

            // $warehouse->load(['zones.rackShelves']);
            // $data = [
            //     'id' => $warehouse->id,
            //     'name' => $warehouse->name,
            //     'address' => $warehouse->address,
            //     'created_at' => $warehouse->created_at,
            //     'updated_at' => $warehouse->updated_at,
            //     'zones' => $warehouse->zones->map(function ($zone) {
            //         $zoneData = [
            //             'id' => $zone->id,
            //             'warehouse_id' => $zone->warehouse_id,
            //             'name' => $zone->name,
            //             'description' => $zone->description,
            //             'storage_location_id' => $zone->storage_location_id,
            //         ];
            //         if ($zone->rackShelves->isNotEmpty()) {
            //             $zoneData['rack_shelves'] = $zone->rackShelves->map(function ($rackShelf) {
            //                 return [
            //                     'id' => $rackShelf->id,
            //                     'zone_id' => $rackShelf->zone_id,
            //                     'identifier' => $rackShelf->identifier,
            //                 ];
            //             })->toArray();
            //         }
            //         return $zoneData;
            //     })->toArray(),
            // ];

            return ResponseWithSuccessData($lang, "efc", 1);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $lang == 'en' ? 'Failed to create warehouse' : 'فشل في إنشاء المستودع',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function show(\Illuminate\Http\Request $request, $id)
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lang = $request->header('lang', 'ar');
        \Illuminate\Support\Facades\App::setLocale($lang);

        $warehouse = Warehouse::with(['zones.rackShelves'])->findOrFail($id);

        $data = [
            'id' => $warehouse->id,
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'created_at' => $warehouse->created_at,
            'updated_at' => $warehouse->updated_at,
            'zones' => $warehouse->zones->map(function ($zone) {
                $zoneData = [
                    'id' => $zone->id,
                    'store_id' => $zone->warehouse_id,
                    'name' => $zone->name,
                    'description' => $zone->description,
                    'storage_location_id' => $zone->storage_location_id,
                ];
                if ($zone->rackShelves->isNotEmpty()) {
                    $zoneData['rack_shelves'] = $zone->rackShelves->map(function ($rackShelf) {
                        return [
                            'id' => $rackShelf->id,
                            'zone_id' => $rackShelf->zone_id,
                            'identifier' => $rackShelf->identifier,
                        ];
                    })->toArray();
                }
                return $zoneData;
            })->toArray(),
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }


    public function update(Request $request, $id, $warehouse)
    {
        $lang = $request->header('lang', 'ar');
        \Illuminate\Support\Facades\App::setLocale($lang);

        // Log::debug('Starting warehouse update', ['warehouse_id' => $id, 'input' => $request->all()]);

        $input = $request->all();

        try {
            DB::beginTransaction();

            // $warehouse->update([
            //     'name' => $input['name'] ?? $warehouse->name,
            //     'address' => $input['address'] ?? $warehouse->address,
            // ]);

            if ($request->has('zones')) {
                // $existingZoneIds = $warehouse->zones()->pluck('id')->toArray();
                // $updatedZoneIds = array_filter(array_column($input['zones'], 'id'), fn($id) => is_numeric($id) && Zone::where('id', $id)->exists());
                // $zonesToDelete = array_diff($existingZoneIds, $updatedZoneIds);

                // Zone::whereIn('id', $zonesToDelete)->delete();
                $existingZoneIds = Zone::where('store_id', $warehouse->id)->pluck('id')->toArray();
                $updatedZoneIds = [];

                foreach ($input['zones'] as $zoneData) {
                    $isNewZone = !isset($zoneData['id']) || !Zone::find($zoneData['id']);
                    $zone = $isNewZone ? new Zone() : Zone::find($zoneData['id']);

                    if ($isNewZone && (!isset($zoneData['name_ar']) || !isset($zoneData['name_en']) || !isset($zoneData['min_temperature']) || !isset($zoneData['max_temperature']) || !isset($zoneData['storage_location_id']))) {
                        throw new \Exception($lang == 'en' ? 'Name and storage location are required for new zones.' : 'الاسم وموقع التخزين مطلوبان للمناطق الجديدة.');
                    }

                    $zone->store_id = $warehouse->id;
                    $zone->name_ar = $zoneData['name_ar'] ?? $zone->name ?? '';
                    $zone->name_en = $zoneData['name_en'] ?? $zone->name ?? '';
                    $zone->description_ar = $zoneData['description_ar'] ?? $zone->description_ar ?? null;
                    $zone->description_en = $zoneData['description_en'] ?? $zone->description_en ?? null;
                    $zone->max_temperature = $zoneData['max_temperature'] ?? $zone->max_temperature ?? null;
                    $zone->min_temperature = $zoneData['min_temperature'] ?? $zone->min_temperature ?? null;
                    $zone->modified_by = authActionSave()['by'];
                    $zone->modified_by_type = authActionSave()['type'];
                    $zone->storage_location_id = $zoneData['storage_location_id'] ?? $zone->storage_location_id ?? null;
                    $zone->save();
                    $updatedZoneIds[] = $zone->id;
                    if (array_key_exists('rack_shelves', $zoneData)) {

                        // GET all existing shelf IDs
                        $existingRackShelfIds = $zone->rackShelves()->pluck('id')->toArray();

                        // GET submitted shelf IDs (only existing ones)
                        $updatedRackShelfIds = array_filter(
                            array_column($zoneData['rack_shelves'], 'id'),
                            fn($id) => is_numeric($id)
                        );

                        // SHELVES TO DELETE = existing - submitted
                        $rackShelvesToDelete = array_diff($existingRackShelfIds, $updatedRackShelfIds);

                        // DELETE removed shelves
                        if (!empty($rackShelvesToDelete)) {
                            $zone->rackShelves()->whereIn('id', $rackShelvesToDelete)->delete();
                        }

                        // NOW handle create / update
                        foreach ($zoneData['rack_shelves'] as $rackShelfData) {
                            $isNewRackShelf = !isset($rackShelfData['id']) ||
                                !$zone->rackShelves()->find($rackShelfData['id']);

                            $rackShelf = $isNewRackShelf
                                ? $zone->rackShelves()->create([
                                    'identifier' => $rackShelfData['identifier']
                                ])
                                : $zone->rackShelves()->find($rackShelfData['id']);

                            if (!$isNewRackShelf) {
                                $rackShelf->update([
                                    'identifier' => $rackShelfData['identifier'] ?? $rackShelf->identifier,
                                ]);
                            }
                        }
                    }
                }
            }
            $zonesToDelete = array_diff($existingZoneIds, $updatedZoneIds);
            if (!empty($zonesToDelete)) {
                Zone::whereIn('id', $zonesToDelete)->each(function ($zone) {
                    $zone->rackShelves()->delete(); // delete related shelves first
                    $zone->delete(); // then delete zone
                });
            }
            DB::commit();

            $warehouse->load(['zones.rackShelves']);
            $data = [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'address' => $warehouse->address,
                'created_at' => $warehouse->created_at,
                'updated_at' => $warehouse->updated_at,
                'zones' => $warehouse->zones->map(function ($zone) {
                    $zoneData = [
                        'id' => $zone->id,
                        'store_id' => $zone->warehouse_id,
                        'name_ar' => $zone->name_ar,
                        'name_en' => $zone->name_en,
                        'description_ar' => $zone->description_ar,
                        'description_en' => $zone->description_en,
                        'min_temperature' => $zone->min_temperature,
                        'max_temperature' => $zone->max_temperature,
                        'storage_location_id' => $zone->storage_location_id,
                    ];
                    if ($zone->rackShelves->isNotEmpty()) {
                        $zoneData['rack_shelves'] = $zone->rackShelves->map(function ($rackShelf) {
                            return [
                                'id' => $rackShelf->id,
                                'zone_id' => $rackShelf->zone_id,
                                'identifier' => $rackShelf->identifier,
                            ];
                        })->toArray();
                    }
                    return $zoneData;
                })->toArray(),
            ];

            Log::debug('Warehouse update successful', ['warehouse_id' => $id]);

            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Warehouse update failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'status' => 'error',
                'message' => $lang == 'en' ? 'Failed to update warehouse' : 'فشل في تحديث المستودع',
                'errors' => [$e->getMessage()],
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $warehouse = Warehouse::findOrFail($id);
        $warehouse->delete();
        return response()->json([
            'status' => 'success',
            'message' => $lang == 'en' ? 'Warehouse deleted successfully' : 'تم حذف المستودع بنجاح',
            'data' => null,
        ]);
    }
}
