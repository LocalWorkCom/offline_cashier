<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ClientServices\InvoiceService;
use Illuminate\Support\Facades\App;

use Illuminate\Http\Request;

class GetInvoiceController extends Controller
{
    protected $invoiceService;
    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;

    }

    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request)
    {
        return $this->invoiceService->makeInvoice($request->order_id, $request->order_type, $request->invoice_type, $request->quantities, $request->dish_size_id, $request->invoice_id, $request->order_details_ids, $request->order_addon_ids);
    }

    public function cancel(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $this->invoiceService->makeCancelRequest($request->invoice_id, $request->items, $request->request_type, $request->reason);
    }

    public function edit(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        return $this->invoiceService->editInvoice($request->order_id, $lang);
    }

    public function mergeInvoiceDetails(Request $request)
    {
        return $this->invoiceService->merge_invoice_details($request->invoice_id);
    }
}
