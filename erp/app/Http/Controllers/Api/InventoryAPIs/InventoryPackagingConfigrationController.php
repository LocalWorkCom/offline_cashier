<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\InventoryPackagingConfiguration;
use App\Models\Product;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class InventoryPackagingConfigrationController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        return ResponseWithSuccessData($lang, InventoryPackagingConfiguration::all(), 1);

    }

    public function getProducts(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $data = Product::with('Category')->where('category_id', 1)->get();
        return ResponseWithSuccessData($lang, $data, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
            'order_type' => 'required|string',
        ]);

        $record = InventoryPackagingConfiguration::create($validated);

        return ResponseWithSuccessData($lang, $record, 1);
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'en');

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
            'order_type' => 'required|string',
        ]);

        $record = InventoryPackagingConfiguration::findOrFail($id);
        $record->update($validated);
        // $activities = Activity::all();

        return ResponseWithSuccessData($lang, $record, 1);
    }
}
