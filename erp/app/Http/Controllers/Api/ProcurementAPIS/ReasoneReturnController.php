<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Http\Controllers\Controller;
use App\Services\ProcurementServices\ReturnReasonService;
use Illuminate\Http\Request;

class ReasoneReturnController extends Controller
{
    protected $returnReasonService;
    public function __construct(ReturnReasonService $returnReasonService)
    {
        $this->returnReasonService = $returnReasonService;
    }

    public function index(Request $request)
    {
        return $this->returnReasonService->index($request);
    }
    public function show(Request $request, $id)
    {
        return $this->returnReasonService->show($request, $id);
    }
    public function store(Request $request)
    {
        return $this->returnReasonService->store($request);
    }
    public function update(Request $request, $id)
    {
        return $this->returnReasonService->update($request, $id);
    }
    public function delete(Request $request, $id)
    {
        return $this->returnReasonService->delete($request, $id);
    }
}
