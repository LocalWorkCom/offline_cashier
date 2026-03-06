<?php


namespace App\Services\SettingsServices;

use App\Http\Requests\LogoFormRequest;
use App\Models\PaymentFrequency;
use App\Models\PolicyPaymentReservation;
use Illuminate\Http\Request;

class PaymentReservationPolicyService
{

    public function index()
    {
        try {
            $lang = app()->getLocale();
            $data = PolicyPaymentReservation::first();
            return ResponseWithSuccessData($lang,$data ,1);

        } catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }

    public function update(Request $request)
    {
        try {
            $lang = app()->getLocale();
            $data = PolicyPaymentReservation::first();
            $data->update($request->except('_token'));

            return ResponseWithSuccessData($lang,$data,1);
        }
        catch (\Exception $e) {
            return respondError($e->getMessage(), 2);
        }
    }
}
