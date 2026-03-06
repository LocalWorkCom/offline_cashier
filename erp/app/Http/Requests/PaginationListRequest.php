<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;

class PaginationListRequest extends BasicFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',  // Default: 10
            'page' => 'nullable|integer|min:1',          // Default: 1
            'sort_dir' => 'nullable|string|in:asc,desc',    // Direction (def
        ];
    }

    public function getSort()
    {
        return $this->input('sort', 'desc');
    }

    public function getPerPage()
    {
        return $this->input('per_page');
    }
}
