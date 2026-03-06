<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
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
        $lang = $request->header('lang', 'ar');
        $response = $this->InvoiceService->index($request);
        $responseData = paginateOrGetAll($response, $request, null, null);
        if(isset($responseData)) 
        {
            $responseData['data'] = collect($responseData['data'])->map(function($invoice)use($lang){
                $invoice->status = __('einvoice.'.$invoice->status);
                $invoice->is_active = $invoice->is_active== 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');
                $invoice->make_type = __('order.'.$invoice->make_type);
                $invoice->orders->status = __('order.'.$invoice->orders->status);
                $invoice->orders->type = __('order.'.$invoice->orders->type);
                collect($invoice->invoiceDetails)->map(function ($detail) {
                    $detail->status = __('einvoice.'.$detail->status);
                    return $detail;
                });
                return $invoice;
            });
        }
        return ResponseWithSuccessDataPaginated($lang, $responseData, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $query = Invoice::where('id', $id)->first();
        if ($query == null) {
            return respondError($lang === 'ar' ? 'لم يتم العثور علي الفاتوره .' : ' invoice id not found', 404);
        }
        $query = $this->InvoiceService->show($request, $id, 1);
        $invoice = $query['query'];
        $paymentmethod = $query['paymentmethod'];
        $invoice->status = __('einvoice.'.$invoice->status);
        $invoice->is_active = $invoice->is_active== 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');
        $invoice->make_type = __('order.'.$invoice->make_type);
        $invoice->orders->status = __('order.'.$invoice->orders->status);
        $invoice->orders->type = __('order.'.$invoice->orders->type);

        collect($invoice->invoiceDetails)->map(function ($detail) {
            $detail->status = __('einvoice.'.$detail->status);
            return $detail;
        });

        $response = [
            'invoice' => $invoice,
            'paymentmethod' => $paymentmethod,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }
}
