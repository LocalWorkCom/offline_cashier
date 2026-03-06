<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateJournalRequest extends FormRequest
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

        $result_data = \App\Models\Journal::with('journalEntryDetails')->where('facility_id', $facility_id)->find($this->parent_id);
        if (!$result_data) {
            $this->failReason = 'not_parent';
            return false;
        }
        
        if ($result_data->journalEntryDetails->count() > 0) {
            $this->failReason = 'no_transaction';
            return false;
        }
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;
        return [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('journals', 'name_ar')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('journals', 'name_en')->where('facility_id', $facility_id)->whereNull('deleted_at'),
            ],
            // 'name_en' => ['nullable','string'],
            'parent_id' => ['required', 'numeric',
                Rule::exists('journals', 'id')->whereNull('deleted_at')
            ],
            'code' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                Rule::unique('journals', 'code')->where('facility_id', $facility_id)->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    if ($this->parent_id) {
                        $parent = \App\Models\Journal::find($this->parent_id);
                        if ($parent && !str_starts_with($value, $parent->code)) {
                            $fail("الكود يجب أن يبدأ بكود الأب: {$parent->code}");
                        }
                    }
                }
            ],
            // 'facility_id' => ['required', 'numeric',
            //     Rule::exists('facilities', 'id')->whereNull('deleted_at')
            // ],
            'currency_id' => ['nullable', 'numeric',
                Rule::exists('currencies', 'id')->whereNull('deleted_at'),
            ],
            'type' => ['required', Rule::in(['assets','liabilities','equity','revenue','expense'])],

            // 'type' => [
            //     'required', 'numeric',
            //     Rule::exists('journals', 'id')->where(function ($query) {
            //         return $query->whereNull('deleted_at')
            //             ->where('level', 1)
            //             ->where('facility_id', $this->facility_id);
            //     }),
            // ],

            // 'account_type' => [
            //     'required', Rule::in(['credit','debit']),
            //     function ($attribute, $value, $fail) {
            //         $journal = \App\Models\Journal::find($this->parent_id);
            //         if ($journal->account_type != $this->account_type) {
            //             return $fail('يجب ان يكون نوع الحساب نفس نوع الحساب الرئيسى.');
            //         }
            //     }
            // ],

            'account_type' => [
                'required',
                Rule::in(['credit', 'debit']),
                function ($attribute, $value, $fail) {
                    if (!$this->filled('parent_id')) return;
                    $parent = \App\Models\Journal::find($this->parent_id);
                    if (!$parent) {
                        $msg = $this->header('lang') === 'en'
                            ? 'Parent account not found.'
                            : 'الحساب الأب غير موجود.';
                        $fail($msg);
                        return;
                    }

                    if ($parent->account_type !== $value) {
                        $msg = $this->header('lang') === 'en'
                            ? 'Account type must be the same as the parent account.'
                            : 'يجب أن يكون نوع الحساب نفس نوع الحساب الأب.';
                        $fail($msg);
                    }
                }
            ],
            'description_ar' => ['sometimes', 'nullable', 'string']
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

    public function after()
    {
        return [
            function () {
                if ($this->parent_id) {
                    $parent = \App\Models\Journal::find($this->parent_id);
                    if ($parent && $parent->account_type !== $this->account_type) {
                        $message = request()->header('lang','en') === 'ar'
                            ? 'نوع الحساب يجب أن يطابق نوع حساب الأب.'
                            : 'Account type must match parent account type.';
                        $this->validator->errors()->add('account_type', $message);
                    }
                }
            }
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
                ? 'You cannot add a new entry because the facility is not active.'
                : 'لا يمكنك اضافة حساب جديد لان المنشاة غير مفعلة';
        } 
        if($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        }

        if ($this->failReason === 'no_transaction') {
            $message = $lang === 'en'
                ? 'You cannot add a new account because the primary account already has transactions.'
                : 'لا يمكنك اضافة حساب جديد لان الحساب الاساسى لديه معاملات';
        } 

        if($this->failReason === 'not_parent') {
            $message = $lang === 'en'
                ? 'This parent acount is not found.'
                : 'الحساب الرئيسى غير موجود.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
