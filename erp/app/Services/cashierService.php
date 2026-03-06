<?php

namespace App\Services;

use App\Models\CashierMachine;
use App\Models\CashierSetting;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class cashierService
{

    public function index(Request $request, $checkToken)
    {

        $lang = $request->header('lang', 'ar');
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $machines = CashierMachine::with('employees')->withoutTrashed()->get();
        return ResponseWithSuccessData($lang, $machines, 1);
    }
    public function store(Request $request, $id)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer|exists:branches,id',
            'name_ar' => 'required',
            'name_en' => 'required',
            'device_id' => 'required|unique:cashier_machines,device_id',
            'date' => 'required|date',
            'employees' => 'nullable|array',
            'employees.*' => 'integer|exists:employees,id',
        ]);
        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        DB::beginTransaction();

        $user_id = auth('admin')->user()->id;
        $cashier_machine = new CashierMachine();
        $cashier_machine->branch_id = $request->branch_id;
        $cashier_machine->name_ar = $request->name_ar;
        $cashier_machine->name_en = $request->name_en;
        $cashier_machine->device_id = $request->device_id;
        $cashier_machine->date = $request->date;
        $cashier_machine->created_by = authActionSave()['by'];
        $cashier_machine->created_by_type = authActionSave()['type'];
        $cashier_machine->save();

        if ($request->has('employees') && is_array($request->employees)) {
            foreach ($request->employees as $employee_id) {
                DB::table('employee_machines')->insert([
                    'cashier_machine_id' => $cashier_machine->id,
                    'employee_id' => $employee_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
            }
        }
        DB::commit();

        return RespondWithSuccessRequest($lang, 1);
    }
    public function update(Request $request, $id)
    {
        $lang =  $request->header('lang', 'en');
        $validateData = Validator::make($request->all(), [
            // 'id' => 'required|exists:cashier_machines,id',
            'branch_id' => 'required|integer|exists:branches,id',
            'name_ar' => 'required',
            'name_en' => 'required',
            'device_id' => 'required|unique:cashier_machines,device_id,' . $id, // Exclude current record
            'date' => 'required|date',
            'employees' => 'nullable|array',
            'employees.*' => 'integer|exists:employees,id',
        ]);

        if ($validateData->fails()) {
            return RespondWithBadRequestWithData($validateData->errors());
        }

        DB::beginTransaction();

        $user_id = auth('admin')->user()->id;
        $cashier_machine = CashierMachine::findOrFail($id);
        $cashier_machine->branch_id = $request->branch_id;
        $cashier_machine->name_ar = $request->name_ar;
        $cashier_machine->name_en = $request->name_en;
        $cashier_machine->device_id = $request->device_id;
        $cashier_machine->date = $request->date;
        $cashier_machine->modified_by = authActionSave()['by'];
        $cashier_machine->modified_by_type = authActionSave()['type'];
        $cashier_machine->save();
        DB::table('employee_machines')->where('cashier_machine_id', $id)->delete();

        if ($request->has('employees') && is_array($request->employees)) {

            DB::table('employee_machines')->where('cashier_machine_id', $id)->delete();

            foreach ($request->employees as $employee_id) {
                DB::table('employee_machines')->insert([
                    'cashier_machine_id' => $id,
                    'employee_id' => $employee_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'modified_by' => authActionSave()['by'],
                    'modified_by_type' => authActionSave()['type']
                ]);
            }
        }
        DB::commit();

        // Return success response
        return RespondWithSuccessRequest($lang, 1);
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');
        $user_id = auth('admin')->user()->id;

        $cashier_machine = CashierMachine::find($id); // Use $id instead of $request->id

        if (!$cashier_machine) {
            return RespondWithBadRequestNotExist();
        }

        $cashier_machine_order = Order::where('cashier_machine_id', $id)->exists();
        $cashier_machine_officer = CashierSetting::where('pos_id', $id)->exists();

        if ($cashier_machine_officer || $cashier_machine_order) {
            return RespondWithBadRequestNotExist();
        }

        $cashier_machine->deleted_by = authActionSave()['by'];
        $cashier_machine->deleted_by_type = authActionSave()['type'];
        $cashier_machine->save();
        $cashier_machine->delete();

        return ResponseWithSuccessData($lang, null, 1);
    }


    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');

            $cashier_machine = CashierMachine::find($request->id);

            return ResponseWithSuccessData($lang, $cashier_machine, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
