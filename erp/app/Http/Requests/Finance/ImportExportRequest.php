<?php

namespace App\Http\Requests\Finance;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ImportExportRequest extends FormRequest
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

        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls'
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_if' => 'A file is required for import',
            'file.mimes' => 'The file must be a CSV, XLSX, or JSON file',
        ];
    }

    protected function failedAuthorization()
    {
        $lang = $this->header('lang', 'ar');
        if ($this->failReason === 'not_active_facility') {
            $message = $lang === 'en'
                ? 'You cannot import new entry because the facility is not active.'
                : 'لا يمكنك استيراد شجرة حسابات لان المنشاة غير مفعلة';
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
