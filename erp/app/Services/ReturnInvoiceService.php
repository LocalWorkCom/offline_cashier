<?php


namespace App\Services;

use App\Models\EmployeeOpeningBalance;
use App\Models\Invoice;
use App\Models\InvoiceDetails;
use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class ReturnInvoiceService
{
    public function index(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();
            $employee = auth('employee')->user();

            if ((!$admin) && (!$employee)) {
                return RespondWithBadRequest($lang, 4);
            }

            // Start with credit note invoices
            $query = Invoice::with(['invoiceDetails', 'orders'])
                ->where('invoice_type', 'credit_note');

            App::setLocale($lang);

            // Get the results after applying the credit_note filter
            $invoices = $query;
            if ($admin && auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    // Apply branch filter to the original query
                    $query->whereHas('orders', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
                    // Get the filtered results
                    // $invoices = $query->get();
                }
            }
            elseif ($employee && auth('employee')->user()->hasRole('Branch_Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    // Apply branch filter to the original query
                    $query->whereHas('orders', function ($q) use ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    });
                    // Get the filtered results
                    // $invoices = $query->get();
                }
            }

            return $invoices;
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 400);
        }
    }
    public function show(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $admin = auth('admin')->user();
            $employee = auth('employee')->user();

            if ((!$admin) && (!$employee)) {
                return RespondWithBadRequest($lang, 4);
            }

            // Start with credit note invoices
            $query = Invoice::with(['invoiceDetails', 'orders', 'orders.branch', 'orders.orderDetails.dish', 'orders.orderAddons.Addon.addons'])
                ->where('id', $id)->first();

            App::setLocale($lang);

            // Get the results after applying the credit_note filter
            return $query;
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function getCurrentBalance(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('employee')->user();

            if ((!$user) || ($user->flag != 'cashier')) {
                return RespondWithBadRequest($lang, 4);
            }

            App::setLocale($lang);

            $validator = Validator::make($request->all(), [
                'cashier_machine_id' => 'required|numeric|min:1|exists:cashier_machines,id',
                'employee_schedule_id' => 'nullable|numeric|min:1|exists:employee_schedules,id',
                //                'balance_id' => 'nullable|numeric|min:1|exists:employee_opening_balances,id',
                'shift_start' => 'nullable|date_format:H:i:s',
                'shift_end' => 'nullable|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }

            $today = Carbon::now()->format('Y-m-d');

            $start = $today . ' ' . $request->shift_start;
            $end = $today . ' ' . $request->shift_end;

            $lastBalance = EmployeeOpeningBalance::latest()->where('cashier_machine_id', $request->cashier_machine_id)->first(); //Eman added ->where('cashier_machine_id', $request->cashier_machine_id)


            if (isset($lastBalance)) {
                $lastBalance_created_at = $lastBalance->created_at;
                $lastBalance_open_cash = $lastBalance->open_cash;
                $lastBalance_open_visa = $lastBalance->open_visa;
                $lastBalance_close_cash = $lastBalance->close_cash;
                $lastBalance_close_visa = $lastBalance->close_visa;
            } else {
                $lastBalance_created_at = Carbon::now();
                $lastBalance_open_cash = 0;
                $lastBalance_open_visa = 0;
                $lastBalance_close_cash = 0;
                $lastBalance_close_visa = 0;
            }

            $cashierMachineId = $request->cashier_machine_id;
            $cashTotalall = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                // $query->where('created_by', $user->id)
                $query->where('invoice_type', 'invoice'); //Eman
                // $query->where('status', '!=', 'cancelled');

                //                        ->where('status', 'completed')
                //                        ->where('print_status', 'done');
            })->whereHas('order', function ($q) use ($cashierMachineId) {
                $q->where('cashier_machine_id', $cashierMachineId);
            })
                //sara and hend
                ->where('paid_at', '>', $lastBalance_created_at)
                ->where('payment_method', 'cash')
                ->where('payment_status', 'paid')
                //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                ->selectRaw('SUM(paid) as total')
                ->value('total');

            $cashTotalRefund = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                // $query->where('created_by', $user->id)
                $query->where('invoice_type', 'credit_note'); //Eman
                // $query->where('status', '!=', 'cancelled');

                //                        ->where('status', 'completed')
                //                        ->where('print_status', 'done');
            })->whereHas('order', function ($q) use ($cashierMachineId) {
                $q->where('cashier_machine_id', $cashierMachineId);
            })
                ->where('paid_at', '>', $lastBalance_created_at)
                ->where('payment_method', 'cash')
                ->where('payment_status', 'paid')
                ->where('is_refund', 1)
                //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                ->selectRaw('SUM(refund) as total')
                ->value('total');
            $cashTotal = $cashTotalall - ($cashTotalRefund);

            $visaTotalall = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                // $query->where('created_by', $user->id)
                //Eman
                $query->where('invoice_type', 'invoice');
                //    ->whereBetween('updated_at', [$start, $end]);
                //                        ->where('status', 'completed')
                //                        ->where('print_status', 'done');
            })->whereHas('order', function ($q) use ($cashierMachineId) {
                $q->where('cashier_machine_id', $cashierMachineId);
            })->where('paid_at', '>', $lastBalance_created_at)
                ->where('payment_method', 'credit')
                ->where('payment_status', 'paid')
                //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                ->selectRaw('SUM(paid) as total')
                ->value('total');
            $visaTotalRefund = OrderTransaction::whereHas('invoice', function ($query) use ($user, $start, $end, $lastBalance_created_at) {
                // $query->where('created_by', $user->id)
                $query->where('invoice_type', 'credit_note'); //Eman
                // $query->where('status', '!=', 'cancelled');

                //    ->whereBetween('updated_at', [$start, $end]);
                //                        ->where('status', 'completed')
                //                        ->where('print_status', 'done');
            })->whereHas('order', function ($q) use ($cashierMachineId) {
                $q->where('cashier_machine_id', $cashierMachineId);
            })
                ->where('paid_at', '>', $lastBalance_created_at)
                ->where('payment_method', 'credit')
                ->where('payment_status', 'paid')
                ->where('is_refund', 1)
                //                    ->selectRaw('SUM(paid - (CASE WHEN is_refund = 1 THEN COALESCE(refund,0) ELSE 0 END)) as total')
                ->selectRaw('SUM(refund) as total')
                ->value('total');

            $visaTotal = $visaTotalall - $visaTotalRefund;


            if (isset($lastBalance) && $lastBalance->type == 1) {
                $totalClosingCash = $cashTotal + $lastBalance_open_cash; //Eman
                $totalClosingVisa =  $visaTotal + $lastBalance_open_visa; //Eman
            } else {
                $totalClosingCash =  $lastBalance_close_cash; //Eman
                $totalClosingVisa =   $lastBalance_close_visa; //Eman
            }
            if (isset($lastBalance) && $lastBalance->balance_after_sent_to_safe != 0 && $lastBalance->type == 1) {
                $totalClosingCash = $totalClosingCash - $lastBalance->balance_after_sent_to_safe; //Eman
                $totalClosingVisa = abs($lastBalance->balance_after_sent_to_safe_visa - $totalClosingVisa); //Eman
                // $currentLastBalanceCash = $lastBalance->balance_after_sent_to_safe;
                // $currentLastBalanceCash = abs($currentLastBalanceCash - ($cashTotal + $lastBalance->open_cash));

                // dd($cashTotal);
            }

            // $totalClosingCash =  $cashTotal;
            $total = [
                [
                    'name' => 'cash',
                    'value' => $this->normalizeZero(round($totalClosingCash ?? 0, 2)),
                ],
                [
                    'name' => 'visa',
                    'value' => $this->normalizeZero(round($totalClosingVisa ?? 0, 2),)
                ],
                [
                    'name' => 'total',
                    'value' => round($totalClosingCash + $totalClosingVisa, 2),
                ],
            ];
            return ResponseWithSuccessData($lang, $total, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 404);
        }
    }
    private function normalizeZero($value)
    {
        return (abs($value) < 0.001) ? 0 : round($value, 2);
    }
}
