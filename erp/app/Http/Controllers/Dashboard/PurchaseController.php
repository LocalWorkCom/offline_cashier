<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Country;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\Store;
use App\Models\Unit;
use App\Models\Vendor;
use App\Services\StoreServices\PurchaseService;
use Illuminate\Http\Request;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PurchaseController extends Controller
{
    protected $purchaseService;
    protected $checkToken;


    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
        $this->checkToken = false;
    }

    public function index()
    {
        $purchases = $this->purchaseService->getAllPurchases($this->checkToken);
        return view('dashboard.purchases.index', compact('purchases'));
    }
    public function show($id)
    {
        $purchase = $this->purchaseService->getPurchase($id);
        return view('dashboard.purchases.show', compact('purchase'));
    }

    public function create()
    {
        $vendors = Vendor::all();
        $stores = Store::all();
        $categories = Category::all();
        $products = Product::all();
        $units = Unit::all();

        return view(
            'dashboard.purchases.create',
            compact('vendors', 'categories', 'products', 'units', 'stores')
        );
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'date' => 'required|date',
            'invoice_number' => 'required|string',
            'vendor_id' => 'required|exists:vendors,id',
            'type' => 'required|in:0,1',
            'store_id' => 'required|exists:stores,id',
            'products' => 'required|array|min:1',
            'products.*.category_id' => 'required|exists:categories,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.unit_id' => 'required|exists:units,id',
            'products.*.price' => 'required|numeric',
            'products.*.quantity' => 'required|numeric',
        ]);

        $this->purchaseService->createPurchase($validatedData, $this->checkToken);
        return redirect()->route('purchases.index')->with('success', 'Purchase created successfully!');
    }
    public function edit($id)
    {

        $vendors = Vendor::all();
        $stores = Store::all();
        $categories = Category::all();
        $products = Product::all();
        $units = Unit::all();


        $purchase = PurchaseInvoice::with(
            'vendor',
            'store',
            'purchaseInvoicesDetails'
        )->findOrFail($id);

        return view(
            'dashboard.purchases.edit',
            compact('vendors', 'categories', 'products', 'units', 'stores', 'purchase')
        );
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'date' => 'nullable|date',
            'invoice_number' => 'nullable|string',
            'vendor_id' => 'nullable|exists:vendors,id',
            'type' => 'nullable|in:0,1',
            'store_id' => 'nullable|exists:stores,id',
            'products' => 'nullable|array|min:1',
            'products.*.id' => 'nullable|exists:purchase_invoices_details,id',
            'products.*.category_id' => 'nullable|exists:categories,id',
            'products.*.product_id' => 'nullable|exists:products,id',
            'products.*.unit_id' => 'nullable|exists:units,id',
            'products.*.price' => 'nullable|numeric',
            'products.*.quantity' => 'nullable|numeric',
        ]);

        $this->purchaseService->updatePurchase($validatedData, $id, $this->checkToken);
        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
    }
    public function destroy($id)
    {
        $this->purchaseService->deletePurchase($id, $this->checkToken);
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
    }

    public function showInvoice($id)
    {

        $vendors = Vendor::all();
        $stores = Store::all();
        $categories = Category::all();
        $products = Product::all();
        $units = Unit::all();


        $purchase = PurchaseInvoice::with(
            'vendor',
            'store',
            'purchaseInvoicesDetails'
        )->findOrFail($id);

        return view(
            'dashboard.purchases.invoice',
            compact('vendors', 'categories', 'products', 'units', 'stores', 'purchase')
        );
    }

    public function print($id)
    {
        $purchase = PurchaseInvoice::with('vendor', 'purchaseInvoicesDetails.product')->findOrFail($id);
        return view('dashboard.purchases.print', compact('purchase'));
    }
}
