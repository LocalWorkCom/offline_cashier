<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\ZoneService;

class ZoneController extends Controller
{
    protected $zone;

    public function __construct(ZoneService $zone)
    {
        $this->zone = $zone;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {

            // Start base query
            $query = Zone::with('warehouse');

            // 🔥 Filter only zones assigned to product brands
            if ($request->boolean('check_product')) {
                $query->whereHas('productStores');
            }

            // Apply paginateOrGetAll directly
            $result = paginateOrGetAll($query, $request, null);

            // Check if result is paginated or a collection
            if (isset($result['data']) && isset($result['meta'])) {
                // It's already formatted as API response (array)
                $data = collect($result['data'])->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                $result['data'] = $data;

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $result['data'],
                    'meta' => $result['meta'] // Preserve pagination meta
                ], 200);
            } else if (method_exists($result, 'getCollection')) {
                // It's a paginator object
                $data = $result->getCollection()->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                $result->setCollection($data);

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $result
                ], 200);
            } else {
                // It's a simple collection/array
                $data = collect($result)->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $data
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Bad Request', [], $lang),
                'code' => 400,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Format zone data for consistent response structure
     */
    private function formatZoneData($zone, $lang)
    {
        return [
            'id' => $zone->id,
            'name' => $lang === 'ar' ? $zone->name_ar : $zone->name_en,
            'description' => $lang === 'ar' ? $zone->description_ar : $zone->description_en,
            'name_ar' => $zone->name_ar,
            'name_en' => $zone->name_en,
            'description_ar' => $zone->description_ar,
            'description_en' => $zone->description_en,
            'warehouse_id' => $zone->store_id,
            'storage_location_id' => $zone->storage_location_id ?? [],
            'storage_locations_data' => collect($zone->storage_locations_data)->map(function ($loc) use ($lang) {
                return [
                    'id' => $loc->id,
                    'name_en' => $loc->name_en,
                    'name_ar' => $loc->name_ar,
                    'description_en' => $loc->description_en,
                    'description_ar' => $loc->description_ar,
                    'name' => $lang === 'ar' ? $loc->name_ar : $loc->name_en,
                    'description' => $lang === 'ar' ? $loc->description_ar : $loc->description_en,
                ];
            }),
            'warehouse' => $zone->warehouse ? [
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
                'name' => $lang === 'ar' ? $zone->warehouse->name_ar : $zone->warehouse->name_en,
            ] : null,
            'created_by' => $zone->created_by,
            'created_by_type' => $zone->created_by_type,
            'created_at' => $zone->created_at,
            'updated_at' => $zone->updated_at,
        ];
    }


    public function getZonesByStore(Request $request, $storeId)
    {
        return $this->zone->getZonesByStore($request, $storeId);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        try {
            $zone = Zone::with(['warehouse'])->find($id);

            if (!$zone) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            // Use the same formatting method as index
            $formattedZone = $this->formatZoneData($zone, $lang);

            return ResponseWithSuccessData($lang, $formattedZone, 1);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Bad Request', [], $lang),
                'code' => 400,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->zone->store($request, false);

            if (is_array($result) && isset($result['code'])) {
                return response()->json($result, $result['code']);
            }

            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);

        try {
            $result = $this->zone->update($request, $id, false);

            // Check if result is an array (error response)
            if (is_array($result) && isset($result['code'])) {
                // Return the error response directly without wrapping
                return response()->json($result, $result['code']);
            }

            // If it's a BusinessActivity object, return success response
            return ResponseWithSuccessData($lang, $result, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }

    public function archive(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            return $this->zone->archive($request, $id);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        try {
            return $this->zone->restore($request, $id);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function allArchive(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            // Pass query builder to pagination helper
            $query = Zone::onlyTrashed()->with('warehouse');

            // Apply paginateOrGetAll directly
            $result = paginateOrGetAll($query, $request, null);

            // Check if result is paginated or a collection
            if (isset($result['data']) && isset($result['meta'])) {
                // It's already formatted as API response (array)
                $data = collect($result['data'])->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                $result['data'] = $data;

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $result['data'],
                    'meta' => $result['meta'] // Preserve pagination meta
                ], 200);
            } else if (method_exists($result, 'getCollection')) {
                // It's a paginator object
                $data = $result->getCollection()->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                $result->setCollection($data);

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $result
                ], 200);
            } else {
                // It's a simple collection/array
                $data = collect($result)->map(function ($zone) use ($lang) {
                    return $this->formatZoneData($zone, $lang);
                });

                return response()->json([
                    'status' => true,
                    'message' => __('Success Message', [], $lang),
                    'code' => 200,
                    'data' => $data
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Bad Request', [], $lang),
                'code' => 400,
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
