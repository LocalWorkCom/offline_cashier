<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Branch;
use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Services\HR_Services\DepartmentService;
use App\Http\Controllers\Controller;

class DepartmentController extends Controller
{
    protected $departmentService;
    protected $checkToken;


    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
        $this->checkToken = false;
    }

    public function index()
    {
        $departments = $this->departmentService->getAllDepartments()->get();
        $branches = Branch::all(); // Make sure to import the Branch model at the top
        return view('dashboard.department.index', compact('departments', 'branches'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'instructions_ar' => 'nullable|string',
            'instructions_en' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id', // Add this line
        ]);

        $this->departmentService->createDepartment($validatedData);
        return redirect()->route('departments.list')->with('success', 'Department created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'instructions_ar' => 'nullable|string',
            'instructions_en' => 'nullable|string',
            'branch_id' => 'nullable|exists:branches,id', // Add this line
        ]);

        $this->departmentService->updateDepartment($validatedData, $id);
        return redirect()->route('departments.list')->with('success', 'Department updated successfully!');
    }
    public function destroy($id)
    {
        $this->departmentService->deleteDepartment($id);
        return redirect()->route('departments.list')->with('success', 'Department deleted successfully!');
    }
    public function getSubDepartments($departmentId)
    {
        // Fetch sub-departments where the department's parent_id matches the selected department
        $subDepartments = Department::where('parent_id', $departmentId)->get(['id', 'name_ar']);

        // Return the sub-departments as a JSON response
        return response()->json($subDepartments);
    }
    public function getPositions($id)
    {
        $positions = Position::where('department_id', $id)->get(['id', 'name_ar']);
        return response()->json($positions);
    }
    public function getDepartmentsByBranch($branch_id)
    {



        $departments = Department::where('branch_id', $branch_id)
            ->get(['id', 'name_ar']);

        return response()->json($departments);
    }
}
