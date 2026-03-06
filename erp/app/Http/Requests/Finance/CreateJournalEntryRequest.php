<?php

namespace App\Http\Requests\Finance;

use App\Helper\APIResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class CreateJournalEntryRequest extends FormRequest
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
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;

        return [
            // 'facility_id' => [
            //     'required', 'numeric',
            //     Rule::exists('facilities', 'id')->whereNull('deleted_at'),
            // ],

            'journal_entry_department_id' => [
                'required', 'numeric',
                Rule::exists('journal_entry_departments', 'id')->whereNull('deleted_at'),
            ],

            'ledger_number' => [
                'nullable',
                'regex:/^[a-zA-Z0-9]+$/',
                Rule::unique('journal_entries', 'ledger_number')
                    ->where(function ($q) use($facility_id){
                        return $q->where('facility_id', $facility_id)
                                ->whereNull('deleted_at');
                    }),
            ],

            'account_type' => ['required', Rule::in(['normal', 'customer', 'supplier'])],

            'date' => ['required', 'date'],

            'file' => [
                'nullable',
                'file',
                'mimes:jpeg,jpg,png,webp,gif,pdf,xls,xlsx,csv',
                'max:5120',
            ],
            
            'is_repeated' => ['required', Rule::in([1, 2])],

            'repeated_count' => [
                'nullable', 'integer', 'min:1', 'max:365',
                'required_if:is_repeated,2',
            ],

            'repeated_type' => [
                'nullable', 
                Rule::in(['day', 'month']),
                'required_if:is_repeated,2',
            ],

            'status' => ['required', Rule::in(['draft', 'posted'])],

            'currency_id' => [
                'required', 'numeric',
                Rule::exists('currencies', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use($facility_id){
                    $facility = \App\Models\Facility::find($facility_id);
                    if (!$facility || !$facility->currency_id) {
                        return $fail('المنشاة ليس لها عملة.');
                    }
                    $hasActiveExchange = \App\Models\CurrencyExchange::where('currency_id', $value)
                        ->where('exchange_currency_id', $facility->currency_id)
                        ->where('facility_id', $facility_id)
                        ->where('is_active', 1)
                        ->exists();

                    if (!$hasActiveExchange) {
                        return $fail('لا يوجد سعر صرف نشط لهذه العملة في المرفق.');
                    }
                }
            ],
            'description' => ['nullable', 'string'],

            'journal_entry_details' => ['required', 'array', 'min:1'],

            'journal_entry_details.*.journal_id' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) {
                    $journal = \App\Models\Journal::withCount('children')
                        ->where('id', $value)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$journal) {
                        return $fail("الحساب غير موجود.");
                    }

                    if ($journal->children_count > 0) {
                        return $fail("لا يمكن اختيار حساب رئيسي يحتوي على حسابات فرعية.");
                    }
                }
            ],

            'journal_entry_details.*.name' => [
                // Rule::requiredIf($this->account_type === 'normal'),
                'nullable', 'string', 'max:255'
            ],

            'journal_entry_details.*.customer_id' => [
                // Rule::requiredIf($this->account_type === 'customer'),
                'nullable', 'numeric',
                Rule::exists('users', 'id')->where('flag', 'client')->whereNull('deleted_at'),
            ],

            'journal_entry_details.*.vendor_id' => [
                // Rule::requiredIf($this->account_type === 'supplier'),
                'nullable', 'numeric',
                Rule::exists('vendors', 'id')->whereNull('deleted_at'),
            ],

            'journal_entry_details.*.cost_center_id' => [
                'nullable',
                'numeric',
                Rule::exists('cost_centers', 'id')->where(function($query) use($facility_id){
                    $query->whereNull('deleted_at')
                        ->where('facility_id', $facility_id)
                        ->where('is_active', 1);
                }),
            ],

            'journal_entry_details.*.debit' => 'nullable|numeric|min:0',
            'journal_entry_details.*.credit' => 'nullable|numeric|min:0',
            'journal_entry_details.*.description' => ['nullable', 'string'],

            'journal_entry_details.*' => [
                function ($attribute, $value, $fail) {
                    $debit  = $value['debit'] ?? 0;
                    $credit = $value['credit'] ?? 0;

                    if ($debit == 0 && $credit == 0) {
                        $fail('يجب إدخال مبلغ مدين أو دائن في كل سطر.');
                    }
                },
            ],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {
                $details = $this->input('journal_entry_details', []);

                $totalDebit  = collect($details)->sum('debit');
                $totalCredit = collect($details)->sum('credit');

                if (abs($totalDebit - $totalCredit) > 0.0001) {
                    $validator->errors()->add(
                        'journal_entry_details',
                        'إجمالي المدين يجب أن يساوي إجمالي الدائن.'
                    );
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
                : 'لا يمكنك اضافة قيد جديد لان المنشاة غير مفعلة';
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
