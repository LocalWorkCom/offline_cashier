<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyFormRequest;
use App\Models\Currency;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currencies = Currency::with('country')->get();
        $countries = Country::all();
        return view('dashboard.currency.list', compact('currencies', 'countries'));
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
    public function store(CurrencyFormRequest $request)
    {
        $lang = app()->getLocale();
        $data = $request->validated();

        $data['is_default'] = $data['is_default'] ?? 0;

        $currency = new Currency();
        $currency->currency_ar = $data['currency_ar'];
        $currency->currency_en = $data['currency_en'];
        $currency->currency_symbol = $data['currency_symbol'];
        $currency->currency_code = $data['currency_code'];
        $currency->is_default = $data['is_default'] ?? 0;
        $currency->country_id = $data['country_id'];
        $currency->created_by = auth('admin')->id() ?? 1;

        // Handle default currency logic
        if ($data['is_default'] == 1) {
            // Reset any existing default currency
            Currency::where('is_default', 1)->update(['is_default' => 0]);
        }

        $currency->save();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];

        return redirect('dashboard/currencies')->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $currency = Currency::with('country')->find($id);

        if (!$currency) {
            return response()->json(['error' => 'Currency not found'], 404);
        }

        return response()->json([
            'currency_ar' => $currency->currency_ar,
            'currency_en' => $currency->currency_en,
            'currency_symbol' => $currency->currency_symbol,
            'currency_code' => $currency->currency_code,
            'is_default' => $currency->is_default,
            'country_id' => $currency->country_id,
            'country_name' => $currency->country ? (app()->getLocale() == 'en' ? $currency->country->name_en : $currency->country->name_ar) : null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CurrencyFormRequest $request, string $id)
    {
        $lang = app()->getLocale();
        $data = $request->validated();

        $data['is_default'] = $data['is_default'] ?? 0;
        $currency = Currency::findOrFail($id);
        $currency->currency_ar = $data['currency_ar'];
        $currency->currency_en = $data['currency_en'];
        $currency->currency_symbol = $data['currency_symbol'];
        $currency->currency_code = $data['currency_code'];
        $currency->is_default = $data['is_default'] ?? 0;
        $currency->country_id = $data['country_id'];
        $currency->modified_by = auth('admin')->id() ?? 1;

        // Handle default currency logic
        if ($data['is_default'] == 1) {
            // Reset any existing default currency except the current one
            Currency::where('is_default', 1)->where('id', '!=', $id)->update(['is_default' => 0]);
        }
        else {
            // Prevent removing default if it's the only one
            $defaultCount = Currency::where('is_default', 1)->count();
            if ($defaultCount <= 1 && $currency->is_default == 1) {
                app()->getLocale() == 'en'
                    ? $error = 'Cannot remove default status from the only default currency'
                    : $error = 'لا يمكن إزالة الحالة الافتراضية من العملة الافتراضية الوحيدة';
                return redirect('dashboard/currencies')->withErrors($error);
            }
        }

        $currency->save();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];

        return redirect('dashboard/currencies')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $currency = Currency::findOrFail($id);

        // Prevent deletion if it's the default currency
        if ($currency->is_default == 1) {
            app()->getLocale() == 'en'
                ? $error = 'Cannot delete the default currency'
                : $error = 'لا يمكن حذف العملة الافتراضية';
            return redirect('dashboard/currencies')->withErrors($error);
        }

        $currency->deleted_by = auth('admin')->id() ?? 1;
        $currency->save(); // Update deleted_by before soft delete
        $currency->delete();

        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];

        return redirect('dashboard/currencies')->with('message', $message);
    }
}
