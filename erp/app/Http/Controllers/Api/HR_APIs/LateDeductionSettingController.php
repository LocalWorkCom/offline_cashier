<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HR_Services\LateDeductionSettingService;

class LateDeductionSettingController extends Controller
{
    protected $service;

    public function __construct(LateDeductionSettingService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return response()->json($this->service->index());
    }

    public function show($id)
    {
        return response()->json($this->service->show($id));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'deduction_mode'   => 'required|in:exact,partial,fixed,double',
            'partial_interval' => 'nullable|integer|min:1',
            'fixed_threshold'  => 'nullable|integer|min:1',
            'deduct_from'      => 'required|in:basic,total',
            'notify_employee'  => 'boolean',
        ]);

        return response()->json($this->service->store($validated), 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'deduction_mode'   => 'sometimes|in:exact,partial,fixed,double',
            'partial_interval' => 'nullable|integer|min:1',
            'fixed_threshold'  => 'nullable|integer|min:1',
            'deduct_from'      => 'sometimes|in:basic,total',
            'notify_employee'  => 'boolean',
        ]);

        return response()->json($this->service->update($id, $validated));
    }

    public function destroy($id)
    {
        $this->service->destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
