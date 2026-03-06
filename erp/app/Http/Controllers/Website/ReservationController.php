<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\FloorPartition;
use App\Models\PolicyPaymentReservation;
use App\Models\TableReservation;
use App\Models\TableReservationTransaction;
use App\Services\ClientServices\ReservationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ReservationController extends Controller
{
    protected $reservationService;
    protected $lang;
    protected $checkToken;
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
        $this->lang =  app()->getLocale();
        $this->checkToken = false;
    }
    public function getSession(Request $request)
    {
        return $this->reservationService->get_table_session($request->count, $request->branch_id, $request->reservation_date, $request->floor_partition, $request->type);
    }
    public function tableConfirmation(Request $request)
    {
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);
        $currency = Branch::find($branchId)->country->currency_symbol;
        return view('website.table-reservation.confirmation', compact('branchId', 'currency'));
    }
    public function tableCheckout(Request $request)
    {
        if (!auth('client')->check()) {
            return redirect()->route('home');
        }
        $branchId = $request->cookie('branch_id') ?? ($_COOKIE['branch_id'] ?? null);
        $reservation_policy = PolicyPaymentReservation::first();
        $currency = Branch::find($branchId)->country->currency_symbol;
        return view('website.table-reservation.checkout', compact('branchId', 'reservation_policy', 'currency'));
    }
    public function storeTableReservation(Request $request)
    {
        $request['lang'] = $this->lang;
        $client_id = auth('client')->user()->id ?? null;
        $request['client_id'] = $client_id;
        $validationResponse = $this->reservationService->validateReservationRequest($request,'store');
        $responseData = $validationResponse->original;

        // if (!$responseData['status']) {
        //     $validationErrors = $responseData['data'];
        //     return redirect()->back()->withErrors($validationErrors)->withInput();
        // }

        if (!$responseData['status']) {
            if ($responseData['validation_type']) {
                return response()->json([
                    'success' => false,
                    'message' => $responseData['errorData'],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $responseData['errorData']['error'],
                ]);
            }
        }


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
            // 'tableId' => $request->tableId,
            'date' => $request->date,
            'time_from' => $request->time_from,
            'time_to' => $request->time_to,
            'adult' => $request->adult,
            'kids' => $request->kids,
            'men' => $request->men,
            'women' => $request->women,
            'notes' => $request->notes,
            'personal_type' => $request->personal_type,
            'deposit' => (float) getBranchSettings($request->branch_id, 'table_reservation_deposit'),
            'reservation_type' => "without",
            'client_id' => $client_id,
            'lang' => $lang,
            'payment_method' => $request->payment_method
        ];

        $response = $this->reservationService->store($result, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }

        $request['payment_method'] = $request['payment_method'] ?? $request['payment_method2'];

        $online = false;
        if (in_array($request['payment_method'], ['deposit_required'])) {
            // $reserve = "without";
            // return redirect()->route('myfatoorah-payment', [$responseData['data']['id'], $reserve]);
            $online = true;
        }
        
        return response()->json([
            'success' => true,
            'message' => $responseData['message'],
            'data' => $responseData['data'],
            'online' => $online
        ]);
        // if ($request->payment_method == "credit_card") {
        //     return redirect()->route('myfatoorah-payment', $responseData['data']['order_id']);
        // } else {
        // $message = $responseData['message'];
        //  return redirect()->route('orders.tracking')->with('message', $message);

        // }
    }
    public function validateReservationCancellation(Request $request)
    {
        $reservation = TableReservation::with('order', 'transaction', 'branch.branchSettings')
            ->find($request->input('reservation_id'));
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.orderNotFound'),
            ]);
        }
        if ($reservation->status !== 'confirm') {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.notAllowed'),
            ]);
        }
        $settings = $reservation->branch->branchSettings;
        $isWithinCancelWindow = $this->isWithinCancelWindow($reservation, $settings);
        $cancellationDetails = $this->getCancellationDetails($reservation, $settings, $isWithinCancelWindow);
        return response()->json([
            'status' => 'success',
            'can_cancel' => true,
            'details' => $cancellationDetails,
        ]);
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
    public function cancelReservation(Request $request)
    {
        $reservation = TableReservation::with('order', 'transaction', 'branch.branchSettings')
            ->find($request->input('reservation_id'));
        if (!$reservation) {
            return response()->json([
                'status' => 'error',
                'message' => __('validation.orderNotFound'),
            ]);
        }
        $settings = $reservation->branch->branchSettings;
        $isWithinCancelWindow = $this->isWithinCancelWindow($reservation, $settings);
        $cancellationDetails = $this->getCancellationDetails($reservation, $settings, $isWithinCancelWindow);
        if ($reservation->reservation_type === 'without') {
            $this->handleWithoutOrderCancellation($reservation, $settings, $isWithinCancelWindow);
        } elseif ($reservation->reservation_type === 'with') {
            $this->handleWithOrderCancellation($reservation, $settings, $isWithinCancelWindow);
        }
        return response()->json([
            'status' => 'success',
            'message' => $cancellationDetails['message'],
            'details' => $cancellationDetails,
        ]);
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
}
