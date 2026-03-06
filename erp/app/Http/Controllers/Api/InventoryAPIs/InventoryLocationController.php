<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Services\Inventory_Services\InventoryLocationService;
use Illuminate\Http\Request;

class InventoryLocationController extends Controller
{
    protected $InventoryLocationService;
    protected $lang;
    public function __construct(InventoryLocationService $InventoryLocationService ,Request $request    )
    {
        $this->InventoryLocationService = $InventoryLocationService;
        $this->lang = $request->header('lang','ar');
    }

    public function index(Request $request)
    {
        return $this->InventoryLocationService->index($request);
    }
}
