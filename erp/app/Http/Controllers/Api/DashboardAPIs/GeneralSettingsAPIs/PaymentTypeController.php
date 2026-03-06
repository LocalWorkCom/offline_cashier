<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PaymentTypeController extends Controller
{
    public function index(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $payment_types = PaymentType::query();
        $response = paginateOrGetAll($payment_types, $request, null);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }

    public function show(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentType = PaymentType::find($id);
        if (!$paymentType) {
            $message = $lang == 'en' ? 'Payment Type not found.' : 'نوع الدفع غير موجود.';
            return respondError($message, 404);
        }

        return ResponseWithSuccessData($lang, $paymentType, 1);
    }

    public function store(Request $request)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                Rule::unique('payment_types', 'name_ar')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                Rule::unique('payment_types', 'name_en')->whereNull('deleted_at')
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

        $payment_type = new PaymentType();
        $payment_type->name_ar = $request->name_ar;
        $payment_type->name_en = $request->name_en;
        $payment_type->created_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $payment_type->save();

        return ResponseWithSuccessData($lang, $payment_type, 1);
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentType = PaymentType::find($id);
        if (!$paymentType) {
            $message = $lang == 'en' ? 'Payment Type not found.' : 'نوع الدفع غير موجود.';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|unique:payment_types,name_ar,' . $id,
            'name_en' => 'required|string|unique:payment_types,name_en,' . $id,
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

        $paymentType->name_ar = $request->name_ar;
        $paymentType->name_en = $request->name_en;
        $paymentType->modified_by = auth('employee')->user()->id ?? auth('admin')->user()->id;

        $paymentType->save();

        return ResponseWithSuccessData($lang, $paymentType, 1);
    }
    public function delete(Request $request, $id)
    {
        $lang = $request->header('lang');
        App::setLocale($lang);

        $paymentType = PaymentType::find($id);
        if (!$paymentType) {
            $message = $lang == 'en' ? 'Payment Type not found.' : 'نوع الدفع غير موجود.';
            return respondError($message, 404);
        }
        $paymentType->deleted_by = auth('employee')->user()->id ?? auth('admin')->user()->id;
        $paymentType->delete();

        return RespondWithSuccessRequest($lang, 1);
    }
}
