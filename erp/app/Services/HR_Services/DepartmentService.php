<?php


namespace App\Services\HR_Services;

use App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getAllDepartments($request)
    {
        $departments = Department::with('branch', 'employees')->withCount('employees');
        if ($request->has('status') && $request->status !== null) {
            $departments->where('status', $request->status);
        }

        // // Filter by authenticated employee branch
        if ($request->filter_by_auth_branch == 1) {

            $employee = auth('employee')->user();

            if ($employee && $employee->branch_id) {
                $departments->where('branch_id', $employee->branch_id);
            }
        }
        $departments->orderByDesc('created_at')->orderByDesc('updated_at');

        return $departments;
    }
    public function getAllDepartmentsPurchase()
    {
        $departments = Department::query();

        return $departments;
    }


    public function getDepartment($id)
    {
        return Department::with(['branch', 'employees'])->findOrFail($id);
    }

    public function createDepartment($data, $module = null)
    {
        $created_by =  authActionSave()['by'];

        $department = new Department();
        $department->name_ar = $data['name_ar'];
        $department->name_en = $data['name_en'];
        $department->description_ar = $data['description_ar'] ?? null;
        $department->description_en = $data['description_en'] ?? null;
        $department->instructions_ar = $data['instructions_ar'] ?? null;
        $department->instructions_en = $data['instructions_en'] ?? null;
        $department->branch_id = $data['branch_id'] ?? null;
        $department->created_by = $created_by;
        $department->created_at = now();
        if ($module != null  && $module == 'procurement') {
            $department->status = $data['status'] == 1 ? 'active' : 'inactive';
        } else {
            $department->status = $data['status'] ?? 'active';
        }
        $department->save();
        return $department;
    }

    public function updateDepartment($data, $id, $module = null)
    {
        $lang = app()->getLocale();
        $modified_by  =  authActionSave()['by'];

        $department = Department::findOrFail($id);
        $department->name_ar = $data['name_ar'];
        $department->name_en = $data['name_en'];
        $department->description_ar = $data['description_ar'] ?? $department->description_ar;
        $department->description_en = $data['description_en'] ?? $department->description_en;
        $department->instructions_ar = $data['instructions_ar'] ?? $department->instructions_ar;
        $department->instructions_en = $data['instructions_en'] ?? $department->instructions_en;
        $department->branch_id = $data['branch_id'] ?? null; // Add this line
        $department->modified_by  = $modified_by;
        $department->updated_at = now();
        if ($module != null  && $module == 'procurement') {
            $department->status = $data['status'] == 'active' ? 1 : 0;
        } else {
            $department->status = $data['status'] ?? 'active';
        }
        $department->save();
        return $department;
    }

    public function deleteDepartment($id)
    {
        $deleted_by =  authActionSave()['by'];
        $department = Department::findOrFail($id);
        $department->deleted_by = $deleted_by;
        $department->status = 'inactive';
        $department->save();
        $department->delete();
    }
    public function getAllChildDepartmentIds($parentId)
    {
        $ids = Department::where('parent_id', $parentId)->pluck('id')->toArray();
        foreach ($ids as $id) {
            $ids = array_merge($ids, $this->getAllChildDepartmentIds($id));
        }
        return $ids;
    }
    public function getAllParentDepartmentIds($childId)
    {
        $ids = [];
        $parent = Department::where('id', $childId)->first();

        if ($parent && $parent->parent_id) {
            $ids[] = $parent->parent_id;
            $ids = array_merge($ids, $this->getAllParentDepartmentIds($parent->parent_id));
        }

        return $ids;
    }
}
