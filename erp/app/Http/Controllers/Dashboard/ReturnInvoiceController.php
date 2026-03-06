<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Services\ClientServices\InvoiceService;
use App\Services\ReturnInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReturnInvoiceController extends Controller
{
    protected $ReturnInvoiceService;
    protected $checkToken;

    protected $InvoiceService;

    public function __construct(ReturnInvoiceService $ReturnInvoiceService, InvoiceService $InvoiceService)
    {
        $this->ReturnInvoiceService = $ReturnInvoiceService;
        $this->InvoiceService = $InvoiceService;
    }

    public function index(Request $request)
    {
        $response = $this->ReturnInvoiceService->index($request)->get();
        // dd($response);
        return view('dashboard.returnInvoice.list', compact('response'));
    }
    public function show(Request $request, $id)
    {        
        $invoice = $this->ReturnInvoiceService->show($request, $id);
        // dd($invoice);
        return view('dashboard.returnInvoice.show', compact('invoice'));
    }
}
