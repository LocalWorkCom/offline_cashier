<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashierMachine;
use App\Models\CashierSetting;
use App\Models\CashierSettingLog;
use App\Models\Einvoice;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashierSettingController extends Controller
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();

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

        $result = $query->get();
        $Data = $result->map(function ($invoice) use ($lang, $orderTypes) {
            if (is_null($invoice->invoice)) {
                return null;
            }

            $orderCreatedAt = Carbon::parse($invoice->invoice->orders->created_at);
            $isOlderThan24h = $orderCreatedAt->diffInHours(now()) > 24;

            return [
                'invoice' => $invoice,
                'invoice_number' => $invoice->invoice->orders->invoice_number ?? null,
                'client_name' => $invoice->invoice->orders->client->name ?? null,
                'branch_name' => $lang === 'ar'
                    ? ($invoice->invoice->orders->Branch->name_ar ?? 'لا يوجد فرع')
                    : ($invoice->invoice->orders->Branch->name_en ?? 'No branch'),
                'total' => $invoice->invoice->orders->total_price_after_tax ?? null,
                'pos' => $invoice->invoice->orders->cashierMachine->name ?? null,
                'order_type' => $orderTypes[$lang][$invoice->invoice->orders->type] ?? $invoice->invoice->orders->type,
                'is_older_than_24h' => $isOlderThan24h,
            ];
        })->filter();

        return view('dashboard.einvoice.index', compact('Data'));
    }
    public function filter(Request $request)
    {

        $query = DB::table('einvoices')
            ->join('invoices', 'einvoices.invoice_id', '=', 'invoices.id')

            ->join('orders', 'invoices.order_id', '=', 'orders.id')
            ->select('einvoices.*', 'orders.total_price_after_tax', 'orders.created_at', 'orders.cashier_id');

        // Apply filters based on request parameters
        if ($request->filled('date')) {
            $query->whereDate('orders.date', $request->date);
        }

        if ($request->filled('amount')) {
            $query->where('orders.total_price_after_tax', $request->amount);
        }

        if ($request->filled('posname')) {
            $query->where('orders.cashier_id', $request->posname);
        }

        // Fetch filtered results
        $data = $query->paginate(10);

        return view('dashboard.einvoice.cashierSetting.viewSetting', compact('data'));
    }

    public function setting()
    {
        $data = CashierSetting::with(['employee', 'pos.branches'])
            ->orderBy('employee_id')
            ->get();
        return view('dashboard.einvoice.cashierSetting.viewSetting', compact('data'));
    }
    public function getPosByBranch(Request $request)
    {
        // Ensure branch_id is provided
        if (!$request->has('branch_id')) {
            return response()->json(['message' => 'Branch ID is required'], 400);
        }

        // Fetch POS list for the given branch
        $posList = CashierMachine::where('branch_id', $request->branch_id)->get();

        // Fetch related settings from the CashierSetting table
        $posSettings = CashierSetting::whereIn('pos_id', $posList->pluck('id'))->get()->keyBy('pos_id');

        // Get selected POS IDs (handle both string and array types)
        $posSelected = $request->input('posSelected');
        if (is_string($posSelected)) {
            $posSelected = explode(',', $posSelected);
        }

        $posList->each(function ($pos) use ($posSelected, $posSettings) {
            // Mark POS as selected if in the selected list
            $pos->selected = is_array($posSelected) && in_array($pos->id, $posSelected);

            // Attach settings if available
            if ($posSettings->has($pos->id)) {
                $pos->min_total = $posSettings[$pos->id]->min_balance;
                $pos->max_total = $posSettings[$pos->id]->max_balance;
                $pos->minnum = $posSettings[$pos->id]->min_count;
                $pos->maxnum = $posSettings[$pos->id]->max_count;
                $pos->auto_run_time = $posSettings[$pos->id]->auto_run_time;
            } else {
                // Default values if no settings found
                $pos->min_total = null;
                $pos->max_total = null;
                $pos->minnum = null;
                $pos->maxnum = null;
                $pos->auto_run_time = null;
            }
        });

        return response()->json($posList);
    }



    public function createSetting()
    {
        $employees = Employee::where('flag', 'officer')
            ->whereNotIn('id', function ($query) {
                $query->select('employee_id')->from('cashier_settings');
            })
            ->get();
        return view('dashboard.einvoice.cashierSetting.setting', compact('employees'));
    }

    public function getPosForBranch(Request $request)
    {
        $branchId = $request->branch_id;
        $employeeId = $request->employee_id;

        $posList = CashierMachine::where('branch_id', $branchId)->get();

        $assignedPosIds = CashierSetting::where('employee_id', $employeeId)
            ->pluck('pos_id')
            ->toArray();

        $response = $posList->map(function ($pos) use ($assignedPosIds) {
            return [
                'id' => $pos->id,
                'name' => $pos->name,
                'assigned' => in_array($pos->id, $assignedPosIds),
                'min_total' => $pos->min_total ?? 0,
                'max_total' => $pos->max_total ?? 0,
                'minnum' => $pos->minnum ?? 0,
                'maxnum' => $pos->maxnum ?? 0
            ];
        });

        return response()->json($response);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $branchId = $request->input('branch_id');
        $posIds = $request->input('pos_ids', []); // Selected POS IDs
        $existingIds = $request->input('ids', []); // IDs for existing records

        // Handle POS assignments
        foreach ($posIds as $index => $posId) {
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
            $existingAssignment->created_by = auth('admin')->user()->id;
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
                'created_by' => auth('admin')->user()->id,
            ]);
        }

        // Remove assignments that are no longer selected
        CashierSetting::where('employee_id', $employeeId)
            ->whereNotIn('pos_id', $posIds)
            ->delete();

        return redirect()->route('dashboard.einvoices.setting.list')
            ->with('success', 'Records updated successfully');
    }

    public function edit(string $id)
    {
        $cashierSettings = CashierSetting::with('pos')->where('employee_id', $id)->get();
        $branchId = $cashierSettings->first()->pos->branch_id ?? null;
        $branches = Branch::all();
        $posData = $cashierSettings->pluck('pos')->filter()->values();
        $posIds = $posData->pluck('id')->toArray(); // Extract POS IDs for JavaScript use

        return view('dashboard.einvoice.cashierSetting.updateSetting', compact('cashierSettings', 'posData', 'branches', 'branchId', 'posIds'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'branch_id' => 'required|exists:branches,id',
            'pos_ids' => 'required|array',
        ]);

        $data = [];
        // dd($request->all());

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
                'created_by' => auth('admin')->user()->id,
            ]);
            $einvoice = new CashierSetting();
            $einvoice->min_balance = $request->input($minTotalKey);
            $einvoice->max_balance = $request->input($maxTotalKey);
            $einvoice->min_count = $request->input($minNumKey);
            $einvoice->max_count = $request->input($maxNumKey);
            $einvoice->auto_run_time = $request->input($autoRunTimeKey);
            $einvoice->pos_id = $posId;
            $einvoice->employee_id = $request->employee_id;
            $einvoice->created_by = auth('admin')->user()->id;
            $einvoice->save();
        }
        return redirect()->route('dashboard.einvoices.setting.list');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */


    /**
     * Update the specified resource in storage.
     */


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
