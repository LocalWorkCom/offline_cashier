<?php


namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderWaste;
use App\Models\ReturnInvoiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ReturnInvoiceRequestService
{
    public function index(Request $request, $api = 0)
    {
        // try {
        $lang = app()->getLocale();
        if ($api === 1) {
            $employee = auth('employee')->user();
            if ((!$employee)) {
                return RespondWithBadRequest($lang, 4);
            }
        } else {
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }
        }
        $query1 = ReturnInvoiceRequest::with(['invoice', 'invoice.orders']);
            // ->where('is_active', 1)
            // ->where('status', '!=', 'rejected');
        App::setLocale($lang);
        if ($api === 1) {
            if ($employee->hasRole('Branch_Manager')) {
                $branch_id = $employee->branch_id;
                if ($branch_id) {
                    $query1->whereHas('invoice.orders', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
                }
            }
        }
        if ($api == 1) {
            $query = paginateOrGetAll($query1, $request, null);
            $meta =paginateOrGetAll($query1, $request, null);;
            $query = $query['data'] ?? [];
        } else {
            $query = $query1->get();
        }
        // dd($query);
        // return $query;
        $namesDishAndAddon = [];
        if (!empty($query)) {
            foreach ($query as $item) {
                $currentItemNames = [];
                $items = json_decode($item->invoice_details_ids);
                // dd($item);
                foreach ($items as $id) {
                    // $invoiceDetails = InvoiceDetails::where('id', $id)->select('type', 'invoice_id', 'details_id')->first();
                    $invoiceDetails = InvoiceDetails::where('id', $id->invoice_detail_id)->first();
                    // dd($id->invoice_detail_id);
                    if ($invoiceDetails) {
                        $invoice = Invoice::where('id', $invoiceDetails->invoice_id)->first();
                        $orders = Order::where('id', $invoice->order_id)->first();
                        if ($invoiceDetails->type == 'dish') {
                            $orderDetails = OrderDetail::where('order_id', $invoice->order_id)
                                ->where('id', $invoiceDetails->details_id)
                                ->with('dish')
                                ->first();
                            $nameColumn = 'name_' . $lang; // e.g., 'name_en' or 'name_ar'
                            $currentItemNames[] = $orderDetails->dish->$nameColumn ?? 'N/A';
                        } else {
                            $orderDetailsaddon = OrderAddon::where('id', $invoiceDetails->details_id)
                                ->with('Addon.addons')
                                ->first();
                            $nameColumn = 'name_' . $lang;
                            $currentItemNames[] = $orderDetailsaddon->Addon->addons->$nameColumn ?? 'N/A';
                        }
                    }
                }

                if (!empty($currentItemNames)) {
                    $namesDishAndAddon[] = $currentItemNames;
                }
                else
                {
                    $namesDishAndAddon[] = ['N/A'];
                }
            }
        }

        // if (!empty($query)) {
        //     foreach ($query as $item) {
        //         $items = json_decode($item->invoice_details_ids);
        //         $detailsWithNames = [];

        //         foreach ($items as $id) {
        //             $invoiceDetails = InvoiceDetails::find($id->invoice_detail_id);

        //             if ($invoiceDetails) {
        //                 $name = 'N/A';
        //                 $invoice = Invoice::find($invoiceDetails->invoice_id);
        //                 if ($invoiceDetails->type == 'dish') {
        //                     $orderDetails = OrderDetail::where('order_id', $invoice->order_id)
        //                         ->where('id', $invoiceDetails->details_id)
        //                         ->with('dish')
        //                         ->first();
        //                     $nameColumn = 'name_' . $lang;
        //                     $name = $orderDetails->dish->$nameColumn ?? 'N/A';
        //                     $quantity = $orderDetails->quantity ?? 0;
        //                     $total = $invoiceDetails->total;
        //                     $price_befor_tax = $invoiceDetails->price_befor_tax;
        //                     $price_after_tax = $invoiceDetails->price_after_tax;
        //                     $tax_value = $invoiceDetails->tax_value;
        //                     $coupon_value = $invoiceDetails->coupon_value;
        //                     $service_fees = $invoiceDetails->service_fees;
        //                     // $id = $orderDetails->dish->id;
        //                 } else {
        //                     $orderDetailsaddon = OrderAddon::where('id', $invoiceDetails->details_id)
        //                         ->with('Addon.addons')
        //                         ->first();
        //                     $nameColumn = 'name_' . $lang;
        //                     $name = $orderDetailsaddon->Addon->addons->$nameColumn ?? 'N/A';
        //                     $quantity = $orderDetailsaddon->quantity;
        //                     $total = $orderDetailsaddon->total;
        //                     $price_befor_tax = $orderDetailsaddon->price_before_tax;
        //                     $price_after_tax = $orderDetailsaddon->price_after_tax;
        //                     $tax_value = $orderDetailsaddon->tax_value;
        //                     $coupon_value = $orderDetailsaddon->coupon_value;
        //                     $service_fees = $orderDetailsaddon->service_fees;
        //                     // $id = $orderDetailsaddon->Addon->addons->id;
        //                 }
        //                 // ضيف الـ name مع الـ invoice_detail_id
        //                 $detailsWithNames[] = [
        //                     'invoice_detail_id' => $invoiceDetails->id,
        //                     // 'id' => $id,
        //                     'name' => $name,
        //                     'quantity' => $quantity ,
        //                     'total'=> $total,
        //                     'price_befor_tax' => $price_befor_tax,
        //                     'price_after_tax' => $price_after_tax,
        //                     'tax_value' => $tax_value,
        //                     'coupon_value' => $coupon_value,
        //                     'service_fees' => $service_fees,
        //                 ];
        //                 // $detailsWithNames[] = $data;
        //             }
        //         }
        //         // ✅ أضيفها جوه الـ invoice مباشرة
        //         // $item->invoice->details_with_names = $detailsWithNames;
        //         if ($item->invoice !== null) {
        //             $item->invoice->setRelation('details_with_names', collect($detailsWithNames));
        //         }
        //     }
        // }

        // return $query; // إذا كنت تريد إرجاع البيانات مباشرة
        if ($api == 1) {
            $datapag = [
                'data' => $query,
                'namesDishAndAddon' => $namesDishAndAddon,
                'meta' => $meta['meta'] ?? [],
            ];
            return ResponseWithSuccessDataPaginated($lang, $datapag, 1);
        }

        return response()->json([
            'status' => true,
            'data' => $query,
            'namesDishAndAddon' => $namesDishAndAddon
        ]);
        // } catch (\Exception $e) {
        //     return respondError($e->getMessage(), 2);
        // }
    }
    public function show(Request $request, $id,  $api = 0)
    {
        try {
            $lang = app()->getLocale();
            if ($api === 1) {
                $employee = auth('employee')->user();
                if ((!$employee)) {
                    return RespondWithBadRequest($lang, 4);
                }
            } else {
                $admin = auth('admin')->user();

                if ((!$admin)) {
                    return RespondWithBadRequest($lang, 4);
                }
            }
            $query = ReturnInvoiceRequest::where('id', $id)->with(['invoice', 'invoice.orders', 'invoice.orders.branch', 'employee', 'admin'])->first();
            // dd($query);
            $admin = Invoice::where('order_id', $query->invoice->orders->id)->where('invoice_type', 'credit_note')->with('admin')->first();
            // dd($admin);
            App::setLocale($lang);
            $dataOfDishes = [];
            $dataOfAddons = [];
            if (!empty($query)) {
                $orderDetails2 = [];

                if ($query->invoice && $query->invoice->orders) {
                    $order = $query->invoice->orders;
                    $orderDetails2 = [
                        'order_timestamp' => $query->created_at->format('Y-m-d H:i:s'),
                        'admin_code' => $admin ? $admin->admin->code : 'N/A'
                    ];
                }
                $items = json_decode($query->invoice_details_ids);


                foreach ($items as $id) {
                    // $invoiceDetails = InvoiceDetails::where('id', $id)->select('id', 'type', 'invoice_id', 'details_id')->first();
                    $invoiceDetails = InvoiceDetails::where('id', $id->invoice_detail_id)->first();
                    $invoice = Invoice::where('id', $invoiceDetails->invoice_id)->first();
                    $orders = Order::where('id', $invoice->order_id)->first();

                    if ($invoiceDetails->type == 'dish') {
                        $orderDetails = OrderDetail::where('order_id', $invoice->order_id)
                            ->where('id', $invoiceDetails->details_id)
                            ->with('dish', 'dishSize')
                            ->first();

                        $orderwaste = OrderWaste::where('order_detail_id', $invoiceDetails->details_id)->first();

                        $nameColumn = 'name_' . $lang;
                        $sizeColumn = 'size_name_' . $lang;

                        $dataOfDishes[] = [
                            'invoiceId' => $invoiceDetails->id ?? null,
                            'id' => $orderDetails->id ?? null,
                            'name' => $orderDetails->dish->$nameColumn ?? 'N/A',
                            'size' => $orderDetails->dishSize->$sizeColumn ?? 'N/A',
                            'quantity' => $id->quantity ?? null,
                            'price' => $invoiceDetails->total_before_tax / $invoiceDetails->quantity,
                            'tax' => $invoiceDetails->tax,
                            'service_fees' => $invoiceDetails->service_fees,
                            'total_after_tax' => $invoiceDetails->total_after_tax,
                            'coupon_value' => $invoiceDetails->coupon_value,
                            'status' => $orderDetails->status ?? null,
                            'statusInvoice' => $invoice->status ?? null,
                            'waste' => $orderwaste?->type ?? 'not_waste',
                        ];
                    } else {
                        $orderDetailsaddon = OrderAddon::where('id', $invoiceDetails->details_id)
                            ->with('Addon.addons', 'orderDetails.dish')
                            ->first();

                        $orderwaste = OrderWaste::where('order_addon_id', $invoiceDetails->details_id)->first();

                        $addonNameColumn = 'name_' . $lang;
                        $dishNameColumn = 'name_' . $lang;

                        $dataOfAddons[] = [
                            'invoiceId' => $invoiceDetails->id ?? null,
                            'id' => $orderDetailsaddon->id ?? null,
                            'forDish' => $orderDetailsaddon->orderDetails->dish->$dishNameColumn ?? null,
                            'name' => $orderDetailsaddon->Addon->addons->$addonNameColumn ?? 'N/A',
                            'quantity' => $id->quantity ?? null,
                            'price' => $invoiceDetails->total_before_tax / $invoiceDetails->quantity,
                            'tax' => $invoiceDetails->tax,
                            'service_fees' => $invoiceDetails->service_fees,
                            'total_after_tax' => $invoiceDetails->total_after_tax,
                            'coupon_value' => $invoiceDetails->coupon_value,

                            'status' => $orderDetailsaddon->status ?? null,
                            'statusInvoice' => $invoice->status ?? null,
                            'waste' => $orderwaste?->type ?? 'not_waste',
                        ];
                    }
                    // dd($dataOfDishes);
                }
            }
            $invoicerefund = Invoice::where('order_id', $query->invoice->orders->id)->where('invoice_type', 'credit_note')->first();
            return response()->json([
                'status' => true,
                'data' => $query,
                'dataOfDishes' => $dataOfDishes,
                'dataOfAddons' => $dataOfAddons,
                'statusOrder' => $orders->status ?? null,
                'invoicerefund' => $invoicerefund->invoice_num ?? null,
                'order_details2' => $orderDetails2 ?? null,

            ]);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 404);
        }
    }
}
