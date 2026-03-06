<?php

namespace App\Services\SettingsServices;

use App\Models\PaymentPolicies;
use App\Models\PaymentPolicyInvoiceCount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PaymentPoliciesService
{
    public function index(Request $request )
    {
        $lang = app()->getLocale();
        $guard = authActionSave();

        // Start building the query
        $query = PaymentPolicies::with('branch')->whereNull('deleted_at');

        // If user is Branch Manager, only show policies from their branch
        if ($guard['type'] == 'admin' && auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        } elseif ($guard['type'] == 'employee' && auth('employee')->user()->hasRole('Branch_Manager')) {
            $branch_id = auth('employee')->user()->branch_id;
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        // For other roles, filter by branch_id if provided in request
        elseif ($request->has('branch_id') && $request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        return $query;
    }


    // In your store method:
    public function store(Request $request, $checkToken)
    {
        $lang = app()->getLocale();
        $employee = authActionSave();
        // Create the payment policy
        $policy = new PaymentPolicies($request->except('invoice_count'));
        $policy->created_by = $employee['by'];
        $policy->created_by_type = $employee['type'];

        $policy->save();

        // Create invoice count record if invoice_count was provided
        if ($request->has('invoice_count') && $request->invoice_count > 0) {
            PaymentPolicyInvoiceCount::create([
                'payment_policy_id' => $policy->id,
                'invoice_count' => $request->invoice_count,
                'created_by' => $employee['by'],
                'created_by_type' => $employee['type']

            ]);
        }

        return $policy;
    }

    // In your update method:
    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $policy = PaymentPolicies::find($id);
        $employee = authActionSave();

        // Update the policy
        $data = $request->except('invoice_count');
        $data['no_payment_required'] = $request->has('no_payment_required') ? 1 : 0;
        $data['deposit_required'] = $request->has('deposit_required') ? 1 : 0;
        $data['full_payment_required'] = $request->has('full_payment_required') ? 1 : 0;
        $data['table_cancelation_value_type'] = $request->has('table_cancelation_value_type') ? 1 : 0;

        $policy->update($data);
        $policy->modified_by = $employee['by'];
        $policy->modified_by_type = $employee['type'];
        $policy->save();

        // Update or create invoice count record
        if ($request->has('invoice_count')) {
            PaymentPolicyInvoiceCount::updateOrCreate(
                ['payment_policy_id' => $policy->id],
                [
                    'invoice_count' => $request->invoice_count,
                    'updated_by' => $employee['by'],
                    'updated_by_type' => $employee['type'],

                ]
            );
        }

        return $policy;
    }
  public function show($id)
    {
        return PaymentPolicies::with(['invoiceCount'])->findOrFail($id);
    }

   public function destroy($id, $lang)
    {
        try {
            $payment = PaymentPolicies::findOrFail($id);
            $payment->update([
                'deleted_by' => authActionSave()['by'],
                'deleted_by_type' => authActionSave()['type']
            ]);

            // Perform soft delete
            $payment->delete();

            // Success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return  RespondWithBadRequestNotExist();
        } catch (\Exception $e) {
            Log::error('Error deleting payment policy: ' . $e->getMessage());
            return respondError(__('Something went wrong'), 500);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = app()->getLocale();
            $policy = PaymentPolicies::withTrashed()->findOrFail($id);
            $policy->restore();

            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring payment policy: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
