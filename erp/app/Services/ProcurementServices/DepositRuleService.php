<?php

namespace App\Services\ProcurementServices;

use App\Models\DepositRule;
use App\Models\DepositRuleVendor;
use App\Models\HighValueRule;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use App\Models\PurchasingBudget;
use App\Models\PurchasingBudgetLog;
use App\Services\AuthAppService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DepositRuleService
{
    public function index(Request $request)
    {
        $query = DepositRule::with('highValueRule');
        if ($request->filled('active')) {
            $query->where('active', $request->filled('active'));
        }
        if ($request->filled('from')) {
            $query->where('created_at', $request->filled('from'));
        }
        if ($request->filled('to')) {
            $query->where('creted_at', $request->filled('to'));
        }
        $query->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc');
        return $query;
    }
    public function show(Request $request, $id)
    {
        $query = DepositRule::with('highValueRule')->find($id);
        return $query;
    }
    public function store(Request $request)
    {
        $rule = new DepositRule();
        $rule->name_ar = $request->name_ar;
        $rule->name_en = $request->name_en;

        $rule->percentage = $request->percentage;
        $rule->applicable_to = $request->applicable_to;
        $rule->vendor_type = $request->vendor_type;
        $rule->conditions = $request->conditions;
        $rule->linked_high_value_rule_id = $request->linked_high_value_rule_id;
        $rule->active = 1;
        $rule->created_by = authActionSave()['by'];
        $rule->save();

        if (!empty($request['vendor_ids'])) {
            foreach ($request['vendor_ids'] as $vendorId) {
                DepositRuleVendor::create([
                    'deposit_rule_id' => $rule->id,
                    'vendor_id' => $vendorId,
                ]);
            }
        }
        return $rule;
    }
    public function update(Request $request, $id)
    {
        $rule = DepositRule::find($id);
        $rule->name_ar = $request->name_ar ?? $rule->name_ar;
        $rule->name_en = $request->name_en ?? $rule->name_en;

        $rule->percentage = $request->percentage ?? $rule->percentage;
        $rule->applicable_to = $request->applicable_to ?? $rule->applicable_to;
        $rule->vendor_type = $request->vendor_type ?? $rule->vendor_type;
        $rule->conditions = $request->conditions ?? $rule->conditions;
        $rule->linked_high_value_rule_id = $request->linked_high_value_rule_id ?? $rule->linked_high_value_rule_id;
        $rule->active = $request->active ?? $rule->active;
        $rule->updated_by = authActionSave()['by'];
        $rule->save();
        DepositRuleVendor::where('deposit_rule_id', $rule->id)->delete();

        /** ADD NEW VENDOR RELATIONSHIPS */
        if (!empty($request['vendor_ids'])) {
            foreach ($request['vendor_ids'] as $vendorId) {
                DepositRuleVendor::create([
                    'deposit_rule_id' => $rule->id,
                    'vendor_id' => $vendorId,
                ]);
            }
        }

        return $rule;
    }
}
