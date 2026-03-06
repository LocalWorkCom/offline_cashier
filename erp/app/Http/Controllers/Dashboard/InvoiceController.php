<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\ClientServices\InvoiceService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $checkToken;

    protected $InvoiceService;

    public function __construct(InvoiceService $InvoiceService)
    {
        $this->InvoiceService = $InvoiceService;
    }

    public function index(Request $request)
    {
        $response = $this->InvoiceService->index($request)->get();
        return view('dashboard.invoice.list', compact('response'));
    }
    public function show(Request $request, $id)
    {
        $query = $this->InvoiceService->show($request, $id);
        $invoice = $query['query'];
        $paymentmethod = $query['paymentmethod'];
        return view('dashboard.invoice.show', compact('invoice', 'paymentmethod'));
    }
}
