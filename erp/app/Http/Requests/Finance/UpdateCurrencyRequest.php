<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class UpdateCurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    private string $failReason = '';

    public function authorize(): bool
    {
        $employee = auth('employee')->user();
        $id = $this->route('id');
        
        $facility_id = $employee->employeeFacility->facility_id;
        $check_facility = \App\Models\Facility::where('id', $facility_id)->first();
        if (!$check_facility) {
            $this->failReason = 'not_facility';
            return false;
        }

        if ($check_facility->currency_id == $id) {
            $this->failReason = 'no_facility';
            return false;
        }

        if ($check_facility->is_active != 1) {
            $this->failReason = 'not_active_facility';
            return false;
        }

        if (!$id) {
            $this->failReason = 'not_found';
            return false;
        }

        $result_data = \App\Models\Currency::find($id);
        if (!$result_data) {
            $this->failReason = 'not_found';
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $currencyId = $this->route('id');

        return [
            'currency_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_ar')->whereNull('deleted_at')->ignore($currencyId),
            ],
            'currency_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_en')->whereNull('deleted_at')->ignore($currencyId),
            ],
            'is_active' => 'sometimes|required|integer|in:0,1,2',
            'currency_symbol' => 'nullable|string',
            'currency_code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_code')->whereNull('deleted_at')->ignore($currencyId),
            ],
            'decimal_number' => 'sometimes|nullable|integer',
            'currency_symbol' => 'sometimes|nullable|string',
            'exchange_value' => 'sometimes|nullable|numeric|decimal:0,8|gte:0',
        ];

    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            // 'name_ar.unique' => __('The Arabic name already exists.')
            // 'name_en.unique' => __('The English name already exists.'),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $lang = $this->header('lang', 'en');
        $response = respondError(
            $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
            400,
            $validator->errors()
        );
        throw new HttpResponseException($response);
    }

    protected function failedAuthorization()
    {
        $lang = $this->header('lang', 'ar');

        if ($this->failReason === 'not_active_facility') {
            $message = $lang === 'en'
                ? 'You cannot edit currency because the facility is not active.'
                : 'لا يمكنك تعديل عملة لان المنشاة غير مفعلة';
        } 

        if($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        }

        if ($this->failReason === 'no_facility') {
            $message = $lang === 'en'
                ? 'You cannot change the base currency of the establishment.'
                : 'لا يمكنك تعديل العملة الاساسية للمنشاة';
        } 

        if ($this->failReason === 'not_found') {
            $message = $lang === 'en'
                ? 'This item is not found.'
                : 'هذا العنصر غير موجود.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
