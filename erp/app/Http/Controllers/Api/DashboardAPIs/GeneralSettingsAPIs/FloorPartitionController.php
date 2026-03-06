<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Floor;
use Illuminate\Http\Request;
use App\Models\FloorPartition;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\If_;

class FloorPartitionController extends Controller
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

            $query = FloorPartition::with('tables');

            $flags = ['waiter', 'cashier', 'customer_service', 'branch manager'];
            // Add branch manager filter
            if (in_array($employee->flag, $flags)) {
                $branch_id = $employee->branch_id;
                if ($branch_id || $employee->hasRole('Branch_Manager')) {
                    $query->with('floors.branches')
                        ->whereHas('floors', function ($query) use ($branch_id) {
                            $query->where('branch_id', $branch_id);
                        });
                }
            } else {
                if ($request->filled('branch_id')) {
                    $branch_id = $request->branch_id;

                    $query->with('floors.branches')
                        ->whereHas('floors', function ($q) use ($branch_id) {
                            $q->where('branch_id', $branch_id);
                        });
                } else {
                    $query->with('floors.branches');
                }
            }

            // Use the helper function for pagination
            $result = paginateOrGetAll($query, $request, null);
            if ($result['data']) {

                $result['data']->transform(function ($item) use ($lang) {
                    $item->type_name = $item->type == 1 ? ($lang == 'en' ? 'in door' : 'داخلي') : ($lang == 'en' ? 'outdoor' : 'خارجي');
                    $item->smoking_name = $item->smoking == 1 ? ($lang == 'en' ? 'smoking' : 'مدخن') : ($lang == 'en' ? 'non smoking' : 'غير مدخن');
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
            $lang = $request->header('lang', 'en');

            // First validate basic requirements
            $validateData = Validator::make($request->all(), [
                'floor_id' => 'required|integer|exists:floors,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1|in:1,2',
                'smoking' => 'required|integer|min:1|in:1,2',
                'capacity' => 'required|integer|min:1'
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

            // Get authenticated user's branch ID
            $user = auth()->user();
            $userBranchId = $user->branch_id; // Adjust this based on your user model structure

            // Check if the floor belongs to the user's branch
            $floor = Floor::find($request->floor_id);

            if (!$floor || $floor->branch_id != $userBranchId) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['floor_id' => [$lang == 'en' ? 'The selected floor does not belong to your branch.' : 'الطابق المحدد لا ينتمي إلى فرعك.']],
                    'validation_type' => true
                ], 400);
            }

            // Create the floor partition
            $floor_partition = new FloorPartition();
            $floor_partition->floor_id = $request->floor_id;
            $floor_partition->name_ar = $request->name_ar;
            $floor_partition->name_en = $request->name_en;
            $floor_partition->type = $request->type;
            $floor_partition->smoking = $request->smoking;
            $floor_partition->capacity = $request->capacity;
            $floor_partition->created_by = authActionSave()['by'];
            $floor_partition->created_by_type = authActionSave()['type'];
            $floor_partition->save();

            return ResponseWithSuccessData($lang, $floor_partition, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = FloorPartition::where('id', $request->id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:floor_partitions,id', // Changed from 'floors,id' to 'floor_partitions,id'
                'floor_id' => 'required|integer|exists:floors,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'type' => 'required|integer|min:1|in:1,2',
                'smoking' => 'required|integer|min:1|in:1,2',
                'capacity' => 'required|integer|min:1'
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

            // Get authenticated user's branch ID
            $user = auth()->user();
            $userBranchId = $user->branch_id; // Adjust this based on your user model structure

            // Check if the new floor belongs to the user's branch
            $floor = Floor::find($request->floor_id);

            if (!$floor || $floor->branch_id != $userBranchId) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['floor_id' => [$lang == 'en' ? 'The selected floor does not belong to your branch.' : 'الطابق المحدد لا ينتمي إلى فرعك.']],
                    'validation_type' => true
                ], 400);
            }

            // Check if the existing floor partition's floor also belongs to the user's branch
            $existingFloorPartition = FloorPartition::find($request->id);
            $existingFloor = Floor::find($existingFloorPartition->floor_id);

            if (!$existingFloor || $existingFloor->branch_id != $userBranchId) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['id' => ['This floor partition does not belong to your branch.']],
                    'validation_type' => true
                ], 400);
            }

            // Update the floor partition
            $floor_partition = FloorPartition::findOrFail($request->id);
            $floor_partition->floor_id = $request->floor_id;
            $floor_partition->name_ar = $request->name_ar;
            $floor_partition->name_en = $request->name_en;
            $floor_partition->type = $request->type;
            $floor_partition->smoking = $request->smoking;
            $floor_partition->capacity = $request->capacity;
            $floor_partition->modified_by = authActionSave()['by'];
            $floor_partition->modified_by_type = authActionSave()['type'];
            $floor_partition->save();

            return ResponseWithSuccessData($lang, $floor_partition, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = FloorPartition::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }            // $user_id = Auth::guard('api')->user()->id;

            $floor_partition = FloorPartition::find($request->id);
            if (!$floor_partition) {
                return  RespondWithBadRequestNotExist();
            }

            $floor_partition->deleted_by = authActionSave()['by'];
            $floor_partition->deleted_by_type = authActionSave()['type'];
            $floor_partition->save();

            $floor_partition->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $exists = FloorPartition::where('id', $id)->exists();
            App::setLocale($lang);
            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            $branch = FloorPartition::with(['floors', 'floors.branches'])->findOrFail($id);
            $responseData = [
                'id' => $branch->id,
                'floor_id' => $branch->floor_id,
                'floor_name' => $branch->floors ? $branch->floors->name : null,
                'branch_id' => ($branch->floors && $branch->floors->branches) ? $branch->floors->branches->id : null,
                'branch_name' => ($branch->floors && $branch->floors->branches) ? $branch->floors->branches->name : null,
                'name_ar' => $branch->name_ar,
                'name_en' => $branch->name_en,
                'type' => $branch->type == 1 ? 'in door' : 'out door',
                'smoking' => $branch->smoking == 1 ? 'smoking' : 'no smoking',
                'capacity' => $branch->capacity,
            ];
            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
