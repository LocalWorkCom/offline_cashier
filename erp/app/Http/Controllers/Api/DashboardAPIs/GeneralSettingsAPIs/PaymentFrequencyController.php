<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\PaymentFrequency;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaymentFrequencyController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $payment_frequencies = PaymentFrequency::withCount('employees');
        $response = paginateOrGetAll($payment_frequencies, $request, null);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentFrequency = PaymentFrequency::withCount('employees')->find($id);
        if (!$paymentFrequency) {
            $message = $lang == 'en' ? 'Payment Frequency not found.' : 'تكرار الدفع غير موجود.';
            return respondError($message, 404);
        }

        return ResponseWithSuccessData($lang, $paymentFrequency, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('payment_frequencies', 'name_ar')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('payment_frequencies', 'name_en')->whereNull('deleted_at')
            ],
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $payment_frequency = new PaymentFrequency();
        $payment_frequency->name_ar = $request->name_ar;
        $payment_frequency->name_en = $request->name_en;
        $payment_frequency->created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $payment_frequency->save();

        return ResponseWithSuccessData($lang, $payment_frequency, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentFrequency = PaymentFrequency::find($id);
        if (!$paymentFrequency) {
            $message = $lang == 'en' ? 'Payment Frequency not found.' : 'تكرار الدفع غير موجود.';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:payment_frequencies,name_ar,' . $id,
            'name_en' => 'required|string|unique:payment_frequencies,name_en,' . $id,
        ], [
            'name_ar.required' => __('validation.required', ['attributes' => 'Arabic name'], $lang),
            'name_ar.unique' => __('validation.unique', ['attributes' => 'Arabic name'], $lang),
            'name_en.required' => __('validation.required', ['attributes' => 'English name'], $lang),
            'name_en.unique' => __('validation.unique', ['attributes' => 'English name'], $lang),
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }

        $paymentFrequency->name_ar = $request->name_ar;
        $paymentFrequency->name_en = $request->name_en;
        $paymentFrequency->modified_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $paymentFrequency->save();

        return ResponseWithSuccessData($lang, $paymentFrequency, 1);
    }
    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentFrequency = PaymentFrequency::find($id);
        if (!$paymentFrequency) {
            $message = $lang == 'en' ? 'Payment Frequency not found.' : 'تكرار الدفع غير موجود.';
            return respondError($message, 404);
        }
        $paymentFrequency->deleted_by = auth('employee')->user()->id ?? auth('admin')->user()->id;
        $paymentFrequency->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
