<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Floor;
use App\Models\Position;
use App\Services\HR_Services\PositionService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    protected $positionService;
    protected $checkToken;


    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
        $this->checkToken = false;
    }

    public function index()
    {
        $positions = $this->positionService->getAllPositions()->get();
        $departments = Department::get();
        return view('dashboard.position.index', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'department_id' => 'required|integer|exists:departments,id',
            'parent_id' => 'nullable|integer|exists:positions,id',
            'name_ar' => 'required|string',
            'name_en' => 'required|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
        ]);

        $this->positionService->createPosition($validatedData);
        return redirect()->route('positions.index')->with('success', 'Position created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'department_id' => 'nullable|integer|exists:departments,id',
            'parent_id' => 'nullable|integer|exists:positions,id',
            'name_ar' => 'nullable|string',
            'name_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
        ]);

        $this->positionService->updatePosition($validatedData, $id, $this->checkToken);
        return redirect()->route('positions.index')->with('success', 'Position updated successfully!');
    }
    public function destroy($id)
    {
        $this->positionService->deletePosition($id, $this->checkToken);
        return redirect()->route('positions.index')->with('success', 'Position deleted successfully!');
    }
}
