<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\CashierMachine;
use App\Models\CashierSetting;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CashierMachineController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $branch_id = $request->input('branch_id');
        $user = auth('employee')->user();

        if ((!$user)) {
            return RespondWithBadRequest($lang, 4);
        }
        $query = CashierMachine::with('branches', 'employees')->withoutTrashed();
        if($branch_id){
            $query->where('branch_id', $request->branch_id);
        }
        $machines = paginateOrGetAll($query, $request, null);
        // $machines = $machines['data'] ?? $machines;
        $machinesData['data'] = $machines['data']->map(function ($machine) {
            return [
                'id'        => $machine->id,
                'name_ar'   => $machine->name_ar,
                'name_en'   => $machine->name_en,
                'branch'    => $machine->branches ? $machine->branches->name : null,
                'branch_id' => $machine->branch_id,
                'device_id' => $machine->device_id,
                'date'      => $machine->date,
                'employee' => $machine->employees ? $machine->employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                    ];
                }) : null,
            ];
        });
        $machinesData['meta'] = $machines['meta'];
        return ResponseWithSuccessDataPaginated($lang, $machinesData, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $user = auth('employee')->user();

        if ((!$user)) {
            return RespondWithBadRequest($lang, 4);
        }
        $machines = CashierMachine::with(['branches', 'employees'])->where('id', $id)->withoutTrashed()->get();
        if ($machines->isEmpty()) {
            return respondError($lang === 'ar' ? 'لم يتم العثور علي الماكينه.' : 'machine id not found', 404);
        }
        $machinesData = $machines->map(function ($machine) {
            return [
                'id'        => $machine->id,
                'name_ar'   => $machine->name_ar,
                'name_en'   => $machine->name_en,
                'branch'    => $machine->branches ? $machine->branches->name : null,
                'employee' => $machine->employees ? $machine->employees->map(function ($employee) {
                    return [
                        'id' => $employee->id,
                        'first_name' => $employee->first_name,
                        'last_name' => $employee->last_name,
                    ];
                }) : null,
                'device_id' => $machine->device_id,
                'date'      => $machine->date,
            ];
        });
        return ResponseWithSuccessData($lang, $machinesData, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $user = auth('employee')->user();

        if ((!$user)) {
            return RespondWithBadRequest($lang, 4);
        }

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
        $lang = $request->header('lang', 'ar');

        $user = auth('employee')->user();

        if ((!$user)) {
            return RespondWithBadRequest($lang, 4);
        }

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer|exists:branches,id',
            'name_ar' => 'required',
            'name_en' => 'required',
            'device_id' => 'required|unique:cashier_machines,device_id,' . $id, // Exclude current record
            'date' => 'required|date',
            'employees' => 'nullable|array',
            'employees.*' => 'integer|exists:employees,id',
        ]);
        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        DB::beginTransaction();

        $cashier_machine = CashierMachine::find($id);
        if (!$cashier_machine) {
            DB::rollBack();
            return respondError($lang === 'ar' ? 'لم يتم العثور علي الماكينه.' : 'machine id not found', 404);
        }

        $cashier_machine->branch_id = $request->branch_id;
        $cashier_machine->name_ar = $request->name_ar;
        $cashier_machine->name_en = $request->name_en;
        $cashier_machine->device_id = $request->device_id;
        $cashier_machine->date = $request->date;
        $cashier_machine->modified_by = authActionSave()['by'];
        $cashier_machine->modified_by_type = authActionSave()['type'];
        $cashier_machine->save();

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
            //         'created_by' => authActionSave()['by'],
            //         'created_by_type' => authActionSave()['type'],
            //     ]); 
            // }
        }
        DB::commit();
        return RespondWithSuccessRequest($lang, 1);
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $user = auth('employee')->user();

        if ((!$user)) {
            return RespondWithBadRequest($lang, 4);
        }

        DB::beginTransaction();

        $cashier_machine = CashierMachine::find($id);
        if (!$cashier_machine) {
            DB::rollBack();
            return respondError($lang === 'ar' ? 'لم يتم العثور علي الماكينه.' : 'machine id not found', 404);
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

        DB::commit();
        return RespondWithSuccessRequest($lang, 1);
    }
}
