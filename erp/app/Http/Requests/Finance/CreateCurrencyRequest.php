<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
class CreateCurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    private string $failReason = '';

    public function authorize(): bool
    {
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;

        $result_data = \App\Models\Facility::find($facility_id);
        if (!$result_data) {
            $this->failReason = 'not_facility';
            return false;
        }

        if ($result_data->is_active != 1) {
            $this->failReason = 'not_active_facility';
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'currency_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_ar')->whereNull('deleted_at'),
            ],
            'currency_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_en')->whereNull('deleted_at'),
            ],
            'is_active' => 'required|integer|in:0,1,2',
            'currency_symbol' => 'nullable|string',
            'currency_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('currencies', 'currency_code')->whereNull('deleted_at'),
            ],
            'decimal_number' => 'nullable|integer',
            'currency_symbol' => 'nullable|string',
            'facility_id' => 'required|exists:facilities,id',
            'exchange_value' => 'required|numeric|decimal:0,8|gte:0',
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
                ? 'You cannot add a new currency because the facility is not active.'
                : 'لا يمكنك اضافة عملة جديد لان المنشاة غير مفعلة';
        } 
        if($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
