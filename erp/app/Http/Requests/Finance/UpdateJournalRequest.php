<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Models\Journal;
class UpdateJournalRequest extends FormRequest
{
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

        $id = $this->route('id');
        if (!$id) {
            $this->failReason = 'not_found';
            return false;
        }

        $result_journal = \App\Models\Journal::find($id);
        if (!$result_journal) {
            $this->failReason = 'not_found';
            return false;
        }

        if($result_journal->level != 1){
            $result_data = \App\Models\Journal::with('journalEntryDetails')->where('facility_id', $facility_id)->find($this->parent_id);
            if (!$result_data) {
                $this->failReason = 'not_parent';
                return false;
            }
        }
        
        
        // if ($result_data->journalEntryDetails->count() > 0) {
        //     $this->failReason = 'no_transaction';
        //     return false;
        // }
        return true;

        // if(count($result_data->journalEntryDetails) > 0){
        //     $this->failReason = 'not_entry';
        //     return false;
        // }
        //  if(count($result_data->children) > 0){
        //     $this->failReason = 'not_children';
        //     return false;
        // }
        return true;
    }

    public function rules(): array
    {
        $journalId = $this->route('id');
        $journal = Journal::with(['children', 'journalEntryDetails'])->find($journalId);
        $parent = $journal->parent_id;

        $hasChildren = $journal?->children->isNotEmpty() ?? false;
        $hasEntries  = $journal?->journalEntryDetails->isNotEmpty() ?? false;

        $isProtected = $hasChildren || $hasEntries;

        $employee = auth('employee')->user();
        $facility_id = $employee?->employeeFacility?->facility_id;

        $rules = [
            'name_ar' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('journals', 'name_ar')
                    ->where('facility_id', $facility_id)
                    ->whereNull('deleted_at')
                    ->ignore($journalId),
            ],
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('journals', 'name_en')
                    ->where('facility_id', $facility_id)
                    ->whereNull('deleted_at')
                    ->ignore($journalId),
            ],
            'description_ar' => ['sometimes', 'nullable', 'string'],
            'description_en' => ['sometimes', 'nullable', 'string'],
        ];

        if ($isProtected) {
            return $rules;
        }

        $rules += [
            'code' => [
                'required',
                'regex:/^[a-zA-Z0-9]+$/',
                Rule::unique('journals', 'code')
                    ->where('facility_id', $facility_id)
                    ->whereNull('deleted_at')
                    ->ignore($journalId),
                function ($attribute, $value, $fail) {
                    if ($this->filled('parent_id')) {
                        $parent = Journal::find($this->parent_id);
                        if ($parent && !str_starts_with($value, $parent->code)) {
                            $fail("الكود يجب أن يبدأ بكود الأب: {$parent->code}");
                        }
                    }
                },
            ],

            'currency_id' => [
                'nullable',
                'numeric',
                Rule::exists('currencies', 'id')->whereNull('deleted_at'),
            ],

            'type' => [
                'required',
                Rule::in(['assets', 'liabilities', 'equity', 'revenue', 'expense']),
            ],

            'parent_id' => [
                Rule::requiredIf(fn() => $journal && $journal->level != 1),
                Rule::prohibitedIf(fn() => $journal && $journal->level == 1),
                'nullable',
                'numeric',
                Rule::exists('journals', 'id')->whereNull('deleted_at'),
            ],

            'account_type' => [
                'required',
                Rule::in(['credit', 'debit']),
                function ($attribute, $value, $fail) {
                    if ($this->filled('parent_id')) {
                        $parent = Journal::find($this->parent_id);
                        if ($parent && $parent->account_type !== $value) {
                            $fail('يجب أن يكون نوع الحساب (دائن/مدين) مطابقًا للحساب الأب.');
                        }
                    }
                },
            ],
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [];
    }

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

        if ($this->failReason === 'not_active_facility') {
            $message = $lang === 'en'
                ? 'You cannot edit a journal because the facility is not active.'
                : 'لا يمكنك تعديل الحساب لان المنشاة غير مفعلة';
        } 

        if ($this->failReason === 'not_facility') {
            $message = $lang === 'en'
                ? 'This facility is not found.'
                : 'المنشاة غير موجودة.';
        } 

        if ($this->failReason === 'not_found') {
            $message = $lang === 'en'
                ? 'This item is not found.'
                : 'هذا العنصر غير موجود.';
        }
        // elseif ($this->failReason === 'not_children') {
        //     $message = $lang === 'en'
        //         ? 'You cannot edit the account because it has sub-accounts'
        //         : 'لا يمكنك تعديل الحساب لانه لديه حسابات فرعية';
        // } else {
        //     $message = $lang === 'en'
                // ? 'You cannot edit the account because it has journal entries'
                // : 'لا يمكنك تعديل الحساب لانه لديه قيود';
        // }

        // if ($this->failReason === 'no_transaction') {
        //     $message = $lang === 'en'
        //         ? 'You cannot add a new account because the primary account already has transactions.'
        //         : 'لا يمكنك اضافة حساب جديد لان الحساب الاساسى لديه معاملات';
        // }

        if($this->failReason === 'not_parent'){
            $message = $lang === 'en'
                ? 'This parent acount is not found.'
                : 'الحساب الرئيسى غير موجود.';
        }

        throw new HttpResponseException(
            respondError($message, 403)
        );
    }
}
