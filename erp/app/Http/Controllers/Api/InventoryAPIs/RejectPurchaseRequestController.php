<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\RejectPurchaseRequestService;
use Illuminate\Http\Request;

class RejectPurchaseRequestController extends Controller
{
    protected $rejectPurchaseRequestService;
    public function __construct(RejectPurchaseRequestService $rejectPurchaseRequestService)
    {
        $this->rejectPurchaseRequestService = $rejectPurchaseRequestService;
    }

    public function index(Request $request)
    {
        return $this->rejectPurchaseRequestService->index($request);
    }
    public function show(Request $request, $id)
    {
        return $this->rejectPurchaseRequestService->show($request, $id);
    }
    public function store(Request $request)
    {
        return $this->rejectPurchaseRequestService->store($request);
    }
    public function update(Request $request, $id)
    {
        return $this->rejectPurchaseRequestService->update($request, $id);
    }
    public function delete(Request $request, $id)
    {
        return $this->rejectPurchaseRequestService->delete($request, $id);
    }
}
