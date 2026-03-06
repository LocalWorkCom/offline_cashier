<?php

namespace App\Services\ProcurementServices;

use App\Models\Currency;
use App\Models\FinancialPrecisionSetting;
use App\Models\GlobalTaxSetting;
use App\Models\PaymentInterval;
use App\Models\TaxType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FinancialService
{
    public function listCurrencies($from, $to)
    {

        $query = Currency::with('latestRate');

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        $query->orderByDesc('created_at')->orderByDesc('updated_at');
        return $query;
    }
    public function showCurrency(Request $request, $id)
    {
        $query = Currency::with('latestRate')->find($id);
        return $query;
    }
    public function storeCurrency(Request $request)
    {


        // if new default currency → remove old default
        if ($request->is_default == 1) {
            Currency::where('is_default', 1)->update(['is_default' => 0]);
        }

        $currency = Currency::create([
            'currency_ar' => $request->currency_ar,
            'currency_en' => $request->currency_en,
            'currency_symbol' => $request->currency_symbol,
            'price_egp' => $request->price_egp,
            'is_default' => $request->is_default ?? 0,
            'created_by' => authActionSave()['by'],

        ]);

        return $currency;
    }

    // ---------------------- EDIT ----------------------
    public function updateCurrency(Request $request, $id)
    {
        $currency = Currency::findOrFail($id);



        if ($request->is_default == 1) {
            Currency::where('is_default', 1)->where('id', '!=', $id)->update(['is_default' => 0]);
        }

        $currency->update([
            'currency_ar' => $request->currency_ar,
            'currency_en' => $request->currency_en,
            'currency_symbol' => $request->currency_symbol,
            'price_egp' => $request->price_egp,
            'is_default' => $request->is_default ?? $currency->is_default,
            'modified_by' => authActionSave()['by'],

        ]);

        return response()->json(['message' => 'Currency updated successfully', 'data' => $currency]);
    }

    // ---------------------- DELETE ----------------------
    public function destroyCurrency($id)
    {
        $currency = Currency::findOrFail($id);

        $currency->update([

            'deleted_by' => authActionSave()['by'],
        ]);

        $currency->delete(); // Soft delete

        return response()->json(['message' => 'Currency deleted successfully']);
    }

    // In FinancialService
    public function listTaxes($from = null, $to = null)
    {
        $query = TaxType::query();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function saveTax($data)
    {
        $tax = TaxType::create([
            'name_en'    => $data['name_en'],
            'name_ar'    => $data['name_ar'],
            'percentage' => $data['percentage'],
            'is_default' => $data['is_default'] ?? false,
            'created_by' => authActionSave()['by'],
        ]);

        if ($tax->is_default) {
            TaxType::where('id', '!=', $tax->id)->update(['is_default' => false]);
        }

        return $tax;
    }


    public function updateTax($id, $data)
    {
        $tax = TaxType::findOrFail($id);

        $tax->update([
            'name_en'    => $data['name_en']??$tax->name_en,
            'name_ar'    => $data['name_ar']??$tax->name_ar,
            'percentage' => $data['percentage']??$tax->percentage,
            'is_default' => $data['is_default'] ?? $tax->is_default,
                        'is_active' => $data['is_active'] ?? $tax->is_active,

            'modified_by' => authActionSave()['by'],
        ]);

        if ($tax->is_default) {
            TaxType::where('id', '!=', $tax->id)->update(['is_default' => false]);
        }

        return $tax;
    }
public function updateTaxDefault($id, $data)
    {
        $tax = TaxType::findOrFail($id);

        $tax->update([

            'is_default' => $data['is_default'] ?? $tax->is_default,
            'modified_by' => authActionSave()['by'],
        ]);

        if ($tax->is_default) {
            TaxType::where('id', '!=', $tax->id)->update(['is_default' => false]);
        }

        return $tax;
    }

    public function getPrecisionSettings()
    {
        return FinancialPrecisionSetting::latest()->first();
    }
    public function updatePrecision($roundRule, $decimals)
    {
        Validator::make([
            'roundRule' => $roundRule,
            'decimals' => $decimals
        ], [
            'roundRule' => 'required|in:nearest,up,down',
            'decimals' => 'required|in:2,3,4'
        ])->validate();

        $settings = FinancialPrecisionSetting::first();


        $settings->update([
            'rounding_rule' => $roundRule,
            'decimals' => $decimals,
            'effective_date' => now(),
            'updated_by' => authActionSave()['by'],
        ]);
        return $settings;
    }
    function applyRounding($value)
    {
        $settings = FinancialPrecisionSetting::latest()->first();

        $dec = $settings->decimals;

        if ($settings->rounding_rule == 'nearest') {
            return round($value, $dec);
        }

        if ($settings->rounding_rule == 'up') {
            return ceil($value * pow(10, $dec)) / pow(10, $dec);
        }

        if ($settings->rounding_rule == 'down') {
            return floor($value * pow(10, $dec)) / pow(10, $dec);
        }
    }
}
