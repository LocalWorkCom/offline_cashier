<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\AuditTypeService;
use Illuminate\Http\Request;

class AuditTypeController extends Controller
{
    protected $AuditTypeService;
    protected $lang;
    public function __construct(AuditTypeService $AuditTypeService ,Request $request    )
    {
        $this->AuditTypeService = $AuditTypeService;
        $this->lang = $request->header('lang','ar');
    }

    public function index(Request $request)
    {
        return $this->AuditTypeService->index($request);
    }
    // public function show($id)
    // {
    //     return $this->AuditTypeService->show($id);
    // }
    // public function store(Request $request)
    // {
    //     return $this->AuditTypeService->store($request);
    // }
    // public function update(Request $request, $id)
    // {
    //     return $this->AuditTypeService->update($request, $id);
    // }
    // public function delete(Request $request, $id)
    // {
    //     return $this->AuditTypeService->delete($request, $id);
    // }
}
