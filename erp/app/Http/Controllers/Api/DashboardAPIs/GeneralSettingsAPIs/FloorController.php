<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Floor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            // Check authentication first
            $employee = auth('employee')->user();
            if (!$employee) {
                return RespondWithBadRequestData($lang, 2); // Assuming code 2 is for unauthorized
            }
            $query = Floor::with(['floorPartitions.tables', 'branches']);

            $flags = ['waiter', 'cashier', 'customer_service', 'branch manager'];
            // Add branch manager filter
            if (in_array($employee->flag, $flags)) {
                $branch_id = $employee->branch_id;
                if ($branch_id || $employee->hasRole('Branch_Manager')) {
                    $query->with('branches')
                        ->where('branch_id', $branch_id);
                }
            } else {
                // Super admin or others → allow request filter
                if ($request->filled('branch_id')) {
                    $query->where('branch_id', $request->branch_id);
                }
            }
            $result = paginateOrGetAll($query, $request, null);
            if ($result['data']) {
                $result['data']->transform(function ($item) use ($lang) {
                    $item->type_name = $item->type == 1 ? $lang == 'en' ? 'in door' : 'داخلي' : ($item->type == 2 ? $lang == 'en' ? 'outdoor' : 'خارجي' : ($item->type == 3 ? $lang == 'en' ? 'both' : 'النوعين' : ''));
                    $item->smoking_name = $item->smoking == 1 ? $lang == 'en' ? 'smoking' : 'مدخن' : ($item->smoking == 2 ? $lang == 'en' ? 'non smoking' : 'غير مدخن' : ($item->smoking == 3 ? $lang == 'en' ? 'both' : 'النوعين' : ''));
                    return $item;
                });
            }
            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2); // Assuming code 2 is for server error
        }
    }
    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1|in:1,2,3',
                'smoking' => 'required|integer|min:1|in:1,2,3'
            ]);

            if ($validateData->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validateData->errors(),
                    'validation_type' => true
                ], 400);
            }


            // $user_id = Auth::guard('api')->user()->id;
            $floor = new Floor();
            $floor->branch_id = $request->branch_id;
            $floor->name_ar = $request->name_ar;
            $floor->name_en = $request->name_en;
            $floor->type = $request->type;
            $floor->smoking = $request->smoking;
            $floor->created_by = authActionSave()['by'];
            $floor->created_by_type = authActionSave()['type'];
            $floor->save();

            return ResponseWithSuccessData($lang, $floor, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Floor::where('id', $request->id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:floors,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1|in:1,2,3',
                'smoking' => 'required|integer|min:1|in:1,2,3'
            ]);

            if ($validateData->fails()) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => $validateData->errors(),
                    'validation_type' => true
                ], 400);
            }

            $floor = Floor::findOrFail($request->id);
            $floor->branch_id = $request->branch_id;
            $floor->name_ar = $request->name_ar;
            $floor->name_en = $request->name_en;
            $floor->type = $request->type;
            $floor->smoking = $request->smoking;
            $floor->modified_by = authActionSave()['by'];
            $floor->modified_by_type = authActionSave()['type'];
            $floor->save();

            return ResponseWithSuccessData($lang, $floor, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Floor::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $floor = Floor::find($request->id);
            if (!$floor) {
                return  RespondWithBadRequestNotExist();
            }
            $floor->deleted_by = authActionSave()['by'];
            $floor->deleted_by_type = authActionSave()['type'];
            $floor->save();

            $floor->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = Floor::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $branch = Floor::with('branches')->findOrFail($id);
            $branch->type_name = $branch->type == 1 ? $lang == 'en' ? 'in door' : 'داخلي' : ($branch->type == 2 ? $lang == 'en' ? 'outdoor' : 'خارجي' : ($branch->type == 3 ? $lang == 'en' ? 'both' : 'النوعين' : ''));
            $branch->smoking_name = $branch->smoking == 1 ? $lang == 'en' ? 'smoking' : 'مدخن' : ($branch->smoking == 2 ? $lang == 'en' ? 'non smoking' : 'غير مدخن' : ($branch->smoking == 3 ? $lang == 'en' ? 'both' : 'النوعين' : ''));
            return ResponseWithSuccessData($lang, $branch, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
