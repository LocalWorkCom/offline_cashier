<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use App\Models\FloorPartition;
use App\Models\EmployeeFloorPartition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class EmployeeFloorPartitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $employee_floor_partitions = EmployeeFloorPartition::with('floorPartitions', 'employees')->get();
            return ResponseWithSuccessData($lang, $employee_floor_partitions, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $employee_floor_partition = EmployeeFloorPartition::where('floor_partition_id', $request->floor_partition_id)->where('date', $request->date)->first();
            if($employee_floor_partition){
                return RespondWithBadRequestNotAvailable($lang, 9);
            }

            $user_id = Auth::guard('api')->user()->id;
            $employee_floor_partition = new EmployeeFloorPartition();
            $employee_floor_partition->floor_partition_id = $request->floor_partition_id;
            $employee_floor_partition->employee_id = $request->employee_id;
            $employee_floor_partition->date = $request->date;
            $employee_floor_partition->created_by = $user_id;
            $employee_floor_partition->save();

            return ResponseWithSuccessData($lang, $employee_floor_partition, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:employee_floor_partitions,id',
                'floor_partition_id' => 'required|integer|exists:floor_partitions,id',
                'employee_id' => 'required|integer|exists:employees,id',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $employee_floor_partition = EmployeeFloorPartition::findOrFail($request->id);
            $employee_floor_partition->floor_partition_id = $request->floor_partition_id;
            $employee_floor_partition->employee_id = $request->employee_id;
            $employee_floor_partition->date = $request->date;
            $employee_floor_partition->modified_by = $user_id;
            $employee_floor_partition->save();

            return ResponseWithSuccessData($lang, $employee_floor_partition, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $employee_floor_partition = EmployeeFloorPartition::find($request->id);
            if (!$employee_floor_partition) {
                return  RespondWithBadRequestNotExist();
            }

            $employee_floor_partition->deleted_by = $user_id;
            $employee_floor_partition->save();

            $employee_floor_partition->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
