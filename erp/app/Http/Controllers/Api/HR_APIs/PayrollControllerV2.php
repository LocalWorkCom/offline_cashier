<?php

namespace App\Http\Controllers\Api\HR_APIs;

use App\Models\PayrollSheet;
use Illuminate\Http\Request;
use App\Models\PayrollSheetItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\HR_Services\PayrollService;

class PayrollControllerV2 extends Controller
{
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'en');
        $user = auth()->user();

        try {
            // Get payroll sheets with draft status and their items FIRST
            $query = $this->payrollService->index($request, false)
                // ->where('status', 'draft')
                ->with(['items']);

            // Apply pagination to the query to get the result BEFORE updating
            $result = paginateOrGetAll($query, $request);

            // THEN update status if HR user with date parameters
            if (($user->hasRole('HR_Manager') || $user->hasRole('HR_Employee')) &&
                $request->has(['start_date', 'end_date'])
            ) {
                $this->updatePayrollSheetsStatus($request->start_date, $request->end_date);
            }

            return ResponseWithSuccessDataPaginated($lang, $result, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Update PayrollSheet and PayrollSheetItem statuses
     */
    private function updatePayrollSheetsStatus($startDate, $endDate)
    {
        // Update PayrollSheet status from draft to review for sheets within the date range
        $updatedSheets = PayrollSheet::where('status', 'draft')
            ->where(function ($query) use ($startDate, $endDate) {
                // Find payroll sheets that fall within the given date range
                $query->where('start_date', '>=', $startDate)
                    ->where('end_date', '<=', $endDate);
            })
            ->update(['status' => 'review']);

        // Update related PayrollSheetItem status from pending to reviewed
        if ($updatedSheets > 0) {
            // Get the IDs of updated payroll sheets within the date range
            $payrollSheetIds = PayrollSheet::where('status', 'review')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where('start_date', '>=', $startDate)
                        ->where('end_date', '<=', $endDate);
                })
                ->pluck('id');

            // Update related payroll sheet items
            DB::table('payroll_sheet_items')
                ->whereIn('payroll_sheet_id', $payrollSheetIds)
                ->where('status', 'pending')
                ->update(['status' => 'reviewed']);
        }

        return $updatedSheets;
    }

    /**
     * Step 1: Generate payroll sheet
     */
    public function generate(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'type'   => 'required',
        ]);

        $sheet = $this->payrollService->generatePayrollSheet(
            $validated['start_date'],
            $validated['end_date'],
            $validated['type']
        );

        return ResponseWithSuccessData($lang, $sheet, 1);
    }

    /**
     * Step 2: Finance approval
     */
    public function approve($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $sheet = PayrollSheet::find($id);
        if (!$sheet) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $approved = $this->payrollService->approveByFinance($sheet);

        // Check if the response is an error response
        if ($approved instanceof \Illuminate\Http\JsonResponse && $approved->getStatusCode() === 400) {
            return $approved; // Return the error response directly
        }

        return ResponseWithSuccessData($lang, $approved, 1);
    }

    /**
     * Step 3: Return for correction
     */
    public function returnCorrection(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        // Manual validation instead of using $request->validate()
        if (!$request->has('comments') || empty($request->comments)) {
            return response()->json([
                "code" => 400,
                "status" => false,
                "message" => "Validation Error.",
                "data" => null,
                "errorData" => [
                    "comments" => [
                        "Comments are required for returning payroll sheet for correction."
                    ]
                ],
                "validation_type" => true
            ], 400);
        }

        $sheet = PayrollSheet::find($id);
        if (!$sheet) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $returned = $this->payrollService->returnForCorrection($sheet, $request->comments);

        // Check if the response is an error response
        if ($returned instanceof \Illuminate\Http\JsonResponse && $returned->getStatusCode() === 400) {
            return $returned; // Return the error response directly
        }

        return ResponseWithSuccessData($lang, $returned, 1);
    }

    /**
     * Step 4: Resubmit after correction
     */
    public function resubmit($id, Request $request)
    {
        $lang = $request->header('lang', 'ar');

        $sheet = PayrollSheet::find($id);
        if (!$sheet) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        $resubmitted = $this->payrollService->resubmit($sheet);
         if ($resubmitted instanceof \Illuminate\Http\JsonResponse && $resubmitted->getStatusCode() === 400) {
            return $resubmitted; // Return the error response directly
        }
        return ResponseWithSuccessData($lang, $resubmitted, 1);
    }

    /**
     * Step 5: update Payroll Item
     */
    public function updatePayrollItem(Request $request, $itemId)
    {
        $lang = $request->header('lang', 'ar');

        $sheet = PayrollSheetItem::find($itemId);
        if (!$sheet) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }

        $validated = $request->validate([
            'base_salary' => 'sometimes|numeric',
            'overtime' => 'sometimes|numeric',
            'deductions' => 'sometimes|numeric',
            'bonuses' => 'sometimes|numeric',
        ]);

        try {
            $resubmitted = $this->payrollService->updatePayrollItem($itemId, $validated);
            // Check if the response is an error response
            if ($resubmitted instanceof \Illuminate\Http\JsonResponse && $resubmitted->getStatusCode() === 400) {
                return $resubmitted; // Return the error response directly
            }

            return ResponseWithSuccessData($lang, $resubmitted, 1);
        } catch (\Exception $e) {
            return respondError($e->getMessage(), 400);
        }
    }
}
