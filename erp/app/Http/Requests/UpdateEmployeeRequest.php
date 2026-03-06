<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        // dd($user->hasRole('hr_manager'));

        if ($user && !$user->hasRole('hr_manager')) {
            return [
                'first_name'        => ['required'],
                'last_name'         => ['required'],
                'first_name_en'     => ['required'],
                'last_name_en'      => ['required'],
                'phone_number'      => ['required'],
                'country_code'      => ['required'],
                'gender'          => ['required', 'string'],
                'birth_date'        => ['required', 'date'],
                // 'image'             => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg|max:2048'],
                'nationality_id'       => ['required'],
                'marital_status' => ['required'],
                'email'             => ['required', 'email', 'max:255'],
                'current_address'   => ['required', 'string', 'max:255'],
                'home_country_address' => ['required', 'string', 'max:255'],
                'emergency_contact_one_name' => ['required', 'string'],
                'emergency_contact_one_relation'  => ['required', 'string'],
                'emergency_contact_one_phone'     => ['required', 'string'],
                'emergency_contact_two_name' => ['nullable', 'string'],
                'emergency_contact_two_relation'   => ['nullable', 'string'],
                'emergency_contact_two_phone'   =>['nullable ', 'string'],
                'Languages_Spoken' => ['required'],
                'Visa_Information'     => ['required'],
                'hear_about_us'    => ['required'],
                'Tasks_instructions'      => ['required'],

            ];
        }

        // 🔹 مستخدم عادي → صلاحيات محدودة (مثال: تعديل وسائل التواصل فقط)
        return [


            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'first_name_en'     => ['required', 'string', 'max:255'],
            'last_name_en'      => ['required', 'string', 'max:255'],
            'phone_number'      => ['required', 'string', 'max:20'],
            'country_code'      => ['required', 'string', 'max:10'],
            'gender'          => ['required', 'string'],
            'birth_date'        => ['required', 'date'],
            // 'image'             => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg|max:2048'],
            'nationality_id'       => ['required'],
            'marital_status' => ['required'],
            'email'             => ['required', 'email', 'max:255'],
            'current_address'   => ['required', 'string', 'max:255'],
            'home_country_address' => ['required', 'string', 'max:255'],
            'emergency_contact_one_name' => ['required', 'string'],
            'emergency_contact_one_relation'  => ['required', 'string'],
            'emergency_contact_one_phone'     => ['required', 'string'],
            'emergency_contact_two_name' => ['nullable', 'string'],
            'emergency_contact_two_relation'   => ['nullable', 'string'],
            'emergency_contact_two_phone'   =>['nullable', 'string'],
            'Languages_Spoken' => ['required'],
            'Visa_Information'     => ['required'],
            'hear_about_us'    => ['required'],
            'Tasks_instructions'      => ['required'],
            'branch_id'   => ['required'],
            'department_id'  => ['required'],
            'position'    => ['required'],
            'employment_type' => ['required'],
            'hire_date' => ['required'],
            'shift_id' => ['required'],
        ];
    }
}
