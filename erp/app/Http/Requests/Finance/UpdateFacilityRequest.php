<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateFacilityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $id = $this->route('id');
        if (!$id) {
            return false;
        }
        $result_data = \App\Models\Facility::find($id);
        if (!$result_data) {
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $facilityId = $this->route('id');

        return [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('facilities', 'name_ar')
                    ->whereNull('deleted_at')
                    ->ignore($facilityId),
            ],
            'is_active' => 'sometimes|required|integer|in:0,1,2',
            'currency_id' => 'sometimes|required|exists:currencies,id',
            'country_id' => 'sometimes|required|exists:countries,id',
            // 'currency_id' => [
            //     'sometimes', 'required', 'numeric',
            //     Rule::exists('currencies', 'id')->whereNull('deleted_at'),
            //     function ($attribute, $value, $fail) use ($facilityId){
            //         $hasPreviousTransactions = \App\Models\JournalEntry::where('currency_id', $value)
            //             ->where('facility_id', $facilityId)
            //             ->exists();
            //         if (!$hasPreviousTransactions) {
            //             return $fail('لا يمكنك تعديل العملة حيث تمت عمليات عليها.');
            //         }
            //     }
            // ],
            'currency_id' => [
                'sometimes',
                'required',
                'numeric',
                Rule::exists('currencies', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($facilityId) {
                    $currentCurrencyId = \App\Models\Facility::where('id', $facilityId)
                        ->value('currency_id');
                    if ((int)$currentCurrencyId === (int)$value) {
                        return;
                    }
                    $used = \App\Models\JournalEntryDetails::whereHas('journalEntry', function ($q) use ($facilityId) {
                            $q->where('facility_id', $facilityId);
                        })->exists();
                    if ($used) {
                        $fail('لا يمكن تغيير العملة لأنها مربوطة بقيود محاسبية سابقة.');
                    }
                },
            ],
            'code' => [
                'sometimes',
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                Rule::unique('facilities', 'code')
                    ->whereNull('deleted_at')
                    ->ignore($facilityId),
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                Rule::unique('facilities', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($facilityId),
            ],
            'logo' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'address' => 'sometimes|nullable|string',
            'tax_id_number' => [
                'sometimes',
                'required',
                Rule::unique('facilities', 'tax_id_number')
                    ->whereNull('deleted_at')
                    ->ignore($facilityId),
            ],
            'commercial_registration' => 'sometimes|nullable',
            'commercial_registration_number' => [
                'sometimes',
                'nullable',
                Rule::unique('facilities', 'commercial_registration_number')
                    ->whereNull('deleted_at')
                    ->ignore($facilityId),
            ],
            'language' => 'sometimes|nullable|in:ar,en',
            'vat_registration_number' => 'sometimes|nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            // 'name_ar.unique' => 'اسم المنشأة بالعربي موجود مسبقًا',
            // 'email.unique'   => 'البريد الإلكتروني مستخدم في منشأة أخرى',
        ];
    }

    /**
     * Handle validation failure
     */
    protected function failedValidation(Validator $validator)
    {
        $lang = $this->header('lang', 'en');

        throw new HttpResponseException(
            respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق من البيانات.',
                422,
                $validator->errors()
            )
        );
    }

    protected function failedAuthorization()
    {
        $lang = $this->header('lang', 'ar');
        $message = $lang === 'en'
            ? 'This item is not found.'
            : 'هذا العنصر غير موجود.';
        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
