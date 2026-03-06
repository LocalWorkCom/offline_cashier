<?php

namespace App\Http\Controllers\Dashboard;

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
use Illuminate\Support\Facades\Log;

class ReturnInvoiceRequestController extends Controller
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
        $response = $this->ReturnInvoiceRequestService->index($request);
        $data = json_decode($response->getContent(), true);

        $returnInvoices = $data['data'] ?? [];
        $namesDishAndAddon = $data['namesDishAndAddon'] ?? [];
        // dd($namesDishAndAddon);
        // dd($namesDishAndAddon, $returnInvoices);
        $returnInvoices = collect($returnInvoices)->map(function ($item) {
            return (object) $item;
        });
        return view('dashboard.retuenInvoiceRequest.list', compact('returnInvoices', 'namesDishAndAddon'));
    }
    public function show(Request $request, $id)
    {
        $response = $this->ReturnInvoiceRequestService->show($request, $id)->getData();
        $returnInvoices = $response->data ?? null;
        $dataOfDishes = $response->dataOfDishes ?? [];
        $dataOfAddons = $response->dataOfAddons ?? [];
        $orders = $response->statusOrder ?? null;
        $invoicerefund = $response->invoicerefund ?? null;
        $orderDetails2 = $response->order_details2 ?? [];
        // dd($orderDetails2);
        return view('dashboard.retuenInvoiceRequest.show', compact(
            'returnInvoices',
            'dataOfDishes',
            'dataOfAddons',
            'orders',
            'invoicerefund',
            'orderDetails2'
        ));
    }

    // public function update(Request $request, $id)
    // {
    //     $invoice = ReturnInvoiceRequest::findOrFail($id);
    //     if ($request->items == null) {
    //         $items = [];
    //         foreach (json_decode($invoice->invoice_details_ids) as $id) {
    //             $invoiceDetails = InvoiceDetails::where('id', $id)->select('type', 'invoice_id', 'details_id')->first();
    //             $invoice2 = Invoice::where('id', $invoiceDetails->invoice_id)->first();
    //             if ($invoiceDetails->type == 'dish') {
    //                 $orderDetails = OrderDetail::where('order_id', $invoice2->order_id)->with('dish')->first();
    //                 $items[] = [
    //                     'invoice_detail_id' => $id,
    //                     'type' => $orderDetails->waste ?? 'N/A',
    //                 ];
    //             } else {
    //                 $orderDetailsaddon = OrderAddon::where('id', $invoiceDetails->details_id)->with('Addon.addons')->first();
    //                 $items[] = [
    //                     'invoice_detail_id' => $id,
    //                     'type' => $orderDetailsaddon->waste ?? 'N/A',
    //                 ];
    //             }
    //         }
    //     }
    //     $data = [
    //         'request_id' => $invoice->id,
    //         // 'status' => $request->status,
    //         'reason' => $request->reject_resone,
    //         'items' => $request->items ?? $items,
    //     ];
    //     $fakeRequest = new Request();
    //     $fakeRequest->replace($data);
    //     $result = $this->InvoiceService->changeRequestStatus($fakeRequest);
    //     return response()->json(['message' => 'Status updated successfully']);
    // }

    public function update(Request $request, $id)
    {
        $invoice = ReturnInvoiceRequest::findOrFail($id);
// dd($request->all());
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
        $fakeRequest = new Request();
        $fakeRequest->replace($data);
        $result = $this->InvoiceService->changeRequestStatus($fakeRequest);

        if (is_object($result) && property_exists($result, 'original')) {
            $responseData = $result->original;
        } else {
            $responseData = $result;
        }

        if (isset($responseData['status']) && $responseData['status'] == false) {

            return response()->json([
                'status' => false,
                'errors' => $responseData['errorData'] ?? ['خطأ غير معروف'],
            ], 422);
        }
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
        return response()->json([
            'status' => true,
            'message' => __('einvoice.status_updated_successfully'),
        ]);
    }


    // public function updateWaste(Request $request, $id)
    // {
    //     $invoice = ReturnInvoiceRequest::findOrFail($request->invoiceId);
    //     $items = [];
    //     foreach ($invoice->invoice_details_ids as $id2) {
    //         $invoiceDetails = InvoiceDetails::where('id', $id2)->select('type', 'invoice_id', 'details_id')->first();
    //         $invoice = Invoice::where('id', $invoiceDetails->invoice_id)->first();
    //         if ($invoiceDetails->type == 'dish') {
    //             $orderDetails = OrderDetail::where('order_id', $invoice->order_id)->with('dish')->first();
    //             $items[] = [
    //                 'invoice_detail_id' => $id2,
    //                 'type' => $request->waste ?? 'N/A',
    //             ];
    //         } else {
    //             $orderDetailsaddon = OrderAddon::where('order_details_id', $invoiceDetails->details_id)->with('Addon.addons')->first();
    //             $items[] = [
    //                 'invoice_detail_id' => $id2,
    //                 'type' => $request->waste ?? 'N/A',
    //             ];
    //         }
    //     }

    //     $data = [
    //         'request_id' => $invoice->id,
    //         'status' => $request->status,
    //         'resone' => $request->reject_resone,
    //         'items' => $items,
    //     ];
    //     $fakeRequest = new Request();
    //     $fakeRequest->replace($data);

    //     $test = $this->InvoiceService->changeRequestStatus($fakeRequest);

    //     if ($request->type === 'addon') {
    //         $detail = OrderAddon::findOrFail($id);
    //         $detail->waste = $request->input('waste');
    //         $detail->save();
    //         return response()->json(['message' => 'تم التحديث بنجاح']);
    //     }
    //     $detail = OrderDetail::findOrFail($id);
    //     $detail->waste = $request->input('waste');
    //     $detail->save();

    //     return response()->json(['message' => 'تم التحديث بنجاح']);
    // }
}
