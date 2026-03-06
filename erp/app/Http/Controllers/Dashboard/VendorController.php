<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Vendor;
use App\Services\StoreServices\VendorService;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    protected $vendorService;
    protected $checkToken;


    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
        $this->checkToken = false;
    }

    public function index()
    {
        $vendors = $this->vendorService->getAllvendors($this->checkToken);
        return view('dashboard.vendors.index', compact('vendors'));
    }
    public function show($id)
    {
        $vendor = $this->vendorService->getVendor($id);
        return view('dashboard.vendors.show', compact('vendor'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('dashboard.vendors.create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'country_id' => 'required|integer|exists:countries,id',
        ]);

        $this->vendorService->createVendor($validatedData, $this->checkToken);
        return redirect()->route('vendors.index')->with('success', 'vendor created successfully!');
    }
    public function edit($id)
    {
        $vendor = Vendor::with('country')->findOrFail($id);
        $countries = Country::all();
        return view('dashboard.vendors.edit', compact('countries', 'vendor'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_en' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'country_id' => 'nullable|integer|exists:countries,id',
        ]);

        $this->vendorService->updateVendor($validatedData, $id, $this->checkToken);
        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully!');
    }
    public function destroy($id)
    {
        $this->vendorService->deleteVendor($id, $this->checkToken);
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully!');
    }
}
