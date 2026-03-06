<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\KitchenServices\AddonService;

class AddonController extends Controller
{
    protected $addonService;
    protected $checkToken;  // Set to true or false based on your need

    public function __construct(AddonService $addonService)
    {
        $this->addonService = $addonService;
        $this->checkToken = false;
    }

    public function index()
    {
        $addons = $this->addonService->index()->get();
        return view('dashboard.addons.index', compact('addons'));
    }

    public function show($id)
    {
        try {
            $addon = $this->addonService->show($id);
            return view('dashboard.addons.show', compact('addon'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.addons.index')->with('error', 'Failed to load addon details.');
        }
    }

    public function create()
    {
        $products = $this->addonService->getAllProductBrands();
        return view('dashboard.addons.create', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            // 'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'ingredients' => 'required|array',
            'ingredients.*.product_brand_id' => 'required|integer|exists:product_brands,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,png,jpeg|max:5000',
        ]);

        $this->addonService->store($data, $request->file('images'));
        return redirect()->route('dashboard.addons.index')->with('success', 'Addon created successfully.');
    }

    public function edit($id)
    {
        $addon = $this->addonService->show($id);
        $products = $this->addonService->getAllProductBrands();
        return view('dashboard.addons.edit', compact('addon', 'products'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            // 'price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'ingredients' => 'required|array',
            'ingredients.*.product_brand_id' => 'required|integer|exists:product_brands,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpg,png,jpeg|max:5000',
        ]);

        $this->addonService->update($id, $data, $request->file('images'));
        return redirect()->route('dashboard.addons.index')->with('success', 'Addon updated successfully.');
    }

    public function destroy(Request $request, $id)
    {
        $response = $this->addonService->delete($request, $id, $this->checkToken, true);
        $responseData = $response->original ?? [];
        $message = $responseData['message'] ?? __('recipes.An error occurred');

        if (isset($responseData['status']) && !$responseData['status']) {
            $validationErrors = $responseData['data'] ?? $message;
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        return redirect('dashboard/addons')->with('message', $message);
    }


    public function restore($id)
    {
        $this->addonService->restore($id);
        return redirect()->route('dashboard.addons.index')->with('success', 'Addon restored successfully.');
    }
}
