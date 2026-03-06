<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderWaste;
use App\Models\ReturnInvoiceRequest;
use App\Services\ClientServices\InvoiceService;
use App\Services\ReturnInvoiceRequestService;
use Illuminate\Http\Request;

class OrderReturnInvoiceRequestController extends Controller
{
    protected $ReturnInvoiceRequestService;
    protected $checkToken;

    protected $InvoiceService;

    public function __construct(ReturnInvoiceRequestService $ReturnInvoiceRequestService, InvoiceService $InvoiceService)
    {
        $this->ReturnInvoiceRequestService = $ReturnInvoiceRequestService;
        $this->InvoiceService = $InvoiceService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        // $user = auth('employee')->user();

        // if ((!$user) || ($user->flag != 'cashier')) {
        //     return RespondWithBadRequest($lang, 4);
        // }
        $response = $this->ReturnInvoiceRequestService->index($request, 1);
        // return $response;

        $data = json_decode($response->getContent(), true);
        // dd($data);

        // return $data['meta'];
        $returnInvoices = $data['data'] ?? [];
        // if(isset($response)) 
        // {
        //     $response['data'] = collect($response['data'])->map(function($invoice){
        //         // $invoice->status = __('einvoice.'.$invoice->status);
        //         // $invoice->make_type = __('order.'.$invoice->make_type);
        //         $invoice->invoice->status = __('order.'.$invoice->invoice->status);
        //         // $invoice->orders->type = __('order.'.$invoice->orders->type);
        //         // collect($invoice->invoiceDetails)->map(function ($detail) {
        //         //     $detail->status = __('einvoice.'.$detail->status);
        //         //     return $detail;
        //         // });
        //         return $invoice;
        //     });
        // }
        $namesDishAndAddon = $data['namesDishAndAddon'] ?? [];
        // dd($namesDishAndAddon, $returnInvoices);
        $returnInvoices = collect($returnInvoices)->map(function ($item, $index) use ($lang, $namesDishAndAddon) {
            $item['status'] = __('order.'.$item['status']);
            $item['request_type']= __(key: 'einvoice.'.$item['request_type']);
            $item['invoice']['namesDishAndAddon'] = $namesDishAndAddon[$index] ?? [];
            $item['invoice']['is_active'] = $item['invoice']['status'] == 1 ? ($lang === 'ar' ? 'نشط' : 'Active') : ($lang === 'ar' ? 'غير نشط' : 'Inactive');
            return (object) $item;
        });
        $data = [

            'data' => $returnInvoices,
            'meta' => $data['meta'] ?? [],
        ];
        return ResponseWithSuccessDataPaginated($lang, $data, 1);
        // return response()->json([
        //     'success' => true,
        //     'returnInvoices' => $returnInvoices,
        //     'namesDishAndAddon' => $namesDishAndAddon,
        // ]);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $response = $this->ReturnInvoiceRequestService->show($request, $id, 1)->getData();


         if($response->data == null) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $data = [
            'returnInvoices' => $response->data ?? null,
            'dataOfDishes' => $response->dataOfDishes ?? [],
            'dataOfAddons' => $response->dataOfAddons ?? [],
            'orders' => $response->statusOrder ?? null,
            'orderDetails2' => $response->order_details2 ?? [],
        ];
        return ResponseWithSuccessData($lang, $data, 1);
        // return response()->json([
        //     'success' => true,
        //     'returnInvoices' => $response->data ?? null,
        //     'dataOfDishes' => $response->dataOfDishes ?? [],
        //     'dataOfAddons' => $response->dataOfAddons ?? [],
        //     'orders' => $response->statusOrder ?? null,
        //     'orderDetails2' => $response->order_details2 ?? [],
        // ]);
    }
    public function showInvoice(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $query = Invoice::where('id', $id)->first();
        if(!$query)
        {
             return respondError(__('branch_menu_category.not_found'), 404);
        }
        $invoice = $this->InvoiceService->show($request, $id, 1);

        return ResponseWithSuccessData($lang, $invoice, 1);

        // return view('dashboard.invoice.show', compact('invoice'));
    }

    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $employee = auth('employee')->user();
        if ((!$employee)) {
            return RespondWithBadRequest($lang, 4);
        }

        $invoice = ReturnInvoiceRequest::find($id);
        if (!$invoice) {
             return respondError(__('branch_menu_category.not_found'), 404);
        }

        if ($request->items == null) {

            $items = [];
            $datainvoice = json_decode($invoice->invoice_details_ids);
            foreach ($datainvoice as $id) {
                $invoiceDetails = InvoiceDetails::where('id', $id->invoice_detail_id)->select('type', 'invoice_id', 'details_id')->first();
                $invoice2 = Invoice::where('id', $invoiceDetails->invoice_id)->first();

                if ($invoiceDetails->type == 'dish') {
                    $orderDetails = OrderDetail::where('order_id', $invoice2->order_id)->with('dish')->first();
                    $orderwaste = OrderWaste::where('order_detail_id', $orderDetails->id)->first();
                    $items[] = [
                        'invoice_detail_id' => $id->invoice_detail_id,
                        'type' => $orderwaste?->type ?? 'not_waste',
                        'quantity' => $id->quantity,
                    ];
                } else {
                    $orderDetailsaddon = OrderAddon::where('id', $invoiceDetails->details_id)->with('Addon.addons')->first();
                    $orderwaste = OrderWaste::where('order_detail_id', $orderDetailsaddon->id)->first();
                    $items[] = [
                        'invoice_detail_id' => $id->invoice_detail_id,
                        'type' => $orderwaste?->type ?? 'not_waste',
                        'quantity' => $id->quantity,
                    ];
                }
            }
        }


        $data = [
            'request_id' => $invoice->id,
            'status' => $request->status,
            'reason' => $request->reject_resone,
            'items' => $request->items ?? $items,
        ];
        // dd($data);
        $fakeRequest = new Request();

        $fakeRequest->replace($data);
//  dd($fakeRequest);

        $result = $this->InvoiceService->changeRequestStatus($fakeRequest);
        // dd($result);
        return ResponseWithSuccessData($lang, $result, 1);
        // if (is_object($result) && property_exists($result, 'original')) {
        //     $responseData = $result->original;
        // } else {
        //     $responseData = $result;
        // }

        // if (isset($responseData['status']) && $responseData['status'] == false) {

        //     return response()->json([
        //         'status' => false,
        //         'errors' => $responseData['errorData'] ?? ['خطأ غير معروف'],
        //     ], 422);
        // }
        // $responseData = $result->original;
        // if ($request->status === 'reject') {
        // }
        // if ($request->status === 'accept') {
        //     if (isset($result['status']) && $result['status'] == false) {
        //         return response()->json([
        //             'status' => false,
        //             'errors' => $result['errorData'],
        //         ], 422);
        //     }
        // }
        // return response()->json([
        //     'status' => true,
        //     'message' => __('einvoice.status_updated_successfully'),
        // ]);
    }
}
