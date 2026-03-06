<?php

namespace App\Http\Controllers\Api\HR_APIs;

use Illuminate\Http\Request;
use App\Models\Nationality;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Services\HR_Services\NationalityService;

class NationalityController extends Controller
{
    protected $NationalityService;

    public function __construct(NationalityService $NationalityService)
    {
        $this->NationalityService = $NationalityService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        try {
            // $response = $this->NationalityService->index($request, false);
            $response = $this->NationalityService->index(false);

            // Now $response is a query builder, so paginateOrGetAll can work with it
            $result = paginateOrGetAll($response, $request, null);

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        // try {
            $exists = Nationality::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $Nationality = Nationality::with(['employees' => function ($query) use ($request) {
                if ($request->has('name') && !empty($request->name)) {
                    $query->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $request->name . '%']);
                }
            }])
            ->withCount('employees')
            ->findOrFail($id);
            return ResponseWithSuccessData($lang, $Nationality, 1);
        // } catch (\Exception $e) {
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->NationalityService->store($request, false);

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
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $result = $this->NationalityService->update($request, $id, false);

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

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = Nationality::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->NationalityService->delete($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
