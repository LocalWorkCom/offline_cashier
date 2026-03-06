<?php

namespace App\Services\ProcurementServices;

use App\Models\Employee;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use App\Models\PurchasingBudget;
use App\Models\PurchasingBudgetLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchasingBudgetService
{
    public function index(Request $request)
    {
        $query = PurchasingBudget::query();
        if ($request->filled('from')) {
            $query->where('created_at', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('updated_at', $request->to);
        }
        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        $query->with('logs');

        // Optional: Order by newest first
        $query->orderBy('year', 'desc')->orderBy('month', 'desc');
        return $query;
    }
    public function show(Request $request, $id)
    {
        $query = PurchasingBudget::find($id);
        return $query;
    }
    public function store(Request $request)
    {

        $PurchasingBudget = new PurchasingBudget();
        $PurchasingBudget->month = $request->month;
        $PurchasingBudget->year = $request->year;
        $PurchasingBudget->base_amount = $request->base_amount;
        $PurchasingBudget->notes = $request->notes;
        $PurchasingBudget->increase_amount = 0;
        $PurchasingBudget->increase_count = 0;
        $PurchasingBudget->remaining_amount = $request->base_amount;
        $PurchasingBudget->created_by = authActionSave()['by'];
        $PurchasingBudget->is_active = 1;
        $PurchasingBudget->save();
        $this->saveHistory(new Request([
            'purchasing_budget_id' => $PurchasingBudget->id,
            'amount' => $request->base_amount,
            'type' => 'creation',
            'reference' => 'Initial budget amount',
            'previous_remaining' => 0,
            'new_remaining' => $request->base_amount,
        ]));
        return $PurchasingBudget;
    }
    public function saveHistory(Request $request)
    {
        $PurchasingBudgetLog = new PurchasingBudgetLog();
        $PurchasingBudgetLog->purchasing_budget_id = $request->purchasing_budget_id;
        $PurchasingBudgetLog->amount = $request->amount;
        $PurchasingBudgetLog->type = $request->type;
        $PurchasingBudgetLog->reference = $request->reference;
        $PurchasingBudgetLog->previous_remaining = $request->previous_remaining;
        $PurchasingBudgetLog->new_remaining = $request->new_remaining;
        $PurchasingBudgetLog->created_by = authActionSave()['by'];
        $PurchasingBudgetLog->save();

        return $PurchasingBudgetLog;
    }
    public function storeNotify(Request $request)
    {
        $employee = auth(guard: 'employee')->user();  // Authenticated employee
        $lang = $request->header('lang', 'ar');


        foreach ($request->increase_ids as $increase_id) {
            $PurchasingBudgetLog = PurchasingBudgetLog::find($increase_id);

            if ($PurchasingBudgetLog) {

                $PurchasingBudgetLog->reasone = $request->reasone;
                $PurchasingBudgetLog->prch_manager_notify = $request->check_notify;
                $PurchasingBudgetLog->save();

                // Collect info for notification
                $increaseAmount = $PurchasingBudgetLog->increase_amount;
                $newTotal = $PurchasingBudgetLog->new_total;
                $increaseDate = $PurchasingBudgetLog->created_at->format('Y-m-d');

                // // Finance manager (approved_by)
                // $financeManager = Employee::find($PurchasingBudgetLog->approved_by);
                // $financeManagerNameAr = $financeManager?->name_ar ?? '—';
                // $financeManagerNameEn = $financeManager?->name_en ?? '—';
            }
        }

        if ($request->check_notify) {

            // Get purchase managers in branch
            $purchaseManagers = Employee::whereHas('roles', function ($query) {
                $query->where('name', 'Purchase_Manager');
            })
                ->get();

            foreach ($purchaseManagers as $manager) {
                if ($manager->device_token) {
                    // Arabic content
                    $title_ar = 'زيادة في ميزانية الشراء';
                    $body_ar = "تمت الموافقة على زيادة في ميزانية الشراء.\n"
                        . " مقدار الزيادة: {$increaseAmount}"
                        . "الميزانية الجديدة: {$newTotal}\n"
                        . "التاريخ: {$increaseDate}\n";
                    // . "👤 المدير المالي الموافق: {$financeManagerNameAr}";

                    // English content
                    $title_en = 'Purchase Budget Increased';
                    $body_en = "A purchase budget increase has been approved.\n"
                        . " Increase Amount: {$increaseAmount}\n"
                        . " New Total Budget: {$newTotal}\n"
                        . " Date: {$increaseDate}\n";
                    // . " Finance Manager Approved: {$financeManagerNameEn}";

                    send_push_notification(
                        $manager->device_token,
                        $body_ar,
                        $body_en,
                        $title_ar,
                        $title_en,
                        'purchase_budget',
                        $manager->id,
                        $employee->id,
                        $PurchasingBudgetLog->id,
                        $lang,
                        13
                    );
                }
            }
        }

        return $PurchasingBudgetLog;
    }

    public function update(Request $request, $id)
    {
        $PurchasingBudget = PurchasingBudget::find($id);
        $PurchasingBudget->notes = $request->notes;
        $PurchasingBudget->modified_by = authActionSave()['by'];
        $PurchasingBudget->save();
        return $PurchasingBudget;
    }

    public function increaseBudgetAmount(Request $request, PurchasingBudget $purchasingBudget)
    {
        // Store previous remaining BEFORE update
        $previousRemaining = $purchasingBudget->remaining_amount;

        // Update budget
        $purchasingBudget->increase_amount += $request->increase_amount;
        $purchasingBudget->increase_count += 1;
        $purchasingBudget->remaining_amount += $request->increase_amount;
        $purchasingBudget->modified_by = authActionSave()['by'];
        $purchasingBudget->save();

        // Save the increase history
        $this->saveHistory(new Request([
            'purchasing_budget_id' => $purchasingBudget->id,
            'amount' => $request->increase_amount,
            'type' => 'increase',
            'reference' => $purchasingBudget->notes ?? 'Budget increase',
            'previous_remaining' => $previousRemaining,
            'new_remaining' => $purchasingBudget->remaining_amount,
            'created_by' => authActionSave()['by'],
        ]));

        // Fetch today's increases only
        $today = now()->toDateString();
        $increaseLogs = $purchasingBudget->logs()
            ->where('type', 'increase')
            ->whereDate('created_at', $today)
            ->orderBy('created_at')
            ->get();

        // Determine initial remaining BEFORE any increases
        $initialRemaining = $increaseLogs->first()?->previous_remaining ?? $purchasingBudget->remaining_amount - $request->increase_amount;

        // Sum of all today's increases
        $totalIncrease = $increaseLogs->sum('amount');

        // Creator of the increases (first creator today)
        $createdBy = $increaseLogs->first()?->created_by ?? null;
        $createdByName = optional(Employee::find($createdBy))->first_name . ' ' .  optional(Employee::find($createdBy))->last_name ?? null;

        // Prepare response
        $response = [
            'increases' => $increaseLogs->map(fn($log) => [
                'id' => $log->id,
                'amount' => $log->amount,
                'reference' => $log->reference,
                'previous_remaining' => $log->previous_remaining,
                'new_remaining' => $log->new_remaining,

                'created_by' => $log->created_by,
            ]),
            'total' => [
                'initial_remaining' => $initialRemaining,
                'after_increases' => $purchasingBudget->remaining_amount,
                'total_increase' => $totalIncrease,
                'created_by' => $createdByName,
                'increase_breakdown' => $increaseLogs->pluck('amount'),
                'created_at' => $increaseLogs->first()->created_at,
                'updated_at' => $increaseLogs->first()->updated_at,
            ],
        ];

        return $response;
    }

    public function historyBudget()
    {

        $budgets = PurchasingBudget::where('year',now()->year)->orderBy('created_at', 'asc')->get();

        $entries = $budgets->map(function ($budget) {
            $prevAmount = $budget->base_amount ?? 0;
            $difference = $budget->remaining_amount - $prevAmount;
            $flag = $difference > 0 ? 'increase' : ($difference < 0 ? 'decrease' : 'no_change');

            return [
                'budget_id' => $budget->id,
                'month' => $budget->month,
                'previous_amount' => $prevAmount,
                'new_amount' => $budget->remaining_amount,
                'difference' => abs($difference),
                'flag' => $flag,
                'creator' => $budget->created_by
                    ? $budget->createdByUser->first_name . ' ' . $budget->createdByUser->last_name
                    : null,
                'created_at' => $budget->created_at,
            ];
        });

        $totalPrevious = $entries->sum('previous_amount');
        $totalNew = $entries->sum('new_amount');
        $totalIncrease = $entries->filter(fn($t) => $t['flag'] === 'increase')->sum('difference');
        $totalDecrease = $entries->filter(fn($t) => $t['flag'] === 'decrease')->sum(fn($t) => abs($t['difference']));

        return  [
                'total_previous' => $totalPrevious,
                'total_new' => $totalNew,
                'total_increase' => $totalIncrease,
                'total_decrease' => $totalDecrease,
                'entries' => $entries,
            ];
    }
}
