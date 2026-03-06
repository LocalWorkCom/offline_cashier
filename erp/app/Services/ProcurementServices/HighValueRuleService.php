<?php

namespace App\Services\ProcurementServices;

use App\Models\HighValueRule;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use App\Models\PurchasingBudget;
use App\Models\PurchasingBudgetLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HighValueRuleService
{
    public function index(Request $request)
    {
        $query = HighValueRule::query();
        if ($request->filled('active')) {
            $query->where('active', $request->filled('active'));
        }
        if ($request->filled('from')) {
            $query->where('created_at', $request->filled('from'));
        }
        if ($request->filled('to')) {
            $query->where('created_at', $request->filled('to'));
        }
        $query->orderBy('created_at', 'desc')->orderBy('updated_at', 'desc');
        return $query;
    }
    public function show(Request $request, $id)
    {
        $query = HighValueRule::find($id);
        return $query;
    }
    public function store(Request $request)
    {

        $highValue = new HighValueRule();

        $highValue->name_ar = $request->name_ar;
        $highValue->name_en = $request->name_en;
        $highValue->financial_limit = $request->financial_limit;
        $highValue->DRR = $request->DRR;
        $highValue->notes = $request->notes;

        $highValue->max_deposit_percentage = $request->max_deposit_percentage;
        $highValue->active = 1;
        $highValue->created_by =  authActionSave()['by'];
        $highValue->save();
        return $highValue;
    }


    public function update(Request $request, $id)
    {
        $highValue = HighValueRule::find($id);
        $highValue->name_ar = $request->name_ar;
        $highValue->name_en = $request->name_en;

        $highValue->financial_limit = $request->financial_limit;
        $highValue->notes = $request->notes;

        $highValue->DRR = $request->DRR;
        $highValue->active = $request->active ?? $highValue->active;
        $highValue->updated_by = authActionSave()['by'];
        $highValue->save();
        return $highValue;
    }
}
