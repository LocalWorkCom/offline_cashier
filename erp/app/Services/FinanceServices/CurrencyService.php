<?php

namespace App\Services\FinanceServices;

use App\Models\Currency;
use App\Models\CurrencyExchange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\CurrencyResource;
use App\Http\Resources\Finance\CurrencyShowResource;
use App\Models\Facility;
use App\Models\FacilityCurrency;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class CurrencyService
{
    public function getAll(Request $request)
    {
        // $facility_id = $request->facility_id;
        // $currency_ids = FacilityCurrency::where('facility_id', $request->facility_id)->pluck('currency_id');
        // $currencies = Currency::whereIn('id', $currency_ids)->with(['currencyExchange' => fn($q) => $q->where('facility_id', $request->facility_id)->where('is_active', 1)]);
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;       
        $currencies = Currency::with(['currencyExchange' => fn($q) => $q->where('facility_id', $facility_id)->where('is_active', 1)]);
        return $currencies;
    }

    public function showList(Request $request)
    {
        // $facility_id = $request->facility_id;
        // $currency_ids = FacilityCurrency::where('facility_id', $request->facility_id)->pluck('currency_id');
        // $currencies = Currency::whereIn('id', $currency_ids)->with(['currencyExchange' => fn($q) => $q->where('facility_id', $request->facility_id)->where('is_active', 1)]);
        $currencies = Currency::get();
        return $currencies;
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $data = Currency::with('currencyExchange')->find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new CurrencyShowResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $result = collect($request->validated())->except('facility_id', 'exchange_value')->toArray();
            $currency = Currency::create(array_merge(
                $result,
                [
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]
            ));

            $facility = Facility::find($request->facility_id);
            if ($request->filled('exchange_value') && $request->exchange_value != 0) {
                $currency_exchange = CurrencyExchange::create([
                    'currency_id' => $currency->id,
                    'exchange_currency_id' => $facility->currency_id,
                    'facility_id' => $request->facility_id,
                    'exchange_value' => $request->exchange_value,
                    'date' => date('Y-m-d'),
                    'is_active' => 1,
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
            }

            return $currency;
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        // }
    }

    public function edit(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $currency = Currency::find($request->id);
            if (!$currency) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $result = collect($request->validated())->except('exchange_value','_method')->toArray();
            $result = array_merge($result, [
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ]);

            $currency->update($result);
            $facility = Facility::find($request->facility_id);
            if ($request->filled('exchange_value') && $request->exchange_value != 0) {
                $exchange_value_before = 0;
                $lastActive = CurrencyExchange::where('currency_id', $currency->id)
                    ->where('facility_id', $facility->id)
                    ->where('is_active', 1)
                    ->first();
                if($lastActive){
                    $exchange_value_before = $lastActive?->exchange_value;
                    $lastActive->update([
                            'is_active' => 0,
                            'modified_by'      => authActionSave()['by'],
                            'modified_by_type' => authActionSave()['type'],
                        ]);
                }
                
                CurrencyExchange::create([
                    'currency_id'           => $currency->id,
                    'facility_id'           => $facility->id,
                    'exchange_currency_id'  => $facility->currency_id,
                    'exchange_value'        => $request->exchange_value,
                    'exchange_value_before' => $exchange_value_before,
                    'date'                  => date('Y-m-d'),
                    'is_active'             => 1,
                    'created_by'            => authActionSave()['by'],
                    'created_by_type'       => authActionSave()['type'],
                ]);
            }

            return ResponseWithSuccessData($lang, new CurrencyResource($currency), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }


    public function delete(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }

            $data = Currency::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $facility = Facility::where('currency_id',$request->id)->get();
            if(count($facility) > 0){
                return respondError(($lang == 'en' ? 'Primary currency of the establishment' : 'عملة اساسية فى المنشاة'), 404, $lang == 'en' ? 'You cannot delete this currency because it is a primary currency in the establishment.' : 'لا يمكنك حذف هذه العملة لانها عملة اساسية فى المنشاة');
            }

            if(count($data->journalEntry) > 0){
                return respondError(($lang == 'en'? 'Has linked journal entery': 'لديه قيود مرتبطة'), 404, $lang == 'en'? 'This item has linked journal entry. Delete them first before deleting this item.': 'هذا العنصر لديه قيود مرتبطة مرتبطة به .. احذفها أولاً ثم قم بالحذف');
            }

            $data->deleted_by = authActionSave()['by'];
            $data->deleted_by_type = authActionSave()['type'];
            $data->save();

            CurrencyExchange::where('facility_id', $request->facility_id)
                ->where('currency_id', $data->id)
                ->update([
                    'deleted_by'      => authActionSave()['by'],
                    'deleted_by_type' => authActionSave()['type'],
                ]);

            CurrencyExchange::where('facility_id', $request->facility_id)
                ->where('currency_id', $data->id)
                ->delete();

            $data->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }



}
