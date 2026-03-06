<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Http\Controllers\Controller;
use App\Http\Resources\purchase\CurrencyResource;
use App\Http\Resources\purchase\TaxResource;

use App\Models\TaxType;
use App\Services\ProcurementServices\FinancialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class FinancialController extends Controller
{
    protected $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    // ---------------------------------------------------------------------
    // 📌 CURRENCY MANAGEMENT
    // ---------------------------------------------------------------------

    public function listCurrencies(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $from = $request->input('from');
        $to = $request->input('to');
        $query = $this->financialService->listCurrencies($from, $to);
        $currencies = paginateOrGetAll($query, $request);
        $responseData = CurrencyResource::collection($currencies['data'])->resolve();

        return ResponseWithSuccessDataPaginated($lang, ['data' => $responseData, 'meta' => $currencies['meta']], 1);
    }
    public function showCurrencies(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $show = $this->financialService->showCurrency($request, $id);
        $responseData = new CurrencyResource($show);

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    public function storeCurrency(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'currency_ar' => 'required|string',
            'currency_en' => 'required|string',
            'currency_symbol' => 'nullable|string',
            'price_egp' => 'nullable|string',
            'is_default' => 'nullable|in:0,1',
        ]);
        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
        }
        $currency = $this->financialService->storeCurrency($request);
        $responseData = new CurrencyResource($currency);

        return ResponseWithSuccessData($lang, $responseData, 1);
    }

    public function updateCurrency(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'currency_ar' => 'required|string',
            'currency_en' => 'required|string',
            'currency_symbol' => 'nullable|string',
            'price_egp' => 'nullable|string',
            'is_default' => 'nullable|in:0,1',
        ]);
        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
        }
        $this->financialService->updateCurrency($request, $id);
        return RespondWithSuccessRequest($lang, 1);
    }

    public function deleteCurrency(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $this->financialService->destroyCurrency($id);
        return RespondWithSuccessRequest($lang, 1);
    }

    // ---------------------------------------------------------------------
    // 📌 TAX MANAGEMENT
    // ---------------------------------------------------------------------

    public function listTaxes(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $query = $this->financialService->listTaxes();
        $taxes = paginateOrGetAll($query, $request);
        $responseData = TaxResource::collection($taxes['data'])->resolve();

        return ResponseWithSuccessDataPaginated($lang, ['data' => $responseData, 'meta' => $taxes['meta']], 1);
    }
    public function showTax(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $tax = TaxType::findOrFail($id);
        if (!$tax) {
            return respondError(__('validation.not_found'), 404);
        }
        $responseData = new TaxResource($tax);

        return ResponseWithSuccessData($lang, $responseData, 1);
    }
    public function storeTax(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $validator = Validator::make($request->all(), [
            'name_en' => 'required|unique:tax_types,name_en',
            'name_ar' => 'required|unique:tax_types,name_ar',
            'percentage' => 'required|numeric|min:0.01|max:100'
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $tax = $this->financialService->saveTax($request->all());
        return RespondWithSuccessRequest($lang, 1);
    }

    public function updateTax(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $tax = TaxType::findOrFail($id);
        if (!$tax) {
            return respondError(__('validation.not_found'), 404);
        }
        $validator = Validator::make($request->all(), [
            'name_ar' => ['required', Rule::unique('tax_types')->ignore($tax->id)],
            'name_en' => ['required', Rule::unique('tax_types')->ignore($tax->id)],
            'percentage' => 'required|numeric|min:0.01|max:100',
            'is_active' => 'nullable|integer|in:0,1'
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $tax = $this->financialService->updateTax($id, $request->all());
        return RespondWithSuccessRequest($lang, 1);
    }
    public function updateTaxDefault(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $tax = TaxType::findOrFail($id);
        if (!$tax) {
            return respondError(__('validation.not_found'), 404);
        }

        $tax = $this->financialService->updateTaxDefault($id, $request->all());
        return RespondWithSuccessRequest($lang, 1);
    }
    public function deleteTax(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $tax = TaxType::find($id);
        $tax->update([
            'deleted_by' => authActionSave()['by']
        ]);

        $tax->delete();
        return RespondWithSuccessRequest($lang, 1);
    }

    // ---------------------------------------------------------------------
    // 📌 PRECISION & ROUNDING SETTINGS
    // ---------------------------------------------------------------------

    public function getPrecisionSettings(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $settings = $this->financialService->getPrecisionSettings();
        // $settings = paginateOrGetAll($query, $request);

        return ResponseWithSuccessData($lang, $settings, 1);
    }

    public function updatePrecision(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);
        $request->validate([
            'roundRule' => 'required|in:nearest,up,down',
            'decimals'  => 'required|in:2,3,4'
        ]);
        $settings = $this->financialService->updatePrecision(
            $request->roundRule,
            $request->decimals
        );
        return RespondWithSuccessRequest($lang, 1);
    }
}
