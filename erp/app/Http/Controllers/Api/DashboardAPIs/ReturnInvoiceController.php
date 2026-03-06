<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Services\ClientServices\InvoiceService;
use App\Services\ReturnInvoiceService;
use Illuminate\Http\Request;

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
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $response = $this->ReturnInvoiceService->index($request);
        $responseData = paginateOrGetAll($response, $request, null, null);
        if(isset($responseData)) 
        {
            $responseData['data'] = collect($responseData['data'])->map(function($invoice) use($lang){
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
        app()->setLocale($lang);
        $query = Invoice::where('id', $id)->first();
        // dd($query);
        if ($query == null) {
            return respondError($lang === 'ar' ? 'لم يتم العثور علي الفاتوره المرتجعه.' : 'return invoice id not found', 404);
        }
        $invoice = $this->ReturnInvoiceService->show($request, $id);
        $invoice->status = __('einvoice.'.$invoice->status);
        $invoice->is_active = $invoice->is_active== 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');
        $invoice->make_type = __('order.'.$invoice->make_type);
        $invoice->orders->status = __('order.'.$invoice->orders->status);
        $invoice->orders->type = __('order.'.$invoice->orders->type);
        collect($invoice->invoiceDetails)->map(function ($detail) {
            $detail->status = __('einvoice.'.$detail->status);
            if($detail->type === "dish"){
                $name_dish = OrderDetail::with('dish')->where('id', $detail->details_id)->first();
                $detail->name_dish = $name_dish->dish->name;
            }else{
                $name_dish = OrderAddon::with('Addon.addons')->where('id', $detail->details_id)->first();
                $detail->name_dish = $name_dish->Addon->addons->name;
            }
            return $detail;
        });
        return ResponseWithSuccessData($lang, $invoice, 1);
    }
}
