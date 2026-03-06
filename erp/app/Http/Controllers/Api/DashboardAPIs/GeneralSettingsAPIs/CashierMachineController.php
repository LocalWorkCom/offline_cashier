<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\CashierMachine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CashierMachineController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $cashier_machines = CashierMachine::with('branches')->get();
            return ResponseWithSuccessData($lang, $cashier_machines, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
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
                'device_id' => 'required',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $cashier_machine = new CashierMachine();
            $cashier_machine->branch_id = $request->branch_id;
            $cashier_machine->name_ar = $request->name_ar;
            $cashier_machine->name_en = $request->name_en;
            $cashier_machine->device_id = $request->device_id;
            $cashier_machine->date = $request->date;
            $cashier_machine->created_by = $user_id;
            $cashier_machine->save();

            return ResponseWithSuccessData($lang, $cashier_machine, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:cashier_machines,id',
                'branch_id' => 'required|integer|exists:branches,id',
                'name_ar' => 'required',
                'name_en' => 'required',
                'device_id' => 'required',
                'date' => 'required|date'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $cashier_machine = CashierMachine::findOrFail($request->id);
            $cashier_machine->branch_id = $request->branch_id;
            $cashier_machine->name_ar = $request->name_ar;
            $cashier_machine->name_en = $request->name_en;
            $cashier_machine->device_id = $request->device_id;
            $cashier_machine->date = $request->date;
            $cashier_machine->modified_by = $user_id;
            $cashier_machine->save();

            return ResponseWithSuccessData($lang, $cashier_machine, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $cashier_machine = CashierMachine::find($request->id);
            if (!$cashier_machine) {
                return  RespondWithBadRequestNotExist();
            }

            $cashier_machine->deleted_by = $user_id;
            $cashier_machine->save();

            $cashier_machine->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
