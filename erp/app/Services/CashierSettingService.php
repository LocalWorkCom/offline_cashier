<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Category;
use App\Models\Branch;
use App\Models\CashierSetting;
use App\Models\CashierSettingLog;
use App\Models\Einvoice;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierSettingService
{
    public function index(Request $request)
    {
        $lang = $request->header('lang') ?? 'ar';
        app()->setLocale($lang);

        $orderTypes = [
            'en' => [
                'Delivery' => 'Delivery',
                'CallCenter' => 'Call Center',
                'Takeaway' => 'Takeaway',
                'Online' => 'Online',
                'dine-in' => 'In Restaurant',
            ],
            'ar' => [
                'Delivery' => 'توصيل',
                'CallCenter' => 'مركز الاتصال',
                'Takeaway' => 'أستلام',
                'Online' => 'عبر الإنترنت',
                'dine-in' => 'في المطعم',
            ]
        ];

        // Get filter inputs
        $date = $request->input('date');
        $amount = $request->input('amount');
        $posname = $request->input('posname');

        // Query with filtering
        $query = Einvoice::with('invoice.orders')->whereHas('invoice.orders', function ($q) use ($date, $amount, $posname) {
            if ($date) {
                $q->whereDate('orders.created_at', $date); // Filter by created_at
            }
            if (!is_null($amount) && is_numeric($amount)) {
                $q->whereNotNull('orders.total_price_after_tax')
                    ->where('orders.total_price_after_tax', '>=', (float) $amount); // Use '>=' for amount filter
            }
            if ($posname) {
                $q->where('orders.cashier_machine_id', $posname); // Filter by POS
            }
        });

        $query = paginateOrGetAll($query, $request, null);
        $result = $query['data'] ?? [];
        $meta = $query['meta'] ?? [];
        $Data = collect($result)?->map(function ($invoice) use ($lang, $orderTypes) {
            if (is_null($invoice->invoice)) {
                return null;
            }

            $orderCreatedAt = Carbon::parse($invoice->invoice->orders->created_at);
            $isOlderThan24h = $orderCreatedAt->diffInHours(now()) > 24;

            return [
                'invoice' =>  [
                    'id' => $invoice->id,
                    'uuid' => $invoice->uuid ?? __('einvoice.nothave'),
                    'status' => $invoice->status ?? __('einvoice.nothave'),
                    'invoice_type' => match ($invoice->invoice_type) {
                        'i' => __('einvoice.Invoice'),
                        'c' => __('einvoice.Credit'),
                        default => $invoice->invoice_type,
                    },
                    'submission_date' => $inv->submission_date ?? __('einvoice.notuploaded'),
                ],
                'invoice_number' => $invoice->invoice->orders->invoice_number ?? null,
                'client_name' => $invoice->invoice->orders->client->name ?? null,
                'branch_name' => $lang === 'ar'
                    ? ($invoice->invoice->orders->Branch->name_ar ?? 'لا يوجد فرع')
                    : ($invoice->invoice->orders->Branch->name_en ?? 'No branch'),
                'total' => $invoice->invoice->orders->total_price_after_tax ?? null,
                'pos' => $invoice->invoice->orders->cashierMachine->name ?? null,
                'order_type' => $orderTypes[$lang][$invoice->invoice->orders->type] ?? $invoice->invoice->orders->type,
                // 'is_older_than_24h' => $isOlderThan24h,
            ];
        })->filter();

        $responseData['data'] = $Data;
        $responseData['meta'] = $meta;
        return $responseData;
    }

    public function createSetting(Request $request)
    {
        $lang = $request->header('lang') ?? 'ar';
        $employees = Employee::where('flag', 'officer')
            ->whereNotIn('id', function ($query) {
                $query->select('employee_id')->from('cashier_settings');
            })
            ->get();
        $data = $employees->map(function ($employee) use ($lang) {
            return [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
            ];
        });
        return $data;
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang') ?? 'ar';
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'branch_id' => 'required|exists:branches,id',
            'pos_ids' => 'required|array',
        ]);

        $data = [];

        foreach ($request->pos_ids as $posId) {
            $data[] = [
                'pos_id' => $posId,
                'min_total' => $request->input("min_total.$posId"),
                'max_total' => $request->input("max_total.$posId"),
                'minnum' => $request->input("minnum.$posId"),
                'maxnum' => $request->input("maxnum.$posId"),
                'auto_run_time' => $request->input("auto_run_time.$posId"),
            ];
            $minTotalKey = "min_total_{$posId}";
            $maxTotalKey = "max_total_{$posId}";
            $minNumKey = "minnum_{$posId}";
            $maxNumKey = "maxnum_{$posId}";
            $autoRunTimeKey = "auto_run_time_{$posId}";
            CashierSettingLog::create([
                'employee_id' => $request->employee_id,
                'branch_id' => $request->branch_id,
                'pos_id' => $posId,
                'min_total' => $request->input($minTotalKey),
                'max_total' => $request->input($maxTotalKey),
                'min_num' => $request->input($minNumKey),
                'max_num' => $request->input($maxNumKey),
                'auto_run_time' => $request->input($autoRunTimeKey),
                'created_at' => now(),
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ]);
            $einvoice = new CashierSetting();
            $einvoice->min_balance = $request->input($minTotalKey);
            $einvoice->max_balance = $request->input($maxTotalKey);
            $einvoice->min_count = $request->input($minNumKey);
            $einvoice->max_count = $request->input($maxNumKey);
            $einvoice->auto_run_time = $request->input($autoRunTimeKey);
            $einvoice->pos_id = $posId;
            $einvoice->employee_id = $request->employee_id;
            $einvoice->created_by = authActionSave()['by'];
            $einvoice->created_by_type = authActionSave()['type'];
            $einvoice->save();
        }
        return true;
    }
    public function setting(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $data = CashierSetting::with(['employee', 'pos.branches'])
            ->orderBy('employee_id')
            ->get();

        $cashierMachine = $data->groupBy('employee_id')->map(function ($settings, $employeeId) use ($lang) {
            $firstSetting   = $settings->first();
            $uniqueBranches = $settings->pluck('pos.branches.name')->unique()->filter();
            $branchName     = $uniqueBranches->count() === 1 ? $uniqueBranches->first() : $uniqueBranches->join(' - ');
            $posNames       = $settings->pluck('pos.name')->filter()->join(' - ') ?: 'N/A';

            return [
                'employee_id'   => $employeeId,
                'employee_name' => $firstSetting->employee->first_name ?? 'N/A',
                'branches'      => $branchName,
                'pos_names'     => $posNames,
            ];
        });

        return $cashierMachine->values();
    }
    public function show(Request $request, $id)
    {
        $cashierSettings = CashierSetting::with('pos')->where('employee_id', $id)->get();

        if ($cashierSettings->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No settings found for this employee',
            ], 404);
        }

        $employeeId = $cashierSettings->first()->employee_id;
        $branchId   = $cashierSettings->first()->pos->branch_id ?? null;

        $posData = $cashierSettings->map(function ($setting) {
            return [
                'setting_id'    => $setting->id,
                'pos_id'        => $setting->pos_id,
                'pos_name'      => $setting->pos->name ?? null,
                'branch_id'     => $setting->pos->branch_id ?? null,
                'min_balance'   => $setting->min_balance,
                'max_balance'   => $setting->max_balance,
                'min_count'     => $setting->min_count,
                'max_count'     => $setting->max_count,
                'auto_run_time' => $setting->auto_run_time,
            ];
        });
        $data = [
            'employee_id' => $employeeId,
            'branch_id'   => $branchId,
            'pos'         => $posData,
        ];

        return $data;
    }
    public function update(Request $request, $id)
    {
        $employeeId = $request->input('employee_id');
        $branchId = $request->input('branch_id');
        $posIds = $request->input('pos_ids', []); // Selected POS IDs
        $existingIds = $request->input('ids', []); // IDs for existing records

        // Handle POS assignments
        foreach ($posIds as $index => $posId) {
            if(($request->input("min_total_" . $posId) > $request->input("max_total_" . $posId)) || ($request->input("minnum_" . $posId) > $request->input("maxnum_" . $posId))) {
                return false;
            }
            // Check if the POS is already assigned to another employee
            $existingAssignment = CashierSetting::where('pos_id', $posId)->first();

            if ($existingAssignment) {
                // If assigned to a different employee, update it
                if ($existingAssignment->employee_id !== $employeeId) {
                    $existingAssignment->employee_id = $employeeId;
                }
            } else {
                // Create a new assignment if none exists
                $existingAssignment = new CashierSetting();
                $existingAssignment->pos_id = $posId;
            }

            // Update or set new values
            $existingAssignment->employee_id = $employeeId;
            $existingAssignment->min_balance = $request->input("min_total_" . $posId);
            $existingAssignment->max_balance = $request->input("max_total_" . $posId);
            $existingAssignment->min_count = $request->input("minnum_" . $posId);
            $existingAssignment->max_count = $request->input("maxnum_" . $posId);
            $existingAssignment->auto_run_time = $request->input("auto_run_time_" . $posId);
            $existingAssignment->created_by = authActionSave()['by'];
            $existingAssignment->created_by_type = authActionSave()['type'];
            $existingAssignment->save();

            // Log the update in CashierSettingLog
            CashierSettingLog::create([
                'employee_id' => $employeeId,
                'branch_id' => $branchId,
                'pos_id' => $posId,
                'min_total' => $request->input("min_total_" . $posId),
                'max_total' => $request->input("max_total_" . $posId),
                'min_num' => $request->input("minnum_" . $posId),
                'max_num' => $request->input("maxnum_" . $posId),
                'auto_run_time' => $request->input("auto_run_time_" . $posId),
                'created_at' => now(),
                'created_by' => authActionSave()['by'],
                'created_by_type' => authActionSave()['type'],
            ]);
        }

        // Remove assignments that are no longer selected
        CashierSetting::where('employee_id', $employeeId)
            ->whereNotIn('pos_id', $posIds)
            ->delete();

        return true;
    }
}
