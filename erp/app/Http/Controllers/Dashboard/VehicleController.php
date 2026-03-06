<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Vehicle;
use App\Services\SettingsServices\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
       protected $VehicleService;

    public function __construct(VehicleService $VehicleService)
    {
        $this->VehicleService = $VehicleService;
    }

    public function index()
    {
        $vehicles = $this->VehicleService->index()->get();
        $employees = Employee::query();

        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $vehicles->whereHas('employee.branch', function ($q) use ($branch_id) {
                    $q->where('id', $branch_id);
                });
                $employees->where('branch_id', $branch_id);
            }
        }

        $vehicles = $vehicles->map(function ($vehicle) {
            $vehicle->employee_id = $vehicle->employee?->id;
            $vehicle->employee_name = $vehicle->employee?->name;
            return $vehicle;
        });

        $employees = $employees->get();

        return view('dashboard.vehicles.index', compact('vehicles', 'employees'));
    }

    public function store(Request $request)
    {
       
         $validator = Validator::make($request->all(), [
            'vehicle_type' => 'required|exists:vehicle_settings,id',
            'license' => 'required|string|unique:vehicles,license',
            'employee' => [
                'nullable',
                Rule::exists('employees', 'id')->where(function ($query) {
                    $query->whereIn('flag', ['driver', 'employee']);
                }),
            ],
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->VehicleService->store($request->all());
        return redirect()->back()->with('success', 'Vehicle added successfully.');
    }

    public function update(Request $request, $id)
    {
        
        $request->validate([
            'vehicle_type' => 'required|exists:vehicle_settings,id',
            'license' => [
                'required',
                'string',
                Rule::unique('vehicles', 'license')->ignore($id),
            ],
            'employee' => 'sometimes|exists:employees,id',
        ]);
        $this->VehicleService->update($id, $request->all());

        return redirect()->back()->with('success', 'Vehicle updated successfully.');
    }

    public function destroy($id)
    {
        $this->VehicleService->delete($id);

        return response()->json([
            'status' => true,
            'message' => 'Vehicle deleted successfully.',
        ]);
    }
}
