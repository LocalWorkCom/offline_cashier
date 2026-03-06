<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeMachine;
use App\Services\cashierService;
use Illuminate\Http\Request;

class cashierMachineController extends Controller
{
    protected $cashierService;
    protected $checkToken;


    public function __construct(cashierService $cashierService)
    {
        $this->cashierService = $cashierService;
        $this->checkToken = false;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $response = $this->cashierService->index($request, $this->checkToken);

        $responseData = $response->original;

        $mashiens = $responseData['data'];
        $Branches = Branch::get();
        $employees = Employee::with('branch', 'machine')->where('flag', 'cashier')->whereDoesntHave('machine')->get();
        //        $employees = Employee::with('branch')->where('flag', 'cashier')->get();
        // dd($employees,$mashiens);
        return view('dashboard.cashierMashienes.index', compact('mashiens', 'Branches', 'employees'));
    }
    public function getEmployeesByBranch(Request $request)
    {
        $branchId = $request->branch_id;

        $employees = Employee::where('branch_id', $branchId)
            ->where('flag', 'cashier')
            ->whereDoesntHave('machine') // relationship should be defined in Employee model
            ->get(['id', 'first_name', 'last_name']);  // select only needed columns

        return response()->json(['data' => $employees]);
    }
    public function getEmployeesForEdit(Request $request)
    {
        $branchId = $request->branch_id;
        $machineId = $request->machine_id;

        // Cashiers assigned to this machine
        $assignedIds = EmployeeMachine::where('cashier_machine_id', $machineId)
            ->pluck('employee_id')
            ->toArray();

        $assignedCashiers = Employee::whereIn('id', $assignedIds)
            ->get(['id', 'first_name', 'last_name']);

        // Employees assigned to ANY machine
        $allAssignedIds = EmployeeMachine::pluck('employee_id')->toArray();

        // Unassigned cashiers: Not assigned to any machine at all, from this branch
        $unassignedCashiers = Employee::where('branch_id', $branchId)
            ->where('flag', 'cashier')
            ->whereNotIn('id', $allAssignedIds)
            ->get(['id', 'first_name', 'last_name']);

        return response()->json([
            'success' => true,
            'data' => [
                'assigned' => $assignedCashiers->map(function ($cashier) {
                    return [
                        'id' => $cashier->id,
                        'name' => $cashier->first_name . ' ' . $cashier->last_name
                    ];
                }),
                'unassigned' => $unassignedCashiers->map(function ($cashier) {
                    return [
                        'id' => $cashier->id,
                        'name' => $cashier->first_name . ' ' . $cashier->last_name
                    ];
                })
            ]
        ]);
    }
    public function getAssignedEmployees(Request $request)
    {
        $machineId = $request->machine_id;

        $machine = Machine::with('employees') // assuming the relation is `employees()`
                    ->find($machineId);

        if (!$machine) {
            return response()->json(['data' => []]);
        }

        $employees = $machine->employees->map(function ($employee) {
            return [
                'id' => $employee->id,
                'first_name' => $employee->first_name,
                'last_name' => $employee->last_name,
            ];
        });

        return response()->json(['data' => $employees]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //        dd($request->all());
        $response = $this->cashierService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('dashboard.cashierMachines.list')->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $response = $this->cashierService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('dashboard.cashierMachines.list')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $response = $this->cashierService->destroy($request, $id);
        $responseData = $response->original;
        // AJAX response for validation errors
        if (!$responseData['status']) {
            return response()->json([
                'status' => false,
                'message' => $responseData['message'],
                'errors' => $responseData['data'] ?? null
            ], 400);
        }

        // Success response
        return response()->json([
            'status' => true,
            'message' => $responseData['message']
        ]);
    }
}
