<?php


namespace App\Services\StoreServices;

use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoicesDetails;
use Illuminate\Support\Facades\Auth;

class PurchaseService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function getAllPurchases($checkToken)
    {

        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $purchases = PurchaseInvoice::with([
            'vendor',
            'store',
            'purchaseInvoicesDetails',
            'purchaseInvoicesDetails.category',
            'purchaseInvoicesDetails.product',
            'purchaseInvoicesDetails.unit'
        ])->get();

        return $purchases;
    }

    public function getPurchase($id)
    {
        $purchases = PurchaseInvoice::with([
            'vendor',
            'store',
            'purchaseInvoicesDetails',
            'purchaseInvoicesDetails.category',
            'purchaseInvoicesDetails.product',
            'purchaseInvoicesDetails.unit'
        ])->findOrFail($id);
        return $purchases;
    }

    public function createPurchase($data, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }
        $purchase = new PurchaseInvoice();
        $purchase->Date = $data['date'];
        $purchase->invoice_number = $data['invoice_number'];
        $purchase->vendor_id = $data['vendor_id'];
        $purchase->type = $data['type'];
        $purchase->store_id = $data['store_id'];
        $purchase->created_by = Auth::user()->id;
        $purchase->save();

        foreach ($data['products'] as $product) {
            $purchaseDetails = new PurchaseInvoicesDetails();
            $purchaseDetails->purchase_invoices_id = $purchase->id;
            $purchaseDetails->category_id = $product['category_id'];
            $purchaseDetails->product_id = $product['product_id'];
            $purchaseDetails->unit_id = $product['unit_id'];
            $purchaseDetails->price = $product['price'];
            $purchaseDetails->quantity = $product['quantity'];
            $purchaseDetails->save();
        }
    }

    public function updatePurchase($data, $id, $checkToken)
    {
        $lang = app()->getLocale();
        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $purchase = PurchaseInvoice::findOrFail($id);
        $purchase->Date = $data['date'] ?? $purchase->Date;
        $purchase->invoice_number = $data['invoice_number'];
        $purchase->vendor_id = $data['vendor_id'];
        $purchase->type = $data['type'];
        $purchase->store_id = $data['store_id'];
        $purchase->modified_by = Auth::user()->id;
        $purchase->updated_at = now();
        $purchase->save();

        // Process each product
        foreach ($data['products'] as $product) {
            $purchaseDetails = PurchaseInvoicesDetails::findOrFail($product['id']);
            $purchaseDetails->category_id = $product['category_id'];
            $purchaseDetails->product_id = $product['product_id'];
            $purchaseDetails->unit_id = $product['unit_id'];
            $purchaseDetails->price = $product['price'];
            $purchaseDetails->quantity = $product['quantity'];
            $purchaseDetails->save();
        }
    }

    public function deletePurchase($id, $checkToken)
    {
        $lang = app()->getLocale();

        if (!CheckToken() && $checkToken) {
            return RespondWithBadRequest($lang, 5);
        }

        $purchase = PurchaseInvoice::find($id);
        $purchase->deleted_by = Auth::user()->id;
        $purchase->save();
        $purchase->delete();
    }
}
