<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Models\Table;
use App\Models\TableReservation;
use App\Models\Setting;
use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuAddon;
use App\Models\BranchMenuAddonCategory;
use App\Models\BranchMenuSize;
use App\Models\ClientAddress;
use App\Models\FloorPartition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use App\Services\ClientServices\ReservationService;
use App\Services\SettingsServices\BranchService;
use App\Services\ClientServices\OrderService;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Expr\Cast\Double;

class TableReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $reservationService;
    protected $branchService;
    protected $lang;
    protected $checkToken;
    protected $orderService;

    public function __construct(ReservationService $reservationService, BranchService $branchService, OrderService $orderService)
    {
        $this->reservationService = $reservationService;
        $this->branchService = $branchService;
        $this->lang =  app()->getLocale();
        $this->checkToken = true;
        $this->orderService = $orderService;
    }

    public function trackReservation(Request $request, $reservationId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = $user = auth('api')->user();

        $reservations = TableReservation::where('id', $reservationId)
            ->where('client_id', $user->id)
            ->whereIn('status', ['pending', 'confirm'])
            ->with([
                'branch.country',
                'tables',
                'transaction',
                'order.orderDetails.dish',
                'order.orderDetails.dishSize',
                'order.orderAddons.Addon.addons',
                'order.orderTransactions',
                'order.coupon',
                'order.branch.country'
            ])
            ->get();

        if ($reservations->isEmpty()) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.'],
                'data' => null
            ], 200);
        }

        $responseData = $reservations->map(function ($reservation) use ($lang) {
            $reservationTracking = [
                'reservation_id' => $reservation->id,
                'reservation_number' => $reservation->reservation_number,
                'created_at' => $reservation->created_at,
                'status' => $reservation->status,
                'reservation_type' => $reservation->reservation_type,
                'time_from' => $reservation->time_from ? $reservation->time_from->format('h:i A') : null,
                'time_to' => $reservation->time_to ? $reservation->time_to->format('h:i A') : null,
            ];

            $reservationDetails = [
                'table_number' => $reservation->tables->table_number,
                'floor_partition_name' => $lang === 'ar' ? $reservation->floorPartition?->name_ar : $reservation->floorPartition?->name_en,
                'date' => $reservation->date ? $reservation->date->format('Y-m-d') : null,
                'adult' => $reservation->adult,
                'kids' => $reservation->kids,
                'men' => $reservation->men,
                'women' => $reservation->women,
                'personal_type' => $reservation->personal_type,
                'table_type' => $reservation->tables->type == 1 ? ($lang === 'ar' ? 'داخلي' : 'Inside') : ($lang === 'ar' ? 'خارجي' : 'Outside'),
                'branch_name' => $lang === 'ar' ? $reservation->branch->name_ar ?? null : $reservation->branch->name_en ?? null,
                'branch_address' => $lang === 'ar' ? $reservation->branch->address_ar : $reservation->branch->address_en,
            ];

            $transactionDetails = [];
            if ($reservation->reservation_type === 'without' && $reservation->transaction) {
                $transaction = $reservation->transaction;
                $transactionDetails = [[
                    'transaction_id' => $transaction->id,
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => $transaction->paid,
                    'created_at' => $transaction->created_at,
                ]];
            } elseif ($reservation->reservation_type === 'with' && $reservation->order && $reservation->order->orderTransactions) {
                $transactionDetails = $reservation->order->orderTransactions->map(function ($transaction) use ($lang) {
                    return [
                        'transaction_id' => $transaction->id,
                        'payment_status' => $transaction->payment_status,
                        'payment_method' => $transaction->payment_method,
                        'paid' => $transaction->paid,
                        'created_at' => $transaction->created_at,
                    ];
                })->values()->toArray();
            }

            $orderDetails = null;
            $orderSummary = null;
            $orderItemsCount = 0;

            if ($reservation->reservation_type === 'with' && $reservation->order) {
                $orderDetails = $reservation->order->orderDetails->map(function ($detail) use ($lang, $reservation) {
                    $addons = $reservation->order->orderAddons->filter(
                        fn($addon) => $addon->order_details_id == $detail->id &&
                            $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
                    );

                    if ($reservation->order->tax_application == 0) {
                        $orderDetailTotal = $detail->price_befor_tax;
                        $addonsTotal = $addons->sum(function ($addon) {
                            return $addon->price_before_tax;
                        });
                    } else {
                        $orderDetailTotal = $detail->price_after_tax;
                        $addonsTotal = $addons->sum(function ($addon) {
                            return $addon->price_after_tax;
                        });
                    }
                    $total = $orderDetailTotal + $addonsTotal;

                    return [
                        'dish_id' => $detail->dish_id,
                        'dish_name' => $lang === 'ar' ? $detail->dish->name_ar ?? null : $detail->dish->name_en ?? null,
                        'size_id' => $detail->dish_size_id ?? null,
                        'size' => $detail->dish_size_id
                            ? ($lang === 'ar' ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                            : null,
                        'quantity' => $detail->quantity,
                        'total_dish_price' => $total,
                        'note' => $detail->note,
                        'addons' => $addons->map(function ($addon) use ($lang, $reservation) {
                            return [
                                'addon_category_id' => $addon->Addon->addon_category_id,
                                'addon_id' => $addon->Addon->addon_id,
                                'addon_name' => $lang === 'ar' ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                                'price' => $reservation->order->tax_application == 0
                                    ? $addon->price_before_tax
                                    : $addon->price_after_tax
                            ];
                        })->values(),
                    ];
                })->values();

                $basePrice = $reservation->order->total_price_befor_tax;
                $couponDiscount = 0;
                if ($reservation->order->coupon_id && $reservation->order->coupon) {
                    if ($reservation->order->coupon->type === 'percentage') {
                        $couponDiscount = ($basePrice * $reservation->order->coupon->value) / 100;
                    } elseif ($reservation->order->coupon->type === 'fixed') {
                        $couponDiscount = $reservation->order->coupon->value;
                    }
                }

                $subTotalPrice = $basePrice + $couponDiscount;
                if ($reservation->order->tax_application == 1) {
                    $subTotalPrice += $reservation->order->tax_value;
                }

                $orderSummary = [
                    'order_number' => $reservation->order->order_number,
                    'subtotal_price' => $subTotalPrice,
                    'service_fees' => $reservation->order->service_fees ?? null,
                    'tax_value' => $reservation->order->tax_value ?? null,
                    'tax_apply' => !empty($reservation->order->tax_value) || $reservation->order->tax_value != 0,
                    'tax_application' => $reservation->order->tax_application == 1,
                    'tax_percentage' => $reservation->order->total_price_befor_tax > 0
                        ? (float)number_format(($reservation->order->tax_value / $reservation->order->total_price_befor_tax) * 100, 2)
                        : 0,
                    'coupon_id' => $reservation->order->coupon_id ?? null,
                    'coupon_code' => $reservation->order->coupon?->code ?? null,
                    'coupon_value' => $couponDiscount > 0 ? (float)$couponDiscount : null,
                    'total_price' => $reservation->order->total_price_after_tax,
                    'preparation_appointment' => app()->getLocale() === 'ar'
                        ? ($reservation->order->preparation_appointment === 'at' ? 'عند الوصول' : ($reservation->order->preparation_appointment === 'before' ? 'قبل الوصول' : null))
                        : $reservation->order->preparation_appointment ?? null,
                ];

                $orderItemsCount = $reservation->order->orderDetails->sum('quantity');
            }

            $currencySymbol = $reservation->branch?->country?->currency_symbol ?? 'ج.م';

            $contact = [
                'branch_id' => $reservation->branch->id ?? null,
                'branch_name' => $lang === 'ar' ? $reservation->branch->name_ar ?? null : $reservation->branch->name_en ?? null,
                'branch_phone' => $reservation->branch->phone ?? null,
            ];

            return [
                'reservation_tracking' => $reservationTracking,
                'reservation_details' => $reservationDetails,
                'transaction_details' => $transactionDetails,
                'order_items_count' => $orderItemsCount,
                'order_details' => $orderDetails,
                'order_summary' => $orderSummary,
                'currency_symbol' => $currencySymbol,
                'contact' => $contact,
            ];
        });

        $response = [
            'reservationData' => $responseData,
        ];

        return ResponseWithSuccessData($lang, $response, 1);
    }
    protected function getCancellationDetails($reservation, $settings, $isWithinCancelWindow)
    {
        $details = [
            'is_within_window' => $isWithinCancelWindow,
            'will_charge' => false,
            'charge_percentage' => 0,
            'charge_reason' => '',
            'message' => ''
        ];

        if ($isWithinCancelWindow) {
            $details['message'] = __('validation.cancelWithinWindow');
            return $details;
        }

        // Handle outside cancellation window
        $details['charge_reason'] = __('validation.lateCancellation');

        if ($reservation->reservation_type === 'without') {
            $policy = $settings->deposit_without_order_deduction_policy ?? 'none';
            $percentage = $settings->deposit_without_order_deduction_percentage ?? 0;
        } else {
            $policy = $settings->deposit_with_order_deduction_policy ?? 'none';
            $percentage = $settings->deposit_with_order_deduction_percentage ?? 0;
        }

        if ($policy === 'full') {
            $details['will_charge'] = true;
            $details['charge_percentage'] = 100;
            $details['message'] = __('validation.cancelFullCharge');
        } elseif ($policy === 'part') {
            $details['will_charge'] = true;
            $details['charge_percentage'] = $percentage;
            $details['message'] = __('validation.cancelPartialCharge', ['percentage' => $percentage]);
        } else {
            $details['message'] = __('validation.cancelNoCharge');
        }

        return $details;
    }
    protected function isWithinCancelWindow($reservation, $settings)
    {
        $reservationDate = Carbon::parse($reservation->date)->startOfDay();
        $today = now()->startOfDay();

        if ($reservationDate->greaterThan($today)) {
            return true;
        }
        if ($reservationDate->lessThan($today)) {
            return false;
        }
        $cancelationWindow = $settings->table_cancelation_time_allowed ?? 0;
        $reservationTime = Carbon::parse($reservation->time_from);
        $currentTime = now();

        return $currentTime->lessThanOrEqualTo(
            $reservationTime->copy()->subMinutes($cancelationWindow)
        );
    }
    protected function handleWithoutOrderCancellation($reservation, $settings, $isWithinCancelWindow)
    {
        $reservation->update([
            'status' => 'cancel',
            'cancellation_reason' => 'reservation cancelled by client'
        ]);

        if ($reservation->transaction->payment_status === 'unpaid') {
            if (!$isWithinCancelWindow) {
                $reservation->client_flag = 1;
            }
        } elseif ($reservation->transaction->payment_status === 'part') {
            $this->processWithoutOrderRefund($reservation, $settings, $isWithinCancelWindow);
        }
    }
    protected function processWithoutOrderRefund($reservation, $settings, $isWithinCancelWindow)
    {
        if ($isWithinCancelWindow) {
            $refundAmount = $reservation->transaction->paid;
        } else {
            $refundAmount = $this->calculateRefundAmount(
                $reservation->transaction->paid,
                $settings->deposit_without_order_deduction_policy,
                $settings->deposit_without_order_deduction_percentage
            );
        }

        $reservation->transaction()->update([
            'is_refund' => '1',
            'refund' => $refundAmount,
        ]);
    }
    protected function handleWithOrderCancellation($reservation, $settings, $isWithinCancelWindow)
    {
        $reservation->update([
            'status' => 'cancel',
            'cancellation_reason' => 'reservation cancelled by client'
        ]);

        $reservation->order()->update([
            'status' => 'cancelled',
            'print_status' => 'cancelled'
        ]);

        $reservation->order->tracking()->create([
            'order_id' => $reservation->order->id,
            'order_status' => 'cancelled'
        ]);

        $transaction = $reservation->order->transaction;

        if ($transaction->payment_status === 'unpaid') {
            if (!$isWithinCancelWindow) {
                $reservation->client_flag = 1;
            }
            $transaction->update(['refund' => 0]);
        } elseif (in_array($transaction->payment_status, ['part', 'paid'])) {
            $this->processWithOrderRefund($transaction, $settings, $isWithinCancelWindow);
        }
    }
    protected function processWithOrderRefund($transaction, $settings, $isWithinCancelWindow)
    {
        if ($isWithinCancelWindow) {
            $refundAmount = $transaction->paid;
        } else {
            if ($transaction->payment_status === 'part') {
                $refundAmount = $this->calculateRefundAmount(
                    $transaction->paid,
                    $settings->deposit_with_order_deduction_policy,
                    $settings->deposit_with_order_deduction_percentage
                );
            } elseif ($transaction->payment_status === 'paid') {
                $refundAmount = $this->calculateRefundAmount(
                    $transaction->paid,
                    $settings->full_paid_order_deduction_policy,
                    $settings->full_paid_order_deduction_percentage
                );
            }
        }

        $transaction->update([
            'is_refund' => '1',
            'refund' => $refundAmount,
        ]);
    }
    protected function calculateRefundAmount($paidAmount, $policy, $percentage)
    {
        if ($policy === 'none') {
            return $paidAmount;
        } elseif ($policy === 'full') {
            return 0;
        } else {
            return $paidAmount - ($percentage / 100) * $paidAmount;
        }
    }
    public function cancelReservation(Request $request, $reservationId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = $user = auth('api')->user();

        $reservation = TableReservation::with('order', 'transaction', 'branch.branchSettings')
            ->where('client_id', $user->id)
            ->where('id', $reservationId)
            ->first();

        if (!$reservation) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.'],
                'data' => null
            ], 200);
        }

        if ($reservation->status !== 'confirm') {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Cancellation not allowed for this reservation status' : 'لا يُسمح بإلغاء الحجز بهذه الحالة.',
                'errorData' => ['error' => $lang == 'en' ? 'Cancellation not allowed' : 'لا يُسمح بالإلغاء.'],
                'data' => null
            ], 200);
        }

        $settings = $reservation->branch->branchSettings;
        $isWithinCancelWindow = $this->isWithinCancelWindow($reservation, $settings);
        $cancellationDetails = $this->getCancellationDetails($reservation, $settings, $isWithinCancelWindow);

        if ($reservation->reservation_type === 'without') {
            $this->handleWithoutOrderCancellation($reservation, $settings, $isWithinCancelWindow);
        } elseif ($reservation->reservation_type === 'with') {
            $this->handleWithOrderCancellation($reservation, $settings, $isWithinCancelWindow);
        }

        $responseData = [
            'reservation_id' => $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'status' => 'cancel',
            'cancellation_details' => [
                'is_within_window' => $cancellationDetails['is_within_window'],
                'will_charge' => $cancellationDetails['will_charge'],
                'charge_percentage' => $cancellationDetails['charge_percentage'],
                'charge_reason' => $cancellationDetails['charge_reason'],
                'message' => $cancellationDetails['message']
            ]
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    public function reservationPaymentDetails(Request $request, $reservationId)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $user = $user = auth('api')->user();

        $reservation = TableReservation::where('client_id', $user->id)
            ->where('id', $reservationId)
            ->with([
                'branch.country',
                'tables',
                'transaction',
                'order.orderDetails.dish',
                'order.orderDetails.dishSize',
                'order.orderAddons.Addon.addons',
                'order.orderTransactions',
                'order.coupon',
                'order.branch.country'
            ])
            ->first();

        if (!$reservation) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.',
                'errorData' => ['error' => $lang == 'en' ? 'Reservation does not exist' : 'الحجز غير موجود.'],
                'data' => null
            ], 200);
        }

        $reservationDetails = [
            'reservation_id' => $reservation->id,
            'reservation_number' => $reservation->reservation_number,
            'created_at' => $reservation->created_at,
            'branch_address' => $lang === 'ar' ? $reservation->branch->address_ar : $reservation->branch->address_en,
            'status' => $reservation->status,
            'table_number' => $reservation->tables->table_number,
            'reservation_type' => $reservation->reservation_type,
            'date' => $reservation->date ? $reservation->date->format('Y-m-d') : null,
            'time_from' => $reservation->time_from ? $reservation->time_from->format('h:i A') : null,
            'time_to' => $reservation->time_to ? $reservation->time_to->format('h:i A') : null,
            'adult' => $reservation->adult,
            'kids' => $reservation->kids,
            'table_type' => $reservation->tables->type == 1 ? ($lang === 'ar' ? 'داخلي' : 'Inside') : ($lang === 'ar' ? 'خارجي' : 'Outside'),
        ];

        $transactionDetails = [];
        if ($reservation->reservation_type === 'without' && $reservation->transaction) {
            $transaction = $reservation->transaction;
            $transactionDetails = [[
                'transaction_id' => $transaction->id,
                'payment_status' => $transaction->payment_status,
                'payment_method' => $transaction->payment_method,
                'paid' => $transaction->paid,
                'created_at' => $transaction->created_at,
            ]];
        } elseif ($reservation->reservation_type === 'with' && $reservation->order && $reservation->order->orderTransactions) {
            $transactionDetails = $reservation->order->orderTransactions->map(function ($transaction) use ($lang) {
                return [
                    'transaction_id' => $transaction->id,
                    'payment_status' => $transaction->payment_status,
                    'payment_method' => $transaction->payment_method,
                    'paid' => $transaction->paid,
                    'created_at' => $transaction->created_at,
                ];
            })->values()->toArray();
        }

        $orderDetails = null;
        $orderSummary = null;
        $orderItemsCount = 0;

        if ($reservation->reservation_type === 'with' && $reservation->order) {
            $orderDetails = $reservation->order->orderDetails->map(function ($detail) use ($lang, $reservation) {
                $addons = $reservation->order->orderAddons->filter(
                    fn($addon) => $addon->order_details_id == $detail->id &&
                        $addon->Addon?->addons?->{($lang === 'ar' ? 'name_ar' : 'name_en')}
                );

                if ($reservation->order->tax_application == 0) {
                    $orderDetailTotal = $detail->price_befor_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_before_tax;
                    });
                } else {
                    $orderDetailTotal = $detail->price_after_tax;
                    $addonsTotal = $addons->sum(function ($addon) {
                        return $addon->price_after_tax;
                    });
                }
                $total = $orderDetailTotal + $addonsTotal;

                return [
                    'dish_id' => $detail->dish_id,
                    'dish_name' => $lang === 'ar' ? $detail->dish->name_ar ?? null : $detail->dish->name_en ?? null,
                    'size_id' => $detail->dish_size_id ?? null,
                    'size' => $detail->dish_size_id
                        ? ($lang === 'ar' ? $detail->dishSize->size_name_ar ?? null : $detail->dishSize->size_name_en ?? null)
                        : null,
                    'quantity' => $detail->quantity,
                    'total_dish_price' => $total,
                    'note' => $detail->note,
                    'addons' => $addons->map(function ($addon) use ($lang, $reservation) {
                        return [
                            'addon_category_id' => $addon->Addon->addon_category_id,
                            'addon_id' => $addon->Addon->addon_id,
                            'addon_name' => $lang === 'ar' ? $addon->Addon->addons->name_ar ?? null : $addon->Addon->addons->name_en ?? null,
                            'price' => $reservation->order->tax_application == 0
                                ? $addon->price_before_tax
                                : $addon->price_after_tax
                        ];
                    })->values(),
                ];
            })->values();

            $subTotalPrice = $reservation->order->total_price_befor_tax;
            if ($reservation->order->tax_application == 1) {
                $subTotalPrice += $reservation->order->tax_value;
            }

            $orderSummary = [
                'order_number' => $reservation->order->order_number,
                'subtotal_price' => $subTotalPrice,
                'delivery_fees' => $reservation->order->delivery_fees ?? null,
                'service_fees' => $reservation->order->service_fees ?? null,
                'tax_value' => $reservation->order->tax_value ?? null,
                'tax_apply' => !empty($reservation->order->tax_value) || $reservation->order->tax_value != 0,
                'tax_application' => $reservation->order->tax_application == 1,
                'tax_percentage' => (float)number_format(($reservation->order->tax_value / $reservation->order->total_price_befor_tax) * 100, 2),
                'coupon_id' => $reservation->order->coupon_id ?? null,
                'coupon_code' => $reservation->order->coupon?->code ?? null,
                'coupon_value' => $reservation->order->coupon_value !== null ? (float)$reservation->order->coupon_value : null,
                'total_price' => $reservation->order->total_price_after_tax,
                'remaining_price' => $reservation->order->orderTransactions->first() && $reservation->order->orderTransactions->first()->payment_status === 'part'
                    ? ($reservation->order->tax_application == 0
                        ? $reservation->order->total_price_befor_tax - $reservation->order->orderTransactions->first()->paid
                        : $reservation->order->total_price_befor_tax + $reservation->order->tax_value - $reservation->order->orderTransactions->first()->paid)
                    : null,
                'preparation_appointment' => app()->getLocale() === 'ar'
                    ? ($reservation->order->preparation_appointment === 'at' ? 'عند الوصول' : ($reservation->order->preparation_appointment === 'before' ? 'قبل الوصول' : null))
                    : $reservation->order->preparation_appointment ?? null,
            ];

            $orderItemsCount = $reservation->order->orderDetails->sum('quantity');
        }

        $currencySymbol = $reservation->branch?->country?->currency_symbol ?? 'ج.م';

        $responseData = [
            'reservation_details' => $reservationDetails,
            'transaction_details' => $transactionDetails,
            'order_items_count' => $orderItemsCount,
            'order_details' => $orderDetails,
            'order_summary' => $orderSummary,
            'currency_symbol' => $currencySymbol,
        ];

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////

    public function getSession(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validationTableSession = $this->reservationService->validationTableSession($request);

        if ($validationTableSession !== true) {
            return $validationTableSession;
        }

        $get_tables = $this->reservationService->get_table_session($request->count, $request->branch_id, $request->date, $request->floor_partition_id, "encode");
        if ($get_tables) {
            return ResponseWithSuccessData($lang, $get_tables, 1);
        } else {
            $message = "no table avilable";
            return respondEmptyData($message, 200, $message);
        }
    }

    // public function checkReservation(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     $validationResponse = $this->reservationService->validateReservationRequest($request, "check");
    //     $responseData = $validationResponse->original;
    //     if (!$responseData['status']) {
    //         if ($responseData['validation_type']) {
    //             // dd($responseData);
    //             return respondError($responseData['message'], 400, $responseData['errorData']);
    //         } else {
    //             return respondErrorData('errors', 400, $responseData['errorData']['error']);
    //         }
    //     }


    //     $barnch_details = Branch::where('id', $request->branch_id)->first();
    //     $branch_id = $request->branch_id;

    //     $floor_partition = FloorPartition::where('id', $request->floor_partition_id)
    //         ->whereHas('floors', function ($q) use ($request) {
    //             $q->where('branch_id', $request->branch_id);
    //         })->first();


    //     $payment_policy = $this->branchService->payment_policy($request->branch_id);
    //     if (!$payment_policy) {
    //         $message = "no branch policy found";
    //         return response()->json([
    //             'code' => 400,
    //             'status' => false,
    //             'message' => $message,
    //             'data' => null,
    //             'errorData' => ['error' => $message]
    //         ], 200);
    //     }

    //     $reservation_policy = $this->branchService->reservation_policy();
    //     $table_details = Table::where('id', $request->table_id)->first();

    //     $result = [
    //         'branch_id' => (int) $request->branch_id,
    //         'branch_name' => $barnch_details->name,
    //         'branch_address' => $barnch_details->address,
    //         'floor_partition_id' => (int) $request->floor_partition_id,
    //         'floor_partition_name' => $floor_partition->name,
    //         'table_id' => (int) $request->table_id,
    //         'table_name' => $table_details->name,
    //         'table_image' => $table_details->image,
    //         'date' => $request->date,
    //         'time_from' => $request->time_from,
    //         'time_to' => $request->time_to,
    //         'adult' => (int) $request->adult,
    //         'kids' => (int) $request->kids,
    //         'men' => (int) $request->men,
    //         'women' => (int) $request->women,
    //         'notes' => $request->notes,
    //         'personal_type' => $request->personal_type,
    //         'deposit' => (float) getBranchSettings($request->branch_id, 'table_reservation_deposit'),
    //         'currency' => $barnch_details->country ? $barnch_details->country->currency_symbol : null,
    //         'payment_policy' => $payment_policy,
    //         'reservation_policy' => $reservation_policy,
    //         'reservation_type' => 'without'
    //     ];

    //     return ResponseWithSuccessData($lang, $result, 1);
    // }

    // public function placeReservation(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');
    //     App::setLocale($lang);

    //     if (!CheckToken()) {
    //         return RespondWithBadRequest($lang, 5);
    //     }

    //     // $payment_method_val = array('cash', 'credit_card', 'online');
    //     // if (!in_array($request->payment_method, $payment_method_val)) {
    //     //     $message = "no payment method found";
    //     //     return respondErrorData($message, 400, $message);
    //     // }

    //     // $deposit_setting = (float) getBranchSettings($request->branch_id, 'table_reservation_deposit');
    //     // if (!$deposit_setting) {
    //     //     $message = "no deposit found";
    //     //     return respondErrorData($message, 400, $message);
    //     // }

    //     // if ($deposit_setting != $request->deposit) {
    //     //     $message = "the deposit is not correct, Please check it again";
    //     //     return respondErrorData($message, 400, $message);
    //     // }

    //     $request['reservation_type'] = 'without';
    //     $validationResponse = $this->reservationService->validateReservationRequest($request, "store");
    //     $responseData = $validationResponse->original;
    //     if (!$responseData['status']) {
    //         if ($responseData['validation_type']) {
    //             // dd($responseData);
    //             return respondError($responseData['message'], 400, $responseData['errorData']);
    //         } else {
    //             return respondErrorData('errors', 400, $responseData['errorData']['error']);
    //         }
    //     }

    //     return $this->storeReservation($request);
    // }

    private function storeReservation(Request $request)
    {
        $client_id = auth('api')->user()->id ?? null;
        $lang = $request->header('lang', 'ar');

        $branch = Branch::find($request->branch_id);
        $floor_partition = FloorPartition::find($request->floor_partition_id);

        $result = [
            'branch_id' => $request->branch_id,
            'branch_name' => $branch->name,
            'branch_address' => $branch->address,
            'floor_partition_id' => $request->floor_partition_id,
            'floor_partition_name' => $floor_partition->name,
            'tableId' => $request->table_id,
            'date' => $request->date,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'adult' => $request->adult,
            'kids' => $request->kids,
            'men' => $request->men,
            'women' => $request->women,
            'notes' => $request->notes,
            'personal_type' => $request->personal_type,
            'deposit' => ($request->payment_method == "deposit_required" ? (float) getBranchSettings($request->branch_id, 'table_reservation_deposit') : 0),
            'reservation_type' => "without",
            'client_id' => $client_id,
            'lang' => $lang,
            'payment_method' => $request->payment_method,
        ];
        return $this->reservationService->store($result, true, null);
    }

    public function checkout(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $request['lang'] = $lang;
        $request['make_type'] = 'app';
        $reservation_type = $request->reservation_type;
        if ($request['reservation_type'] == 'with') {
            $validated = $this->orderService->validate($request->all(), $lang, $this->checkToken, 'api', 'check');
            $responseData = $validated->original;

            if (!$responseData['status']) {
                return $validated; // Respond with validation error if any
            }
        }
        $validationResponse = $this->reservationService->validateReservationRequest($request, 'check');
        $responseData = $validationResponse->original;
        if (!$responseData['status']) {
            if ($responseData['validation_type']) {
                // dd($responseData);
                return respondError($responseData['message'], 400, $responseData['errorData']);
            } else {
                return respondErrorData('errors', 400, $responseData['errorData']['error']);
            }
        }
        $branch = Branch::find($request->branch_id);
        $branch_id = $branch->id;

        $table_details = Table::where('id', $request->table_id)->first();
        $floor_partition = FloorPartition::find($request->floor_partition_id);
        if ($request['reservation_type'] == 'with') {
            $order_type = "reservation_with_order";
        } else {
            $order_type = "reservation_without_order";
        }
        $payment_policy = getBranchPolicyPayment($branch_id, $order_type);
        $currency_symbol = $branch->country->currency_symbol;
        $deposit = ($request['reservation_type'] == "without" ? (float) getBranchSettings($request->branch_id, 'table_reservation_deposit') : 0);
        $reservation_policy = $this->branchService->reservation_policy();
        $lang = $request->header('lang', 'ar');

        if ($request['reservation_type'] == 'with') {
            $client_id = auth('api')->user()->id ?? null;

            $tax_application = getBranchSettings($branch_id, 'tax_application');
            $tax_percentage = getBranchSettings($branch_id, 'tax_percentage');
            $coupon_application = getBranchSettings($branch_id, 'coupon_application');
            $service_fees_value =    getBranchSettings($branch_id, 'service_fees');
            $service_fees_type =  getBranchSettings($branch_id, 'service_fees_type');
            $delivery_time = getBranchSettings($branch_id, 'delivery_time');
            $tax_apply = getBranchSettings($branch_id, 'tax_apply');
            $delivery = getBranchSettings($branch_id, 'delivery_fees');
            $note = $request->note;
            $DataOrderDetails = $request->items;
            $coupon_code = $request->coupon_code;
            $type = $request->type;
            $coupon = null;
            if (!empty($coupon_code)) {
                $coupon = GetCouponId($request['coupon_code'], $branch_id);
            }



            $total_price_before_tax = 0;
            $total_price_after_tax = 0;
            $tax_value_total = 0;
            $total_service = 0;
            // Check if the address is within the delivery radius
            // Process the order items
            $total_price_before_discount = 0;
            $items = [];

            // Step 1: Calculate item prices first (dish + addon)
            foreach ($DataOrderDetails as $index => $DataOrderDetail) {
                $Dish = BranchMenu::where('id', $DataOrderDetail['dish_id'])
                    ->where('branch_id', $branch_id)
                    ->where('is_active', 1)
                    ->first();

                if ($Dish) {
                    $items[$index]['dish_name'] = $Dish->dish->name;
                    $price = $Dish->dish->has_sizes
                        ? optional(BranchMenuSize::find($DataOrderDetail['sizeId']))->price ?? 0
                        : $Dish->price;

                    if (isset($DataOrderDetail['sizeId'])) {
                        $size = BranchMenuSize::find($DataOrderDetail['sizeId']);
                        $items[$index]['size_name'] = $size->dishSizes->name;
                    }

                    $dish_price = $price * $DataOrderDetail['quantity'];
                    $addon_price = 0;

                    if (!empty($DataOrderDetail['addon_categories'])) {
                        foreach ($DataOrderDetail['addon_categories'] as $addon_category) {
                            foreach ($addon_category['addon'] as $i => $addon_id) {
                                $addon = BranchMenuAddon::find($addon_id);
                                $items[$index]['addons'][$i]['addon_name'] = $addon->dishAddons->addons->name;
                                if ($addon) {
                                    $addon_price += $addon->price * $DataOrderDetail['quantity'];
                                }
                            }
                        }
                    }

                    $total_price_before_discount += ($dish_price + $addon_price);

                    $items[$index]['dish_price'] = $dish_price + $addon_price;
                    $items[$index]['quantity'] = $DataOrderDetail['quantity'];
                    $items[$index]['note'] = $DataOrderDetail['note'] ?? null;
                }
            }

            // Step 2: Apply coupon
            $coupon_value = 0;
            $total_after_coupon = $total_price_before_discount;

            if ($coupon) {
                $coupon_value = calcCoupon($total_price_before_discount, $coupon);
                $total_after_coupon = applyCoupon($total_price_before_discount, $coupon);
            }

            // Step 3: Service fee
            $total_service = $service_fees_type != 'fixed'
                ? ($total_after_coupon * ($service_fees_value / 100))
                : $service_fees_value;

            // Step 4: Tax
            $tax_value_total = $tax_application == 1
                ? CalculateTax($tax_percentage, $total_after_coupon + $total_service)
                : ($total_after_coupon + $total_service) * ($tax_percentage / 100);

            // Step 5: Final total
            $final_total = $total_after_coupon + $total_service + $tax_value_total;


            // Prepare response
            $response['order'] = [
                'currency_symbol' => $currency_symbol,
                'note' => $note,
                'coupon_code' => $coupon ? $coupon_code : null,
                'total' => round($final_total, 2),
                'tax_value' => round($tax_value_total, 2),
                'tax_percentage' => "{$tax_percentage}%",
                'sub_total' => $total_price_before_discount,
                'total_after_coupon' => $total_after_coupon,
                'fees' => $total_service,
                'coupon_value' => $coupon ? $coupon_value : 0,
                'coupon_type' => $coupon ? $coupon->type : null,
                'deposit' => (float)  round($final_total * (getBranchSettings($request->branch_id, 'order_reservation_deposit') / 100), 2),
                'items' => $items,
                'cash_limit' => getCashPaymentPolicy($branch_id),
                'service_fees_value' => $service_fees_value,
                'service_fees_type' => $service_fees_type

            ];
        } else {
            $response['order'] = (object)[];
            $response['reservation']['deposit'] = (float) getBranchSettings($request->branch_id, 'table_reservation_deposit');
            $note = $request->note;
        }
        $response['reservation'] = [
            'branch_id' => $request->branch_id,
            'branch_name' => $branch->name,
            'branch_address' => $branch->address,
            'floor_partition_id' => $request->floor_partition_id,
            'floor_partition_name' => $floor_partition->name,
            'table_id' => $request->table_id,
            'date' => $request->date,
            'table_name' => $table_details->name,
            'table_image' => $table_details->image,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'adult' => $request->adult,
            'kids' => $request->kids,
            'men' => $request->men,
            'women' => $request->women,
            'deposit' => $deposit,
            'personal_type' => $request->personal_type,
            'payment_policy' => $payment_policy,
            'currency' => $branch->country ? $branch->country->currency_symbol : null,
            'reservation_type' => $reservation_type,
            'reservation_policy' => $reservation_policy,
            'notes' => $request['reservation_type'] == 'without' ? $note : null,
        ];
        return ResponseWithSuccessData($lang, $response, 1);
    }

    public  function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // if (!CheckToken()) {
        //     return RespondWithBadRequest($lang, 5);
        // }
        $request['lang'] = $lang;
        $request['make_type'] = 'app';
        $branchId = $request['branch_id'];

        $validationResponse = $this->reservationService->validateReservationRequest($request, 'store');
        $responseData = $validationResponse->original;
        if (!$responseData['status']) {
            if ($responseData['validation_type']) {
                // dd($responseData);
                return respondError($responseData['message'], 400, $responseData['errorData']);
            } else {
                return respondErrorData('errors', 400, $responseData['errorData']['error']);
            }
        }

        if ($request['reservation_type'] == 'with') {
            $response = $this->orderService->store_v2($request->all(), $this->checkToken, 'api');
            $responseData = $response->original;
            if (!$responseData['status']) {
                if ($responseData['validation_type']) {
                    return respondError($responseData['message'], 400, $responseData['errorData']);
                } else {
                    return respondErrorData('errors', 400, $responseData['errorData']['error']);
                }
            }

            $reservationData = [
                'tableId' => $request['table_id'] ?? null,
                'branch_id' => $branchId,
                'floor_partition_id' => $request['floor_partition_id'] ?? null,
                'client_id' => $created_by ?? null,
                'date' => $request['date'] ?? null,
                'time_from' => $request['time_from'] ?? null,
                'time_to' => $request['time_to'] ?? null,
                'reservation_type' => 'with',
                'adult' => $request['adult'] ?? null,
                'kids' => $request['kids'] ?? null,
                'men' => $request['men'] ?? null,
                'women' => $request['women'] ?? null,
                'personal_type' => $request['personal_type'] ?? null,
                'lang' => $lang,
                'payment_method' => $request['payment_method'] ?? null
            ];
            $this->reservationService->store($reservationData, $this->checkToken, $responseData['data']['order_id']);
            return ResponseWithSuccessData($lang, ['order_id' => $responseData['data']['order_id']], 1);
        } else {
            return $this->storeReservation($request);
        }
    }


    //////////////////////////////////////////////////////////////////////////////////
    public function index(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $table_reservations = TableReservation::with('tables')->get();
            return ResponseWithSuccessData($lang, $table_reservations, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function add(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $new_reservation_date = "";
            $reservation_time_from = "";
            $reservation_time_to = "";
            $validateData = Validator::make($request->all(), [
                'table_id' => 'required|integer|exists:tables,id',
                'client_id' => 'required|integer|exists:users,id',
                'date' => 'required|date|after:yesterday',
                'time_from' => 'required|date_format:H:i',
                'time_to' => 'required|date_format:H:i|after:time_from'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $setting_details = Setting::find(1);
            if (!$setting_details) {
                return  RespondWithBadRequestNotExist();
            }

            // if($setting_details->reservation_time_type == 2){
            //     $new_reservation_date = $request->time_date + $setting_details->reservation_time;
            // }else{
            //     $reservation_time_from = Carbon::parse($request->time_from)->addMinutes($setting_details->reservation_time)->format('H:i:s');
            //     $reservation_time_to = Carbon::parse($request->time_to)->addMinutes($setting_details->reservation_time)->format('H:i:s');
            // }

            $check_branch = Table::where('id', $request->table_id)->with('floorPartitions.floors.branches')->first();
            $time_from = Carbon::parse($request->time_from);
            $time_to = Carbon::parse($request->time_to);
            $opening_hour = Carbon::parse($check_branch->floorPartitions->floors->branches->opening_hour);
            $closing_hour = Carbon::parse($check_branch->floorPartitions->floors->branches->closing_hour);


            if ($opening_hour > $time_from) {
                return RespondWithBadRequestNotAvailable($lang, 9);
            }

            if ($closing_hour < $time_to) {
                return RespondWithBadRequestNotAvailable($lang, 9);
            }

            $check_tabel_reservations = TableReservation::where('table_id', $request->table_id)->where('date', $request->date)->get();
            if ($check_tabel_reservations) {
                foreach ($check_tabel_reservations as $check_tabel_reservation) {
                    $reservation_from = Carbon::parse($check_tabel_reservation->time_from);

                    if ($reservation_from == $time_from) {
                        return RespondWithBadRequestNotAvailable($lang, 9);
                    }

                    if ($setting_details->reservation_time_type == 1) {
                        $reservation_to = Carbon::parse($check_tabel_reservation->time_to);
                        $diff_minutes = $reservation_to->diffInMinutes($time_from);
                        if ($diff_minutes < $setting_details->reservation_time) {
                            return RespondWithBadRequestNotAvailable($lang, 9);
                        }
                    }

                    // if($check_tabel_reservation->status != 3){
                    //     return RespondWithBadRequestNotAvailable($lang, 9);
                    // }
                }
            }

            $user_id = Auth::guard('api')->user()->id;
            $table_reservation = new TableReservation();
            $table_reservation->table_id = $request->table_id;
            $table_reservation->client_id = $request->client_id;
            $table_reservation->date = $request->date;
            $table_reservation->time_from = $request->time_from;
            $table_reservation->time_to = $request->time_to;
            $table_reservation->confirmed = 1;
            $table_reservation->status = 1;
            $table_reservation->created_by = $user_id;
            $table_reservation->save();

            return ResponseWithSuccessData($lang, $table_reservation, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function edit(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:table_reservations,id',
                'table_id' => 'required|integer|exists:tables,id',
                'client_id' => 'required|integer|exists:users,id',
                'date' => 'required|date|after:yesterday',
                'time_from' => 'required|date_format:H:i',
                'time_to' => 'required|date_format:H:i'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $check_tabel_reservation = TableReservation::where('table_id', $request->table_id)->first();
            if ($check_tabel_reservation) {
                //not empty
                if ($check_tabel_reservation->date == $request->date && $check_tabel_reservation->status != 1) {
                    return RespondWithBadRequestNotAvailable($lang, 9);
                }

                //not client
                if ($check_tabel_reservation->client_id != $request->client_id) {
                    return RespondWithBadRequestNotAvailable($lang, 9);
                }

                //not penddeing
                if ($check_tabel_reservation->confirmed != 1) {
                    return  RespondWithBadRequestNotHavePermeation($lang, 9);
                }
            }

            $user_id = Auth::guard('api')->user()->id;
            $table_reservation = TableReservation::find($request->id);
            $table_reservation->table_id = $request->table_id;
            $table_reservation->client_id = $request->client_id;
            $table_reservation->date = $request->date;
            $table_reservation->time_from = $request->time_from;
            $table_reservation->time_to = $request->time_to;
            $table_reservation->modified_by = $user_id;
            $table_reservation->save();

            return ResponseWithSuccessData($lang, $table_reservation, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function delete(Request $request, $id)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $user_id = Auth::guard('api')->user()->id;

            $table_reservation = TableReservation::find($request->id);
            if (!$table_reservation) {
                return  RespondWithBadRequestNotExist();
            }

            if ($table_reservation) {
                //not empty
                if ($table_reservation->status != 1) {
                    return RespondWithBadRequestNotAvailable($lang, 9);
                }

                //not penddeing
                if ($table_reservation->confirmed != 1) {
                    return  RespondWithBadRequestNotHavePermeation($lang, 9);
                }
            }

            $table_reservation->deleted_by = $user_id;
            $table_reservation->save();

            $table_reservation->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function change_status(Request $request)
    {
        try {
            $lang =  $request->header('lang', 'en');
            $validateData = Validator::make($request->all(), [
                'id' => 'required|exists:table_reservations,id',
                'confirmed' => 'required|integer',
                'confirmed_date' => 'required|date|after:yesterday',
                'confirmed_time' => 'required|date_format:H:i'
            ]);

            if ($validateData->fails()) {
                return RespondWithBadRequestWithData($validateData->errors());
            }

            $user_id = Auth::guard('api')->user()->id;
            $table_reservations = TableReservation::find($request->id);

            if (!$table_reservations) {
                return  RespondWithBadRequestNotExist();
            }

            // if($table_reservations->date < $request->confirmed_date){
            //     return  RespondWithBadRequestNotDate();
            // }

            $table_reservations->confirmed = $request->confirmed;
            $table_reservations->confirmed_date = $request->confirmed_date;
            $table_reservations->confirmed_time = $request->confirmed_time;
            $table_reservations->confirmed_by = $user_id;
            $table_reservations->modified_by = $user_id;
            $table_reservations->save();

            return ResponseWithSuccessData($lang, $table_reservations, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
