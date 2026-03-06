<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HR_Services\AbsenceSettingService;

class AbsenceSettingController extends Controller
{
    protected $service;

    public function __construct(AbsenceSettingService $service)
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
        return response()->json($this->service->store($request), 201);
    }

    public function update(Request $request, $id)
    {
        return response()->json($this->service->update($request, $id));
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }
}
