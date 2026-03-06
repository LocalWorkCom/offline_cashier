<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\ReasonPurchaseRequestService;
use Illuminate\Http\Request;

class ReasonPurchaseRequestController extends Controller
{
    protected $reasonPurchaseRequestService;
    protected $lang;
    public function __construct(ReasonPurchaseRequestService $reasonPurchaseRequestService)
    {
        $this->reasonPurchaseRequestService = $reasonPurchaseRequestService;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        $result = $this->reasonPurchaseRequestService->index($request);
        return $result;
    }
    public function show(Request $request, $id)
    {
        return $this->reasonPurchaseRequestService->show($request, $id);
    }
    public function store(Request $request)
    {
        return $this->reasonPurchaseRequestService->store($request);
    }
    public function update(Request $request, $id)
    {
        return $this->reasonPurchaseRequestService->update($request, $id);
    }
    public function delete(Request $request, $id)
    {
        return $this->reasonPurchaseRequestService->delete($request, $id);
    }
}
