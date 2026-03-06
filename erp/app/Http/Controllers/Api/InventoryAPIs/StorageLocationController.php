<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\StorageLocation;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class StorageLocationController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $storageLocations = StorageLocation::query();
        if ($request->boolean('check_product')) {
            $storageLocations->whereHas('productBrands');
        }

        $storageLocations = paginateOrGetAll($storageLocations, $request, null, null);

        // Check if it's paginated data or a collection
        if (isset($storageLocations['data'])) {
            // It's paginated data (array format)
            $storageLocations['data'] = collect($storageLocations['data'])->map(function ($location) use ($lang) {
                if (is_array($location)) {
                    return [
                        'id' => $location['id'],
                        'name' => $lang == 'ar' ? $location['name_ar'] : $location['name_en'],
                        'name_ar' => $location['name_ar'],
                        'name_en' => $location['name_en'],
                        'description' => $lang == 'ar' ? $location['description_ar'] : $location['description_en'],
                        'description_ar' => $location['description_ar'],
                        'description_en' => $location['description_en']
                    ];
                } else {
                    return [
                        'id' => $location->id,
                        'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
                        'name_ar' => $location->name_ar,
                        'name_en' => $location->name_en,
                        'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
                        'description_ar' => $location->description_ar,
                        'description_en' => $location->description_en
                    ];
                }
            })->toArray();
        } else {
            // It's a simple collection
            $storageLocations = collect($storageLocations)->map(function ($location) use ($lang) {
                if (is_array($location)) {
                    return [
                        'id' => $location['id'],
                        'name' => $lang == 'ar' ? $location['name_ar'] : $location['name_en'],
                        'name_ar' => $location['name_ar'],
                        'name_en' => $location['name_en'],
                        'description' => $lang == 'ar' ? $location['description_ar'] : $location['description_en'],
                        'description_ar' => $location['description_ar'],
                        'description_en' => $location['description_en']
                    ];
                } else {
                    return [
                        'id' => $location->id,
                        'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
                        'name_ar' => $location->name_ar,
                        'name_en' => $location->name_en,
                        'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
                        'description_ar' => $location->description_ar,
                        'description_en' => $location->description_en
                    ];
                }
            })->toArray();
        }

        return ResponseWithSuccessDataPaginated($lang, $storageLocations, 1);
    }
    public function getStorageLocationByZone(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $zone = Zone::find($id);
        if (!$zone) {
            return response()->json([
                'code' => 404,
                'status' => false,
                'message' => $lang == 'en' ? 'Zone not found.' : 'المنطقة غير موجودة.',
                'data' => null,
            ]);
        }

        // Use the accessor
        $storageLocations = collect($zone->storage_locations_data)->map(function ($location) use ($lang) {
            return [
                'id' => $location->id,
                'name' => $lang == 'en' ? $location->name_en : $location->name_ar,
                'description' => $lang == 'en' ? $location->description_en : $location->description_ar,
            ];
        });

        return response()->json([
            'code' => 200,
            'status' => true,
            'message' => $lang == 'en'
                ? 'Storage locations retrieved successfully.'
                : 'تم جلب مواقع التخزين بنجاح.',
            'data' => $storageLocations,
        ]);
    }


    public function show($id, Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            App::setLocale($lang);

            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $location = StorageLocation::find($id);
            if (!$location) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => 'Storage Location not found',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }

            // Prepare response data with both localized and original fields
            $responseData = [
                'id' => $location->id,
                'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
                'name_ar' => $location->name_ar,
                'name_en' => $location->name_en,
                'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
                'description_ar' => $location->description_ar,
                'description_en' => $location->description_en
            ];

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:storage_locations,name_ar',
            'name_en' => 'required|string|unique:storage_locations,name_en',
            'description_ar' => 'nullable|string|unique:storage_locations,description_ar',
            'description_en' => 'nullable|string|unique:storage_locations,description_en',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }

        $name_ar = $request->name_ar;
        $name_en = $request->name_en;
        $description_ar = $request->description_ar;
        $description_en = $request->description_en;
        $craeted = authActionSave();
        $created_by = $craeted['by'];
        $created_by_type = $craeted['type'];

        $location = new StorageLocation();
        $location->name_ar = $name_ar;
        $location->name_en =  $name_en;
        $location->description_ar = $description_ar;
        $location->description_en = $description_en;
        $location->created_by =  $created_by;
        $location->created_by_type =  $created_by_type;
        $location->save();

        // Prepare response data with both localized and original fields
        $responseData = [
            'id' => $location->id,
            'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
            'name_ar' => $location->name_ar,
            'name_en' => $location->name_en,
            'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
            'description_ar' => $location->description_ar,
            'description_en' => $location->description_en
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        App::setLocale($lang);

        // Retrieve the location by ID, or throw an exception if not found
        $location = StorageLocation::find($id);
        if (!$location) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Storage Location not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }
        // Prevent updating records with IDs 1 to 4
        if ($id >= 1 && $id <= 4) {
            return response()->json([
                'code' => 403,
                'status' => false,
                'message' => $lang == 'en' ? 'Cannot edit protected records (ID 1-4)' : 'لا يمكن تعديل السجلات المحمية (ID 1-4)',
                'data' => null,
                'errorData' => null,
                'validation_type' => true
            ], 403);
        }

        // Validate the input
        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|unique:storage_locations,name_ar,' . $id,
            'name_en' => 'required|unique:storage_locations,name_en,' . $id,
            'description_ar' => 'nullable|string|unique:storage_locations,description_ar,' . $id,
            'description_en' => 'nullable|string|unique:storage_locations,description_en,' . $id,
        ]);

        if ($validator->fails()) {
            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
        }

        $craeted = authActionSave();
        $modified_by = $craeted['by'];
        $modified_by_type = $craeted['type'];

        $location->name_ar = $request->name_ar;
        $location->name_en = $request->name_en;
        $location->description_ar = $request->description_ar;
        $location->description_en = $request->description_en;
        $location->modified_by = $modified_by;
        $location->modified_by_type = $modified_by_type;
        $location->save();

        // Prepare response data with both localized and original fields
        $responseData = [
            'id' => $location->id,
            'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
            'name_ar' => $location->name_ar,
            'name_en' => $location->name_en,
            'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
            'description_ar' => $location->description_ar,
            'description_en' => $location->description_en
        ];

        // Return success response
        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    public function archieve(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {

            $location = StorageLocation::find($id);
            if (!$location) {
                return [
                    'code' => 404,
                    'status' => false,
                    'message' => $lang == 'en' ? 'Storage location not found.' : 'موقع التخزين غير موجود.',
                    'data' => null,
                    'errorData' => null,
                    'validation_type' => false
                ];
            }
            // Prevent updating records with IDs 1 to 4
            // if ($id >= 1 && $id <= 4) {
            //     return response()->json([
            //         'code' => 403,
            //         'status' => false,
            //         'message' => $lang == 'en' ? 'Cannot edit protected records (ID 1-4)' : 'لا يمكن حذف السجلات المحمية (ID 1-4)',
            //         'data' => null,
            //         'errorData' => null,
            //         'validation_type' => true
            //     ], 403);
            // }
            $authData = authActionSave();

            $location->update([
                'deleted_by'       => $authData['by'],
                'deleted_by_type'  => $authData['type'],
            ]);
            $location->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function restore(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        try {

            $location = StorageLocation::onlyTrashed()->find($id);

            if (!$location) {
                return response()->json([
                    'status'  => false,
                    'message' => $lang == 'en' ? 'Storage location not found.' : 'موقع التخزين غير موجود.',
                    'code' => 404,
                    'data' => null
                ], 404);
            }
            $authData = authActionSave();

            $location->update([
                'modified_by'       => null,
                'modified_by_type'  => null,
                'deleted_by'       => $authData['by'],
                'deleted_by_type'  => $authData['type'],
            ]);
            $location->restore();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function allArchive(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        $trashedLocations = StorageLocation::onlyTrashed();

        // Check if there are any trashed records before calling paginateOrGetAll
        if ($trashedLocations->count() === 0) {
            // Return empty response structure instead of 404
            $emptyResponse = [
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $request->per_page ?? 15,
                'total' => 0
            ];
            return ResponseWithSuccessDataPaginated($lang, $emptyResponse, 1);
        }

        $trashedLocations = paginateOrGetAll($trashedLocations, $request, null, null);

        // If paginateOrGetAll returns a 404 response, handle it
        if (isset($trashedLocations['code']) && $trashedLocations['code'] == 404) {
            // Return empty data instead of 404
            $emptyResponse = [
                'data' => [],
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => $request->per_page ?? 15,
                'total' => 0
            ];
            return ResponseWithSuccessDataPaginated($lang, $emptyResponse, 1);
        }

        // Check if it's paginated data or a collection
        if (isset($trashedLocations['data'])) {
            // It's paginated data (array format)
            $trashedLocations['data'] = collect($trashedLocations['data'])->map(function ($location) use ($lang) {
                if (is_array($location)) {
                    return [
                        'id' => $location['id'],
                        'name' => $lang == 'ar' ? $location['name_ar'] : $location['name_en'],
                        'name_ar' => $location['name_ar'],
                        'name_en' => $location['name_en'],
                        'description' => $lang == 'ar' ? $location['description_ar'] : $location['description_en'],
                        'description_ar' => $location['description_ar'],
                        'description_en' => $location['description_en'],
                        'deleted_at' => $location['deleted_at'],
                        'deleted_by' => $location['deleted_by'],
                        'deleted_by_type' => $location['deleted_by_type']
                    ];
                } else {
                    return [
                        'id' => $location->id,
                        'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
                        'name_ar' => $location->name_ar,
                        'name_en' => $location->name_en,
                        'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
                        'description_ar' => $location->description_ar,
                        'description_en' => $location->description_en,
                        'deleted_at' => $location->deleted_at,
                        'deleted_by' => $location->deleted_by,
                        'deleted_by_type' => $location->deleted_by_type
                    ];
                }
            })->toArray();
        } else {
            // It's a simple collection
            $trashedLocations = collect($trashedLocations)->map(function ($location) use ($lang) {
                if (is_array($location)) {
                    return [
                        'id' => $location['id'],
                        'name' => $lang == 'ar' ? $location['name_ar'] : $location['name_en'],
                        'name_ar' => $location['name_ar'],
                        'name_en' => $location['name_en'],
                        'description' => $lang == 'ar' ? $location['description_ar'] : $location['description_en'],
                        'description_ar' => $location['description_ar'],
                        'description_en' => $location['description_en'],
                        'deleted_at' => $location['deleted_at'],
                        'deleted_by' => $location['deleted_by'],
                        'deleted_by_type' => $location['deleted_by_type']
                    ];
                } else {
                    return [
                        'id' => $location->id,
                        'name' => $lang == 'ar' ? $location->name_ar : $location->name_en,
                        'name_ar' => $location->name_ar,
                        'name_en' => $location->name_en,
                        'description' => $lang == 'ar' ? $location->description_ar : $location->description_en,
                        'description_ar' => $location->description_ar,
                        'description_en' => $location->description_en,
                        'deleted_at' => $location->deleted_at,
                        'deleted_by' => $location->deleted_by,
                        'deleted_by_type' => $location->deleted_by_type
                    ];
                }
            })->toArray();
        }

        return ResponseWithSuccessDataPaginated($lang, $trashedLocations, 1);
    }
}
