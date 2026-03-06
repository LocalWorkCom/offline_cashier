<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\DiscrepancyReasonService;
use Illuminate\Http\Request;

class DiscrepancyReasonController extends Controller
{
    protected $DiscrepancyReasonService;
    protected $lang;
    public function __construct(DiscrepancyReasonService $DiscrepancyReasonService)
    {
        $this->DiscrepancyReasonService = $DiscrepancyReasonService;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        return $this->DiscrepancyReasonService->index($request);
    }
    public function show($id)
    {
        return $this->DiscrepancyReasonService->show($id);
    }
    public function store(Request $request)
    {
        return $this->DiscrepancyReasonService->store($request);
    }
    public function update(Request $request, $id)
    {
        return $this->DiscrepancyReasonService->update($request, $id);
    }
    public function delete(Request $request, $id)
    {
        return $this->DiscrepancyReasonService->delete($request, $id);
    }
}
