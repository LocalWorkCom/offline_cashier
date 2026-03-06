<?php


namespace App\Services\ClientServices;

use App\Events\NewOrder;
use App\Events\TableStatus;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Order;
use App\Models\Table;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Einvoice;
use App\Models\Employee;
use App\Models\BranchMenu;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Events\CashierNotify;
use App\Events\EditOrder;
use App\Models\ClientAddress;
use App\Models\OrderTracking;
use App\Models\BranchMenuSize;
use App\Models\BranchMenuAddon;
use App\Models\OrderTransaction;
use App\Models\TableReservationTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\BranchMenuAddonCategory;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\ReturnInvoiceRequest;
use App\Models\TableReservation;
use App\Services\AddressServices\BranchSiteService;
use App\Services\SettingsServices\BranchService;
use Google\Service\AndroidPublisher\OrderDetails;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InvoiceServiceOld
{

    protected $reservationService;
    protected $branchService;
    protected $orderService;

    protected $wasteService;

    public function __construct(ReservationService $reservationService, BranchSiteService $branchService, OrderService $orderService, WasteService $wasteService)
    {
        $this->reservationService = $reservationService;
        $this->branchService = $branchService;
        $this->orderService = $orderService;
        $this->wasteService = $wasteService;
    }

    public function CalculateItemOrder($order_id, $orderDetailsId, $orderAddonsId, $details_type, $new_quantity = 0)
    {
        $subTotal = 0;
        $itemData = []; // to track totals for later discount distribution
        $Order = Order::find($order_id);

        $IDBranch = $Order->branch_id;
        $coupon_application = getBranchSettings($IDBranch, 'coupon_application');

        $tax_application = getBranchSettings($IDBranch, 'tax_application');
        $tax_percentage = getBranchSettings($IDBranch, 'tax_percentage');
        $service_fees_value =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees');
        $service_fees_type =  ($Order->type  != 'dine-in' && $Order->type != "reservation-table") ? 0 : getBranchSettings($IDBranch, 'service_fees_type');
        $delivery_fees = ($Order->type == 'Delivery') ?  getBranchSettings($IDBranch, 'delivery_fees') : 0;
        $addonsTotal = 0;

        // Calculate coupon discount
        $couponValue = 0;
        $couponApplication = $Order->coupon_id ? getBranchSettings($IDBranch, 'coupon_application') : null;
        $totalTax = 0;
        $totalService = 0;
        $allSubTotalAfterCoupon = 0;
        $totalCouponValue = 0;

        //dish
        // dd($orderDetailsId);

        $DataOrderDetails = OrderDetail::whereIn('id', $orderDetailsId)->get();
        if (!empty($DataOrderDetails)) {
            $quantityDishMap = [];
            foreach ($orderDetailsId as $index => $detailId) {
                $quantityDishMap[$detailId] = $new_quantity[$index] ?? 0;
            }
            foreach ($DataOrderDetails as $DataOrderDetailKey => $DataOrderDetail) {
                $dishQuantity = $new_quantity != 0 ? ($quantityDishMap[$DataOrderDetail->id] ?? $DataOrderDetail['quantity']) : $DataOrderDetail['quantity'];
                $Branch_Dish = BranchMenu::where('dish_id', $DataOrderDetail->dish_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
                if ($Branch_Dish) {
                    $has_size = $Branch_Dish->dish->has_sizes;
                    if ($has_size && $DataOrderDetail->dish_size_id) {
                        $size_id = $DataOrderDetail->dish_size_id;
                        $branch_dish_size = BranchMenuSize::where('dish_size_id', $size_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();
                        $price =  $branch_dish_size->price;
                    } else {

                        $price =  $Branch_Dish->price;
                    }
                }

                $baseTotal = $price * $dishQuantity;

                // if ($Order->coupon_id && $couponApplication == 0) {
                //     $coupon = Coupon::find($Order->coupon_id);
                //     if ($coupon->type == 'fixed') {
                //         $couponValue = $coupon->value / (count($DataOrderDetails) + count($DataOrderDetail->dishAddons));
                //         $subTotalAfterCoupon = $baseTotal - $couponValue;
                //     } else {
                //         $couponValue = calcCoupon($baseTotal, $coupon);
                //         $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                //     }
                // } else {
                //     $subTotalAfterCoupon = $baseTotal;
                // }

                // if ($Order->coupon_id && $couponApplication == 0) {
                //     $coupon = Coupon::find($Order->coupon_id);
                //     if ($coupon->type == 'fixed') {
                //         $couponValue = $coupon->value / (count($DataOrderDetails) + count($DataOrderDetail->dishAddons));
                //         $subTotalAfterCoupon = $baseTotal - $couponValue;
                //     } else {
                //         $couponValue = calcCoupon($baseTotal, $coupon);
                //         $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                //     }
                // } else if ($DataOrderDetail->coupon_id) {
                //     $coupon = Coupon::find($DataOrderDetail->coupon_id);
                //     if ($coupon->type == 'fixed') {
                //         $couponValue = $coupon->value;
                //         $subTotalAfterCoupon = $baseTotal - $couponValue;
                //     } else {
                //         $couponValue = calcCoupon($baseTotal, $coupon);
                //         $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                //     }
                // } else {
                //     $subTotalAfterCoupon = $baseTotal;
                // }


                if ($Order->coupon_id && $couponApplication == 0) {
                    $coupon = Coupon::find($Order->coupon_id);
                    if ($coupon->type == 'fixed') {
                        $couponValue = $coupon->value / (count($DataOrderDetails) + count($DataOrderDetail->dishAddons));
                        $subTotalAfterCoupon = $baseTotal - $couponValue;
                    } else {
                        $couponValue = calcCoupon($baseTotal, $coupon);
                        $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                    }
                } else if ($DataOrderDetail->coupon_id) {
                    $coupon = Coupon::find($DataOrderDetail->coupon_id);
                    if ($coupon->type == 'fixed') {
                        $couponValue = $coupon->value;
                        $subTotalAfterCoupon = $baseTotal - $couponValue;
                    } else {
                        $couponValue = calcCoupon($baseTotal, $coupon);
                        $subTotalAfterCoupon = applyCoupon($baseTotal, $coupon);
                    }
                } else {
                    $subTotalAfterCoupon = $baseTotal;
                }


                // Service fee
                $serviceFee = 0;
                if (in_array($Order->type, ['dine-in', 'reservation-table'])) {
                    $serviceFee = ($service_fees_type === "percentage") ? ($subTotalAfterCoupon * $service_fees_value / 100) : 0;
                }

                // Tax
                $taxValue = ($tax_application == 1)
                    ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $serviceFee)
                    : ($subTotalAfterCoupon + $serviceFee) * $tax_percentage / 100;

                // Add to totals
                $totalTax += $taxValue;
                $totalService += $serviceFee;
                $totalCouponValue += $couponValue;
                $allSubTotalAfterCoupon += $subTotalAfterCoupon;

                $itemTotal = $baseTotal;
                $subTotal += $itemTotal;

                if ($details_type == "dish") {
                    return [
                        'service_fees' => round($serviceFee, 2),
                        'coupon_value' => round($couponValue, 2),
                        // 'total_before_tax' => round($subTotalAfterCoupon, 2),
                        'total_before_tax' => round($baseTotal, 2),
                        'total_before_coupon' => round($baseTotal, 2),
                        'total_after_tax' => round($subTotalAfterCoupon + $serviceFee + $taxValue, 2),
                        'tax' => round($taxValue, 2)
                    ];
                }
                $couponValue = 0;
            }
        }

        //addons
        if (! empty($orderAddonsId)) {
            $OrderAddonsArray = OrderAddon::whereIn('id', $orderAddonsId)->get();
            $addonDetails = [];
            if (!empty($OrderAddonsArray)) {
                $quantityAddonMap = [];
                foreach ($orderDetailsId as $index => $detailId) {
                    $quantityAddonMap[$detailId] = $new_quantity[$index] ?? 0;
                }
                foreach ($OrderAddonsArray as $OrderAddonsArrKey => $OrderAddonsArr) {
                    $addon_id = $OrderAddonsArr->Addon->id;
                    $addon = BranchMenuAddon::where('dish_addon_id', $addon_id)->where('branch_id', $IDBranch)->where('is_active', 1)->first();

                    $addonQuantity = $new_quantity != 0 ? ($quantityAddonMap[$OrderAddonsArr->id] ?? $OrderAddonsArr['quantity']) : $OrderAddonsArr['quantity'];
                    // $addonsTotal += $addon->price * $addonQuantity;
                    $addonPrice = $addon->price * $addonQuantity;

                    // if ($Order->coupon_id && $couponApplication === 0) {
                    //     $coupon = Coupon::find($Order->coupon_id);
                    //     if ($coupon->type == 'fixed') {
                    //         $subTotalAfterCoupon = $addonPrice - $couponValue;
                    //     } else {
                    //         $couponValue = calcCoupon($addonPrice, $coupon);
                    //         $subTotalAfterCoupon = applyCoupon($addonPrice, $coupon);
                    //     }
                    // } else {
                    //     $subTotalAfterCoupon = $addonPrice;
                    // }


                    if ($Order->coupon_id && $couponApplication == 0) {
                        $coupon = Coupon::find($Order->coupon_id);

                        if ($coupon->type == 'fixed') {
                            $subTotalAfterCoupon = $addonPrice - $couponValue;
                        } else {

                            $couponValue = calcCoupon($addonPrice, $coupon);
                            $subTotalAfterCoupon = applyCoupon($addonPrice, $coupon);
                        }
                    } else {
                        $subTotalAfterCoupon = $addonPrice;
                    }

                    $addonService = in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type == "percentage"
                        ? ($subTotalAfterCoupon * $service_fees_value / 100)
                        : 0;
                    $addonTax = ($tax_application == 1)
                        ? CalculateTax($tax_percentage, $subTotalAfterCoupon + $addonService)
                        : ($subTotalAfterCoupon + $addonService) * $tax_percentage / 100;

                    $totalTax += $addonTax;
                    $totalService += $addonService;
                    $totalCouponValue += $couponValue;
                    $allSubTotalAfterCoupon += $subTotalAfterCoupon;

                    $itemTotal = $addonPrice;
                    $subTotal += $itemTotal;

                    if ($details_type == "addon") {
                        return [
                            'service_fees' => round($addonService, 2),
                            'coupon_value' => round($couponValue, 2),
                            'total_before_tax' => round($addonPrice, 2),
                            'total_before_coupon' => round($addonPrice, 2),
                            'total_after_tax' => round($subTotalAfterCoupon + $addonService + $addonTax, 2),
                            'tax' => round($addonTax, 2)
                        ];
                    }
                    $couponValue = 0;
                }
            }
        }


        // $itemTotal = $baseTotal + $addonsTotal;
        // $subTotal += $itemTotal;
        // Add fixed service if applicable
        if (in_array($Order->type, ['dine-in', 'reservation-table']) && $service_fees_type === 'fixed') {
            $totalService = $service_fees_value;
        }
        $total = $allSubTotalAfterCoupon + $totalService + $totalTax + $Order->delivery_fees;
        // $total_before_coupon = $subTotal + $totalService + $totalTax + $Order->delivery_fees;
        return [
            'service_fees' => round($totalService, 2),
            'coupon_value' => ($Order->coupon_id ? round($totalCouponValue, 2) : 0),
            'total_before_tax' => round($subTotal, 2),
            'total_before_coupon' => round($subTotal, 2),
            'total_after_tax' => round($total, 2),
            'tax' => round($totalTax, 2),
            'delivery_fees' => round($Order->delivery_fees, 2)
        ];
    }

    public function makeInvoice(
        $order_id,
        $order_type,
        $invoice_type,
        $quantities = 0,
        $dish_size_id = 0,
        $invoice_id = null,
        $order_details_ids = array(),
        $order_addon_ids = array()
    ) {
        // try{

        $user_id = Auth::guard('employee')->user();
        if (!in_array($order_type, ['order', 'reservation'])) {
            return respondError("errors", 400, ['order_type' => [__('order.order_type_not_found')]]);
        }

        if ($order_type == "order") {
            $checkOrder = Order::find($order_id);
        } else {
            $checkOrder = TableReservation::find($order_id);
        }
        if (!$checkOrder) {
            return respondError("errors", 400, ['order_id' => [__('order.order_not_found')]]);
        }

        if (count($order_details_ids) > 0) {
            $checkOrderDetails = OrderDetail::whereIn('id', $order_details_ids)->pluck('id')->toArray();
            $missingIds = array_diff($order_details_ids, $checkOrderDetails);
            if (!empty($missingIds)) {
                return respondError("errors", 400, ['order_details_id' => [__('order.order_details_not_found')]]);
            }
        }

        if (count($order_addon_ids) > 0) {
            $checkOrderAddons = OrderAddon::whereIn('id', $order_addon_ids)->pluck('id')->toArray();
            $missingIds = array_diff($order_addon_ids, $checkOrderAddons);
            if (!empty($missingIds)) {
                return respondError("errors", 400, ['order_addons_id' => [__('order.order_addons_not_found')]]);
            }
        }

        if (!in_array($invoice_type, ['invoice', 'credit_note'])) {
            return respondError("errors", 400, ['invoice_type' => [__('order.invoice_type_not_found')]]);
        }

        if (! empty($invoice_id) && $invoice_type == "credit_note") {
            $checkInvoice = Invoice::where('id', $invoice_id)->first();
            if (empty($checkInvoice)) {
                return respondError("errors", 400, ['invoice_id' => [__('order.invoice_not_found')]]);
            }
        }

        $order_transaction = null;
        if ($order_type == "order") {
            $order_details = Order::where('id', $order_id)->first();
            $order_transaction = OrderTransaction::where('order_id', $order_id)->latest()->first();
        } else {
            $order_details = TableReservation::where('id', $order_id)->first();
            $order_transaction = TableReservationTransaction::where('table_reservation_id', $order_id)->latest()->first();
        }

        $caluclate_order = $this->CalculateItemOrder($order_id, $order_details_ids, $order_addon_ids, "order", $quantities); //type = dish or addon or order

        $add_invoice = new Invoice();
        $add_invoice->order_id = $order_id;
        $add_invoice->order_type = $order_type;
        $add_invoice->invoice_type = $invoice_type;
        $add_invoice->invoice_num =
            $order_type == "order"
            ? (
                $invoice_type == 'credit_note'
                // ? "CN-" . $checkOrder->order_number
                ? "CN-" . ltrim($checkOrder->order_number, '#')
                : $checkOrder->invoice_number
            )
            : $checkOrder->reservation_number;
        $add_invoice->date = $checkOrder->date;
        $add_invoice->time = date('H:m:i');
        $add_invoice->note = $order_type == "order" ? $checkOrder->note : $checkOrder->notes;
        $add_invoice->status = ($invoice_type == 'credit_note')
            ? 'paid'
            : ($order_transaction != null
                ? $order_transaction->payment_status
                : null);
        $add_invoice->service_fees = $order_type == "order" ? $caluclate_order['service_fees'] : 0;
        $add_invoice->delivery_fees = $order_type == "order" ? $caluclate_order['delivery_fees'] : 0;
        $add_invoice->tax_percentage = $order_type == "order" ? ($checkOrder->tax_percentage != null ? $checkOrder->tax_percentage : 0) : 0;
        $add_invoice->service_percentage = $order_type == "order" ? ($checkOrder->service_percentage != null ? $checkOrder->service_percentage : 0) : 0;
        $add_invoice->coupon_id = $order_details->coupon_id;
        $add_invoice->coupon_value = $order_type == "order" ? $caluclate_order['coupon_value'] : 0;
        $add_invoice->total_before_tax = $order_type == "order" ? $caluclate_order['total_before_tax'] : ($order_transaction != null ? $order_transaction->paid : null);
        $add_invoice->total_before_coupon = $order_type == "order" ? $caluclate_order['total_before_coupon'] : ($order_transaction != null ? $order_transaction->paid : null);
        $add_invoice->total_after_tax = $order_type == "order" ? $caluclate_order['total_after_tax'] : ($order_transaction != null ? $order_transaction->paid : null);
        $add_invoice->tax = $order_type == "order" ? $caluclate_order['tax'] : 0;
        $add_invoice->parent_id = $invoice_id;
        $add_invoice->make_type = $checkOrder->make_type;
        $add_invoice->is_active = 1;
        $add_invoice->created_by = ($invoice_type == 'credit_note') ? $checkOrder->created_by : (($user_id) ? $user_id->id : null);
        $add_invoice->save();

        if (! empty($order_details_ids)) {

            $quantityDishMap = [];
            foreach ($order_details_ids as $indexDish => $detailId) {
                $quantityDishMap[$detailId] = $quantities[$indexDish] ?? 0;
            }
            $orderDetails = OrderDetail::whereIn('id', $order_details_ids)->get();
            foreach ($orderDetails as $orderDetailKey => $orderDetail) {
                $dishQuantity = $quantities != 0 ? ($quantityDishMap[$orderDetail->id] ?? $orderDetail['quantity']) : $orderDetail['quantity'];
                $caluclate_dish = $this->CalculateItemOrder($order_id, [$orderDetail->id], [], "dish", [$dishQuantity]); //type = dish or addon or order
                $add_invoice_details = new InvoiceDetails();
                $add_invoice_details->invoice_id = $add_invoice->id;
                $add_invoice_details->details_id = $orderDetail->id;
                $add_invoice_details->dish_size_id = $dish_size_id != 0 ? $dish_size_id : $orderDetail->dish_size_id;
                $add_invoice_details->type = "dish";
                $add_invoice_details->note = $orderDetail->note;
                $add_invoice_details->quantity = $dishQuantity;
                $add_invoice_details->tax = $caluclate_dish['tax'];;
                $add_invoice_details->service_fees = $caluclate_dish['service_fees'];;
                $add_invoice_details->coupon_id = $orderDetail->coupon_id;
                $add_invoice_details->coupon_value = $caluclate_dish['coupon_value'];
                $add_invoice_details->total_before_tax = $caluclate_dish['total_before_tax'];
                $add_invoice_details->total_before_coupon = $caluclate_dish['total_before_coupon'];
                $add_invoice_details->total_after_tax = $caluclate_dish['total_after_tax'];
                $add_invoice_details->status = $orderDetail->status;
                $add_invoice_details->created_by = $orderDetail->created_by;
                $add_invoice_details->save();
            }
        }

        if (! empty($order_addon_ids)) {
            $quantityAddonMap = [];
            foreach ($order_addon_ids as $indexAddon => $addonId) {
                $quantityAddonMap[$addonId] = $quantities[$indexAddon] ?? 0;
            }

            $orderAddons = OrderAddon::whereIn('id', $order_addon_ids)->get();
            foreach ($orderAddons as $orderAddonKey => $orderAddon) {
                if ($invoice_type == 'credit_note') {
                    $invoice_details_info = InvoiceDetails::where('details_id', $orderAddon->order_details_id)->latest()->first();
                    $addonQuantity = $invoice_details_info->quantity;
                } else {
                    $addonQuantity = $quantities != 0 ? ($quantityAddonMap[$orderAddon->id] ?? $orderAddon['quantity']) : $orderAddon['quantity'];
                }

                $caluclate_addon = $this->CalculateItemOrder($order_id, [], [$orderAddon->id], "addon", [$addonQuantity]); //type = dish or addon or order
                $add_invoice_details = new InvoiceDetails();
                $add_invoice_details->invoice_id = $add_invoice->id;
                $add_invoice_details->order_detail_id = $orderAddon->order_details_id;
                $add_invoice_details->details_id = $orderAddon->id;
                $add_invoice_details->type = "addon";
                $add_invoice_details->quantity = $addonQuantity;
                $add_invoice_details->tax = $caluclate_addon['tax'];;
                $add_invoice_details->service_fees = $caluclate_addon['service_fees'];;
                $add_invoice_details->coupon_value = $caluclate_addon['coupon_value'];
                $add_invoice_details->total_before_tax = $caluclate_addon['total_before_tax'];
                $add_invoice_details->total_before_coupon = $caluclate_addon['total_before_coupon'];
                $add_invoice_details->total_after_tax = $caluclate_addon['total_after_tax'];
                $add_invoice_details->status = $orderAddon->status;
                $add_invoice_details->created_by = $orderAddon->created_by;
                $add_invoice_details->save();
            }
        }

        return $add_invoice->id;

        // }catch(Exception $e) {
        //     DB::rollBack();
        //     return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        // }


    }

    public function updateInvoiceTransaction($invoice_id)
    {
        $check_invoice = Invoice::find($invoice_id);
        if (!$check_invoice) {
            return respondError("errors", 400, ['invoice_id' => [__('order.invoice_not_found')]]);
        }
        if ($check_invoice->order_type == "order") {
            $order_transaction = OrderTransaction::where('order_id', $check_invoice->order_id)->latest()->first();
        } else {
            $order_transaction = TableReservationTransaction::where('table_reservation_id', $check_invoice->order_id)->latest()->first();
        }

        if ($order_transaction) {
            $check_invoice->status = $order_transaction != null ? $order_transaction->payment_status : null;
            $check_invoice->save();
        }
    }

    public function makeCancelRequest(
        $invoice_id,
        $items = array(),
        $request_type,
        $reason
    ) {
        // try{

        $checkOrder = Invoice::find($invoice_id);
        if (!$checkOrder) {
            return respondError("errors", 400, ['invoice_id' => [__('order.invoice_not_found')]]);
        }

        if (! empty($items)) {
            $invoice_details_ids = array_column($items, 'invoice_detail_id'); // extract all item_id values
            $checkInvoiceDetails = InvoiceDetails::whereIn('id', $invoice_details_ids)->pluck('id')->toArray();
            $missingIds = array_diff($invoice_details_ids, $checkInvoiceDetails);
            if (!empty($missingIds)) {
                return respondError("errors", 400, ['invoice_details_ids' => [__('order.invoice_details_not_found')]]);
            }
        }
        if (!in_array($request_type, ['partial', 'full'])) {
            return respondError("errors", 400, ['request_type' => [__('order.request_type_not_found')]]);
        }

        if (empty($reason)) {
            return respondError("errors", 400, ['reason' => [__('order.reason_not_empty')]]);
        }

        //validation in invoice_detail_id && quantity
        $invoiceId = $invoice_id;
        // $items = $items;
        $requests = ReturnInvoiceRequest::where('invoice_id', $invoiceId)->get();
        $status = null;
        if ($request_type == 'partial' && !empty($items)) {
            foreach ($items as $item) {
                $found = false;
                foreach ($requests as $requestRow) {
                    $invoiceDetails = json_decode($requestRow->invoice_details_ids, true);
                    // $invoiceDetails = $requestRow->invoice_details_ids;
                    foreach ($invoiceDetails as $detail) {
                        if (
                            isset($detail['invoice_detail_id'], $detail['quantity']) &&
                            $detail['invoice_detail_id'] == $item['invoice_detail_id'] &&
                            $detail['quantity'] == $item['quantity']
                        ) {
                            $found = true;
                            $status = $requestRow->status;
                            break 2; // stop both loops
                        }
                    }
                }
                if ($found) {
                    if ($status == "pending") {
                        return respondError("errors", 400, ['status' => [__('order.request_already_send')]]);
                    } elseif ($status == "accept") {
                        return respondError("errors", 400, ['status' => [__('order.request_already_acceptes')]]);
                    } else {
                        return respondError("errors", 400, ['status' => [__('order.request_already_rejected')]]);
                    }
                }
            }
        }

        $item = [];
        if ($request_type == "full") {
            $order_details = $checkOrder->invoiceDetails;
            if ($order_details) {
                foreach ($order_details as $item_details) {

                    $item[] = [
                        'invoice_detail_id' => $item_details->id,
                        'quantity' => $item_details->quantity,
                    ];
                }
            }
            $all_items = json_encode($item);
        } else {
            $all_items = json_encode($items);
        }


        $request_num = $this->generateRequestNumber();
        $created_by = Auth::guard('employee')->user()->id;

        $add_new_request = new ReturnInvoiceRequest();
        $add_new_request->invoice_id = $invoice_id;
        $add_new_request->invoice_details_ids = $all_items;
        $add_new_request->request_type = $request_type;
        $add_new_request->reason = $reason;
        $add_new_request->request_num = $request_num;
        $add_new_request->date = date('Y-m-d');
        $add_new_request->time = date('H:i:s');
        $add_new_request->is_active = 1;
        $add_new_request->created_by = $created_by;
        $add_new_request->save();

        return ResponseWithSuccessData('ar', $add_new_request, 1);
        // }catch(Exception $e) {
        //     DB::rollBack();
        //     return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        // }
    }

    private function generateRequestNumber()
    {
        $prefix = "#RC-";
        $get_last_id = ReturnInvoiceRequest::latest()->first();
        if ($get_last_id) {
            return $new_request_id = $prefix . ($get_last_id->id + 1);
        } else {
            return $new_request_id = $prefix . "1";
        }
    }
    public function changeRequestStatus(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        // Validate the request
        $validator = Validator::make($request->all(), [
            'request_id' => 'required|exists:return_invoice_requests,id',
            'status' => 'required|in:accept,reject',
            'reason' => 'nullable|string|max:255',
            // 'items' => 'required|array', // Ensure 'items' is an array
            // 'items.*.invoice_detail_id' => 'required|exists:invoice_details,id', // Validate each invoice_detail_id
            // 'items.*.quantity' => 'required', // Validate each invoice_detail_id
            // 'items.*.type' => 'required|in:waste,not_waste,temp', // Adjust based on valid types
        ]);
        // If validation fails, return the error response
        if ($validator->fails()) {

            Log::info('Validation failed');
            return respondErrorData($validator->errors(), 400, $validator->errors());
        }
        // Get items from request
        $items = $request->items;
        // Start database transaction
        DB::beginTransaction();
        try {
            // Fetch the ReturnInvoiceRequest
            $ReturnInvoiceRequest = ReturnInvoiceRequest::find($request->request_id);
            $invoice = Invoice::find($ReturnInvoiceRequest->invoice_id);
            if (!$invoice) {
                // return respondError(__('invoice not found'), 404);
                return respondError("error", 400, ['error' => 'invoice not found']);
            }
            if (!$ReturnInvoiceRequest) {
                Log::info('ReturnInvoiceRequest not found');
                return respondError("error", 400, ['error' => 'ReturnInvoiceRequest not found']);
            }
            if ($ReturnInvoiceRequest->request_type == 'partial' && empty($items)) {
                return respondError("error", 400, ['error' => 'items required']);
            }
            // Convert Invoice to Order
            $response = $this->orderService->convertInvoiceToOrder($ReturnInvoiceRequest->invoice_id, json_decode($ReturnInvoiceRequest->invoice_details_ids));
            // Check if the conversion was successful
            if (isset($response['error'])) {
                return respondError("error", 400, ['error' => $response['error']]);
            }
            // Update the status and reason of the ReturnInvoiceRequest
            $ReturnInvoiceRequest->status = $request->status;
            $ReturnInvoiceRequest->reject_resone = $request->reason;
            $ReturnInvoiceRequest->save();

            if ($response && !isset($response['error'])) {
                $order_detail_ids = array_column($response['order_details'], 'order_detail_id');
                if (count($response['order_details']) > count($response['order_addons'])) {
                    $quantities = array_column($response['order_details'], 'quantity');
                } else {
                    $quantities = array_column($response['order_addons'], 'quantity');
                }
            }
            if ($request->status == 'accept') {

                // If the request is accepted, process the cancellation
                if ($ReturnInvoiceRequest->request_type == 'full') {
                    $data = new Request([
                        'order_id' => $response['order_id'],
                        'item_id' => null,
                        'reason_id' => null,
                        'reason' => $ReturnInvoiceRequest->reason,
                        'type' => 1
                    ]);
                    $cancel_response =  $this->orderService->orderCancel($data);
                    $responseData = $cancel_response->original;
                    if (!$responseData['status']) {
                        return $cancel_response;
                    }
                } else {
                    if ($response && !isset($response['error'])) {
                        foreach ($response['order_details'] as  $order_detail) {

                            $data = new Request([
                                'order_id' => $response['order_id'],
                                'item_id' => $order_detail['order_detail_id'],
                                'reason_id' => null,
                                'reason' => $ReturnInvoiceRequest->reason,
                                'quantity' => $order_detail['quantity'],
                                'type' => 2
                            ]);
                            $cancel_response =  $this->orderService->orderCancel($data);
                            $responseData = $cancel_response->original;
                            if (!$responseData['status']) {
                                return $cancel_response;
                            }
                        }
                    }
                }

                if ($response && !isset($response['error'])) {
                    $order = Order::find($response['order_id']);
                    $hasPaidTransaction = OrderTransaction::where('order_id', $response['order_id'])
                        ->where('payment_status', 'paid')
                        ->where('is_refund', 0)
                        ->exists();
                    // If the order is paid, create a credit note invoice
                    if ($hasPaidTransaction) {
                        $order_addons = OrderAddon::whereIn('order_details_id', $order_detail_ids)->where('order_id', $response['order_id'])->pluck('id')->toArray();
                        $responseInvoice = $this->makeInvoice(
                            $response['order_id'],
                            "order",
                            "credit_note",
                            $quantities,
                            0,
                            $ReturnInvoiceRequest->invoice_id,
                            $order_detail_ids,
                            $order_addons
                        );
                        if (is_numeric($responseInvoice)) {
                            OrderTransaction::where('order_id', $response['order_id'])->where('is_refund', 1)->update(['invoice_id' => $responseInvoice]);
                            Einvoice::create([
                                "invoice_id" => $responseInvoice,
                                "invoice_type" => 'c'
                            ]);
                        }
                    }
                    if ($request->status == 'accept' && $invoice->orders->status != 'pending') {
                        foreach ($items as $item) {
                            $invoice_detail = InvoiceDetails::find($item['invoice_detail_id']);
                            $quantity = $item['quantity']; // from original request
                            if ($invoice_detail->type == 'dish') {
                                $dataRequest = [
                                    'order_id' => $response['order_id'] ?? null,
                                    'invoice_id' => $ReturnInvoiceRequest->invoice_id,
                                    'return_invoice_request_id' => $request->request_id,
                                    'order_detail_id' => $invoice_detail->details_id,
                                    'order_addon_id' => null,
                                    'waste_quantity' => $quantity,
                                    'waste_reason_id' => 1,
                                    'type' => $item['type'],
                                    'flag' => 'dish',
                                    'reused' => 1,
                                    'note' => '',
                                ];
                                $wasteResponse =  $this->wasteService->storeWaste($dataRequest, $lang, 1);
                                $responseData = $wasteResponse->original;
                                // Handle service response
                                if (!$responseData['status']) {
                                    return $wasteResponse;
                                }
                            } else {
                                $dataRequest = [
                                    'order_id' => $response['order_id'] ?? null,
                                    'invoice_id' => $ReturnInvoiceRequest->invoice_id,
                                    'return_invoice_request_id' => $request->request_id,
                                    'order_detail_id' => null,
                                    'order_addon_id' => $invoice_detail->details_id,
                                    'waste_quantity' => $quantity,
                                    'waste_reason_id' => 1,
                                    'type' => $item['type'],
                                    'flag' => 'addon',
                                    'reused' => 1,
                                    'note' => '',
                                ];
                                $wasteResponse =     $this->wasteService->storeWaste($dataRequest, $lang, 1);
                                $responseData = $wasteResponse->original;
                                // Handle service response
                                if (!$responseData['status']) {
                                    return $responseData;
                                }
                            }
                            $InvoiceDetails = InvoiceDetails::where('id', $item['invoice_detail_id'])->update(['in_request_return' => 0]);
                        }
                        $Invoice = Invoice::where('id', $ReturnInvoiceRequest->invoice_id)->update(['in_request_return' => 0]);
                        $OrderDetails = OrderDetail::whereIn('id', $order_detail_ids)->update(['in_request_return' => 0]);
                        // $OrderAddons = OrderAddon::whereIn('id', $order_addons)->update(['in_request_return' => 0]);
                        $Order = Order::where('id', $response['order_id'])->update(['in_request_return' => 0]);
                    }
                } else {
                    $order_addons = OrderAddon::whereIn('order_details_id', $order_detail_ids)->where('order_id', $response['order_id'])->pluck('id')->toArray();
                    // Handle the 'reject' case (e.g., set status back to 'in_progress')
                    $OrderDetails = OrderDetail::whereIn('id', $order_detail_ids)->update(['in_request_return' => 0]);
                    // $OrderAddons = OrderAddon::whereIn('id', $order_addons)->update(['in_request_return' => 0]);
                    $Order = Order::where('id', $response['order_id'])->update(['in_request_return' => 0]);
                }
                // Commit transaction
                DB::commit();
            }
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            // Rollback the transaction if any error occurs
            DB::rollBack();
            // Return an error response
            return respondErrorData('error', 500, 'An error occurred while processing the request');
        }
    }
    // public function changeRequestStatus(Request $request)
    // {


    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);
    //     // Validate the request
    //     $validator = Validator::make($request->all(), [
    //         'request_id' => 'required|exists:return_invoice_requests,id',
    //         'status' => 'required|in:accept,reject',
    //         'reason' => 'nullable|string|max:255',
    //         // 'items' => 'required|array', // Ensure 'items' is an array
    //         // 'items.*.invoice_detail_id' => 'required|exists:invoice_details,id', // Validate each invoice_detail_id
    //         // 'items.*.quantity' => 'required', // Validate each invoice_detail_id
    //         // 'items.*.type' => 'required|in:waste,not_waste,temp', // Adjust based on valid types
    //     ]);


    //     // If validation fails, return the error response
    //     if ($validator->fails()) {

    //         Log::info('Validation failed');
    //         return respondErrorData($validator->errors(), 400, $validator->errors());
    //     }


    //     // Get items from request
    //     $items = $request->items;
    //     // Start database transaction

    //     DB::beginTransaction();

    //     try {
    //         // Fetch the ReturnInvoiceRequest
    //         $ReturnInvoiceRequest = ReturnInvoiceRequest::find($request->request_id);
    //         $invoice = Invoice::find($ReturnInvoiceRequest->invoice_id);



    //         if (!$invoice) {
    //             // return respondError(__('invoice not found'), 404);
    //             return respondError("error", 400, ['error' => 'invoice not found']);
    //         }

    //         if (!$ReturnInvoiceRequest) {
    //             Log::info('ReturnInvoiceRequest not found');
    //             return respondError("error", 400, ['error' => 'ReturnInvoiceRequest not found']);
    //         }



    //         if ($ReturnInvoiceRequest->request_type == 'partial' && empty($items)) {
    //             return respondError("error", 400, ['error' => 'items required']);
    //         }
    //         // Convert Invoice to Order
    //         $response = $this->orderService->convertInvoiceToOrder($ReturnInvoiceRequest->invoice_id, json_decode($ReturnInvoiceRequest->invoice_details_ids));

    //         // Check if the conversion was successful
    //         if (isset($response['data']['error'])) {
    //             return respondError("error", 400, ['error' => $response['error']]);
    //         }

    //         // Update the status and reason of the ReturnInvoiceRequest
    //         $ReturnInvoiceRequest->status = $request->status;
    //         $ReturnInvoiceRequest->reject_resone = $request->reason;
    //         $ReturnInvoiceRequest->save();

    //         if (isset($response['data']) && !isset($response['data']['error'])) {
    //             $order_detail_ids = array_column($response['order_details'], 'order_detail_id');

    //             if (count($response['order_details']) > count($response['order_addons'])) {

    //                 $quantities = array_column($response['order_details'], 'quantity');
    //             } else {
    //                 $quantities = array_column($response['order_addons'], 'quantity');
    //             }
    //         }



    //         if ($request->status == 'accept') {
    //             // If the request is accepted, process the cancellation
    //             if ($ReturnInvoiceRequest->request_type == 'full') {
    //                 $data = new Request([
    //                     'order_id' => $response['order_id'],
    //                     'item_id' => null,
    //                     'reason_id' => null,
    //                     'reason' => $ReturnInvoiceRequest->reason,
    //                     'type' => 1
    //                 ]);
    //                 $cancel_response =  $this->orderService->orderCancel($data);
    //                 $responseData = $cancel_response->original;

    //                 if (!$responseData['status']) {
    //                     return $cancel_response;
    //                 }
    //             } else {

    //                 if (isset($response['data']) && !isset($response['data']['error'])) {
    //                     foreach ($response['order_details'] as $order_detail) {
    //                         $data = new Request([
    //                             'order_id' => $response['order_id'],
    //                             'item_id' => $order_detail['order_detail_id'],
    //                             'reason_id' => null,
    //                             'reason' => $ReturnInvoiceRequest->reason,
    //                             'quantity' => $order_detail['quantity'],
    //                             'type' => 2
    //                         ]);

    //                         $cancel_response =  $this->orderService->orderCancel($data);
    //                         $responseData = $cancel_response->original;
    //                         if (!$responseData['status']) {
    //                             return $cancel_response;
    //                         }
    //                     }
    //                 }
    //             }

    //             if (isset($response['data']) && !isset($response['data']['error'])) {
    //                 $order = Order::find($response['order_id']);
    //                 $hasPaidTransaction = OrderTransaction::where('order_id', $response['order_id'])
    //                     ->where('payment_status', 'paid')
    //                     ->where('is_refund', 0)
    //                     ->exists();


    //                 // If the order is paid, create a credit note invoice
    //                 if ($hasPaidTransaction) {
    //                     $order_addons = OrderAddon::whereIn('order_details_id', $order_detail_ids)->where('order_id', $response['order_id'])->pluck('id')->toArray();

    //                     $responseInvoice = $this->makeInvoice(
    //                         $response['order_id'],
    //                         "order",
    //                         "credit_note",
    //                         $quantities,
    //                         0,
    //                         $ReturnInvoiceRequest->invoice_id,
    //                         $order_detail_ids,
    //                         $order_addons
    //                     );


    //                     if (is_numeric($responseInvoice)) {
    //                         OrderTransaction::where('order_id', $response['order_id'])->where('is_refund', 1)->update(['invoice_id' => $responseInvoice]);
    //                         Einvoice::create([
    //                             "invoice_id" => $responseInvoice,
    //                             "invoice_type" => 'c'
    //                         ]);
    //                     }
    //                 }


    //                 if ($request->status == 'accept' && $invoice->orders->status != 'pending') {
    //                     foreach ($items as $item) {
    //                         $invoice_detail = InvoiceDetails::find($item['invoice_detail_id']);
    //                         $quantity = $item['quantity']; // from original request
    //                         if ($invoice_detail->type == 'dish') {

    //                             $dataRequest = [
    //                                 'order_id' => $response['order_id'] ?? null,
    //                                 'invoice_id' => $ReturnInvoiceRequest->invoice_id,
    //                                 'return_invoice_request_id' => $request->request_id,
    //                                 'order_detail_id' => $invoice_detail->details_id,
    //                                 'order_addon_id' => null,
    //                                 'waste_quantity' => $quantity,
    //                                 'waste_reason_id' => 1,
    //                                 'type' => $item['type'],
    //                                 'flag' => 'dish',
    //                                 'reused' => 1,
    //                                 'note' => '',
    //                             ];
    //                             $wasteResponse =  $this->wasteService->storeWaste($dataRequest, $lang, 1);
    //                             $responseData = $wasteResponse->original;
    //                             // Handle service response
    //                             if (!$responseData['status']) {

    //                                 return $wasteResponse;
    //                             }
    //                         } else {

    //                             $dataRequest = [
    //                                 'order_id' => $response['order_id'] ?? null,
    //                                 'invoice_id' => $ReturnInvoiceRequest->invoice_id,
    //                                 'return_invoice_request_id' => $request->request_id,
    //                                 'order_detail_id' => null,
    //                                 'order_addon_id' => $invoice_detail->details_id,
    //                                 'waste_quantity' => $quantity,
    //                                 'waste_reason_id' => 1,
    //                                 'type' => $item['type'],
    //                                 'flag' => 'addon',
    //                                 'reused' => 1,
    //                                 'note' => '',
    //                             ];

    //                             $wasteResponse =     $this->wasteService->storeWaste($dataRequest, $lang, 1);
    //                             $responseData = $wasteResponse->original;
    //                             // Handle service response

    //                             if (!$responseData['status']) {

    //                                 return $responseData;
    //                             }
    //                         }
    //                         $InvoiceDetails = InvoiceDetails::where('id', $item['invoice_detail_id'])->update(['in_request_return' => 0]);
    //                     }
    //                     $Invoice = Invoice::where('id', $ReturnInvoiceRequest->invoice_id)->update(['in_request_return' => 0]);
    //                     $OrderDetails = OrderDetail::whereIn('id', $order_detail_ids)->update(['in_request_return' => 0]);
    //                     // $OrderAddons = OrderAddon::whereIn('id', $order_addons)->update(['in_request_return' => 0]);
    //                     $Order = Order::where('id', $response['order_id'])->update(['in_request_return' => 0]);
    //                 }
    //             } else {
    //                 $order_addons = OrderAddon::whereIn('order_details_id', $order_detail_ids)->where('order_id', $response['order_id'])->pluck('id')->toArray();
    //                 // Handle the 'reject' case (e.g., set status back to 'in_progress')
    //                 $OrderDetails = OrderDetail::whereIn('id', $order_detail_ids)->update(['in_request_return' => 0]);
    //                 // $OrderAddons = OrderAddon::whereIn('id', $order_addons)->update(['in_request_return' => 0]);
    //                 $Order = Order::where('id', $response['order_id'])->update(['in_request_return' => 0]);
    //             }
    //             // Commit transaction
    //         DB::commit();
    //         }


    //         return RespondWithSuccessRequest($lang, 1);
    //     } catch (\Exception $e) {
    //         // Rollback the transaction if any error occurs
    //         DB::rollBack();

    //         // Return an error response
    //         return respondErrorData('error', 500, 'An error occurred while processing the request' );
    //     }
    // }
    public function updateInvoice($invoice_id)
    {
        Invoice::find($invoice_id)->update(['status' => 'paid']);
        return true;
    }

    public function editInvoice($orderId)
    {
        // try{

        $check_invoice = Invoice::where('order_id', $orderId)->where('order_type', 'order')->where('invoice_type', 'invoice')->where('status', 'unpaid')->first();
        if (! $check_invoice) {
            return respondError("errors", 400, ['invoice_id' => [__('order.invoice_not_found')]]);
        }

        $order_id = $check_invoice->order_id;
        $order_type = $check_invoice->order_type;
        $invoice_type = $check_invoice->invoice_type;

        if ($order_type == "order") {
            $check_order = Order::find($orderId);
            $get_order_details = $check_order->orderDetails->where('status', '!=', 'cancel');
            $get_order_addons = $check_order->orderAddons->where('status', '!=', 'cancel');
            $get_order_details_ids = $check_order->orderDetails->where('status', '!=', 'cancel')->pluck('id');
            $get_order_addons_ids = $check_order->orderAddons->where('status', '!=', 'cancel')->pluck('id');
        } else {
            $check_order = TableReservation::find($orderId);
            $get_order_details_ids = [];
            $get_order_addons_ids = [];
        }
        if (! $check_order) {
            return respondError("errors", 400, ['order_id' => [__('order.order_not_found')]]);
        }



        //edit or add dish
        if (count($get_order_details) > 0) {
            foreach ($get_order_details as $order_details) {
                $get_invoice_details = InvoiceDetails::where(['details_id' => $order_details->id, 'type' => 'dish', 'invoice_id' => $check_invoice->id])->first();
                if ($get_invoice_details) {
                    //edit
                    $caluclate_dish = $this->CalculateItemOrder($order_id, [$order_details->id], [], "dish", 0); //type = dish or addon or order
                    $get_invoice_details->dish_size_id = $order_details->dish_size_id;
                    $get_invoice_details->note = $order_details->note;
                    $get_invoice_details->quantity = $order_details->quantity;
                    $get_invoice_details->tax = $caluclate_dish['tax'];;
                    $get_invoice_details->service_fees = $caluclate_dish['service_fees'];;
                    $get_invoice_details->coupon_value = $caluclate_dish['coupon_value'];
                    $get_invoice_details->total_before_tax = $caluclate_dish['total_before_tax'];
                    $get_invoice_details->total_before_coupon = $caluclate_dish['total_before_coupon'];
                    $get_invoice_details->total_after_tax = $caluclate_dish['total_after_tax'];
                    $get_invoice_details->modified_by = $order_details->modify_by;
                    $get_invoice_details->save();
                } else {
                    //add
                    $caluclate_dish = $this->CalculateItemOrder($order_id, [$order_details->id], [], "dish", 0); //type = dish or addon or order
                    $add_invoice_details = new InvoiceDetails();
                    $add_invoice_details->invoice_id = $check_invoice->id;
                    $add_invoice_details->details_id = $order_details->id;
                    $add_invoice_details->dish_size_id = $order_details->dish_size_id;
                    $add_invoice_details->type = "dish";
                    $add_invoice_details->note = $order_details->note;
                    $add_invoice_details->quantity = $order_details->quantity;
                    $add_invoice_details->tax = $caluclate_dish['tax'];;
                    $add_invoice_details->service_fees = $caluclate_dish['service_fees'];
                    $add_invoice_details->coupon_value = $caluclate_dish['coupon_value'];
                    $add_invoice_details->total_before_tax = $caluclate_dish['total_before_tax'];
                    $add_invoice_details->total_before_coupon = $caluclate_dish['total_before_coupon'];
                    $add_invoice_details->total_after_tax = $caluclate_dish['total_after_tax'];
                    $add_invoice_details->created_by = $order_details->created_by;
                    $add_invoice_details->save();
                }
            }
        }

        //edit or add addons
        if (count($get_order_addons) > 0) {
            foreach ($get_order_addons as $order_addons) {
                $get_invoice_details = InvoiceDetails::where(['details_id' => $order_addons->id, 'type' => 'addon', 'invoice_id' => $check_invoice->id])->first();
                if ($get_invoice_details) {
                    //edit
                    $caluclate_addon = $this->CalculateItemOrder($order_id, [], [$order_addons->id], "addon", 0); //type = dish or addon or order
                    $get_invoice_details->quantity = $order_addons->quantity;
                    $get_invoice_details->tax = $caluclate_addon['tax'];
                    $get_invoice_details->service_fees = $caluclate_addon['service_fees'];;
                    $get_invoice_details->coupon_value = $caluclate_addon['coupon_value'];
                    $get_invoice_details->total_before_tax = $caluclate_addon['total_before_tax'];
                    $get_invoice_details->total_before_coupon = $caluclate_addon['total_before_coupon'];
                    $get_invoice_details->total_after_tax = $caluclate_addon['total_after_tax'];
                    $get_invoice_details->modified_by = $order_addons->modify_by;
                    $get_invoice_details->save();
                } else {
                    //add
                    $caluclate_addon = $this->CalculateItemOrder($order_id, [], [$order_addons->id], "addon", 0); //type = dish or addon or order
                    $add_invoice_details = new InvoiceDetails();
                    $add_invoice_details->invoice_id = $check_invoice->id;
                    $add_invoice_details->order_detail_id = $order_addons->order_details_id;
                    $add_invoice_details->details_id = $order_addons->id;
                    $add_invoice_details->type = "addon";
                    $add_invoice_details->quantity = $order_addons->quantity;
                    $add_invoice_details->tax = $caluclate_addon['tax'];;
                    $add_invoice_details->service_fees = $caluclate_addon['service_fees'];;
                    $add_invoice_details->coupon_value = $caluclate_addon['coupon_value'];
                    $add_invoice_details->total_before_tax = $caluclate_addon['total_before_tax'];
                    $add_invoice_details->total_before_coupon = $caluclate_addon['total_before_coupon'];
                    $add_invoice_details->total_after_tax = $caluclate_addon['total_after_tax'];
                    $add_invoice_details->created_by = $order_addons->created_by;
                    $add_invoice_details->save();
                }
            }
        }

        //edit invoice
        $order_transaction = null;
        if ($order_type == "order") {
            $order_transaction = OrderTransaction::where('order_id', $orderId)->latest()->first();
        } else {
            $order_transaction = TableReservationTransaction::where('table_reservation_id', $orderId)->latest()->first();
        }
        $caluclate_order = $this->CalculateItemOrder($orderId, $get_order_details_ids, $get_order_addons_ids, "order", 0); //type = dish or addon or order

        $check_invoice->note = $order_type == "order" ? $check_order->note : $check_order->notes;
        $check_invoice->status = ($invoice_type == 'credit_note')
            ? 'paid'
            : ($order_transaction != null
                ? $order_transaction->payment_status
                : null);
        $check_invoice->service_fees = $order_type == "order" ? $caluclate_order['service_fees'] : 0;
        $check_invoice->delivery_fees = $order_type == "order" ? $caluclate_order['delivery_fees'] : 0;
        $check_invoice->tax_percentage = $order_type == "order" ? ($check_order->tax_percentage != null ? $check_order->tax_percentage : 0) : 0;
        $check_invoice->service_percentage = $order_type == "order" ? ($check_order->service_percentage != null ? $check_order->service_percentage : 0) : 0;
        $check_invoice->coupon_value = $order_type == "order" ? $caluclate_order['coupon_value'] : 0;
        $check_invoice->total_before_tax = $order_type == "order" ? $caluclate_order['total_before_tax'] : ($order_transaction != null ? $order_transaction->paid : null);
        $check_invoice->total_before_coupon = $order_type == "order" ? $caluclate_order['total_before_coupon'] : ($order_transaction != null ? $order_transaction->paid : null);
        $check_invoice->total_after_tax = $order_type == "order" ? $caluclate_order['total_after_tax'] : ($order_transaction != null ? $order_transaction->paid : null);
        $check_invoice->tax = $order_type == "order" ? $caluclate_order['tax'] : 0;
        $check_invoice->make_type = $check_order->make_type;
        $check_invoice->modified_by = $check_order->modify_by;
        $check_invoice->save();

        return RespondWithSuccessMsg(__('order.order_edit_successfully'));

        // }catch(Exception $e) {
        //     DB::rollBack();
        //     return RespondWithBadRequestWithData(['error' => $e->getMessage()]);
        // }
    }

    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();

            if ((!$admin)) {
                return RespondWithBadRequest($lang, 4);
            }

            $query = Invoice::with(['invoiceDetails', 'orders'])
                ->where('invoice_type', 'invoice');

            App::setLocale($lang);

            $invoices = $query->get();
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->whereHas('orders', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
                    $invoices = $query->get();
                }
            }

            return $invoices;
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
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

            $query = Invoice::with(['invoiceDetails', 'orders', 'orders.orderDetails.dish', 'orders.orderAddons.Addon.addons', 'orders.orderTransactions'])
                ->where('id', $id)->first();
            $paymentMethods = [];

            foreach ($query->orders->orderTransactions as $transaction) {
                $paymentMethods[] = $transaction->payment_method;
            }

            $uniqueMethods = array_unique($paymentMethods);
            $paymentmethod = implode('-', $uniqueMethods);
            // dd($paymentmethod);
            App::setLocale($lang);
            $data = [
                'query' => $query,
                'paymentmethod' => $paymentmethod
            ];

            return $data;
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function merge_invoice_details($invoice_id)
    {
        $check_invoice = Invoice::where('id', $invoice_id)
            ->with([
                'invoiceDetails.orderDetail.dish',
                'invoiceDetails.orderDetail.dishSize',
                'invoiceDetails.orderAddon.addon',
                'invoiceDetails.orderAddon.orderDetail'
            ])
            ->first();

        if (! $check_invoice) {
            return respondError("errors", 400, ['invoice_id' => [__('order.invoice_not_found')]]);
        }

        $dishes = [];
        $dishGroups = [];
        $addonGroups = [];

        // Group invoice details by order_detail_id
        foreach ($check_invoice->invoiceDetails as $item) {
            if ($item->type === 'dish' && $item->orderDetail && $item->orderDetail->dish) {
                if ($item->invoices->invoice_type == "invoice") {
                    if ($item->status != "cancel") {
                        $orderDetailId = $item->details_id;
                        $dishGroups[$orderDetailId] = $item;
                    }
                } else {
                    $orderDetailId = $item->details_id;
                    $dishGroups[$orderDetailId] = $item;
                }
            }

            if ($item->type === 'addon' && $item->orderAddon && $item->orderAddon->addon) {
                $orderDetailId = $item->orderAddon->order_details_id;
                if ($item->invoices->invoice_type == "invoice") {
                    if ($item->status != "cancel") {
                        if (!isset($addonGroups[$orderDetailId])) {
                            $addonGroups[$orderDetailId] = [];
                        }
                        $addonGroups[$orderDetailId][] = $item;
                    }
                } else {
                    if (!isset($addonGroups[$orderDetailId])) {
                        $addonGroups[$orderDetailId] = [];
                    }
                    $addonGroups[$orderDetailId][] = $item;
                }
            }
        }


        // Process each dish with its associated addons
        foreach ($dishGroups as $orderDetailId => $dishItem) {
            $dishId = $dishItem->orderDetail->dish->id;
            $sizeName = $dishItem->orderDetail->dishSize->name ?? null;
            $noteText = trim((string) $dishItem->note);

            // Get addon IDs for this order detail
            $addonIds = [];
            if (isset($addonGroups[$orderDetailId])) {
                foreach ($addonGroups[$orderDetailId] as $addonItem) {
                    $addonIds[] = $addonItem->orderAddon->addon->id;
                }
            }
            sort($addonIds);

            $addonKey = empty($addonIds) ? 'NO_ADDONS' : implode(',', $addonIds);
            $uniqueKey = $dishId . '|' . $sizeName . '|' . $noteText . '|' . $addonKey;

            if (!isset($dishes[$uniqueKey])) {
                $dishes[$uniqueKey] = [
                    'dish_id' => $dishId,
                    'name' => $dishItem->orderDetail->dish->name,
                    'quantity' => $dishItem->quantity,
                    'coupon_id' => $dishItem->coupon_id,
                    'size' => $sizeName,
                    'price_before_tax' => $dishItem->total_before_tax,
                    'price_after_tax' => $dishItem->total_after_tax,
                    'coupon_value' => $dishItem->coupon_value,
                    'coupon_title' => $dishItem->coupon?->title,
                    'note' => $dishItem->note,
                    'addons' => []
                ];

                // Add addons to the dish
                if (isset($addonGroups[$orderDetailId])) {
                    foreach ($addonGroups[$orderDetailId] as $addonItem) {
                        $dishes[$uniqueKey]['addons'][] = [
                            'addon_id' => $addonItem->orderAddon->Addon?->addons?->id,
                            'dish_id' => $dishId,
                            'name' => $addonItem->orderAddon->Addon?->addons?->name,
                            'quantity' => $addonItem->quantity,
                            'price_before_tax' => $addonItem->total_before_tax,
                            'price_after_tax' => $addonItem->total_after_tax,
                            'coupon_value' => $addonItem->coupon_value,
                            'note' => $addonItem->note,
                        ];

                        $dishes[$uniqueKey]['price_before_tax'] += $addonItem->total_before_tax;
                        $dishes[$uniqueKey]['price_after_tax'] += $addonItem->total_after_tax;
                        $dishes[$uniqueKey]['coupon_value'] += $addonItem->coupon_value;
                    }
                }
            } else {
                // Merge with existing dish
                $dishes[$uniqueKey]['quantity'] += $dishItem->quantity;
                $dishes[$uniqueKey]['price_before_tax'] += $dishItem->total_before_tax;
                $dishes[$uniqueKey]['price_after_tax'] += $dishItem->total_after_tax;
                $dishes[$uniqueKey]['coupon_value'] += $dishItem->coupon_value;

                // Merge addon quantities
                if (isset($addonGroups[$orderDetailId])) {
                    foreach ($addonGroups[$orderDetailId] as $index => $addonItem) {
                        if (isset($dishes[$uniqueKey]['addons'][$index])) {
                            $dishes[$uniqueKey]['addons'][$index]['quantity'] += $addonItem->quantity;
                            $dishes[$uniqueKey]['addons'][$index]['price_before_tax'] += $addonItem->total_before_tax;
                            $dishes[$uniqueKey]['addons'][$index]['price_after_tax'] += $addonItem->total_after_tax;
                            $dishes[$uniqueKey]['addons'][$index]['coupon_value'] += $addonItem->coupon_value;
                        }
                    }
                }
            }
        }

        return ['dishes' => array_values($dishes)];
    }
}
