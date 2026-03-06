<?php


namespace App\Services\Inventory_Services;

use App\Models\InventoryLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryLocationService
{

    public function index(Request $request)
    {
        $lang = app()->getLocale();

        $reasons = InventoryLocation::get();

        return ResponseWithSuccessData($lang, $reasons, 1);
    }
}
