<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\App;

class UpdateRecipeRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $lang = $this->header('lang', 'ar');
        App::setLocale($lang);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can customize this if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'time' => 'nullable|integer',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'type' => 'required|integer|in:1,2',
            'item_code_id' => 'required|integer|exists:item_codes,id',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
            'images.*' => 'nullable|image|max:2048',
        ];
    }

    /**
     * Custom error messages.
     */
    public function messages(): array
    {
        return [
            'ingredients.required' => __('recipes.Please provide at least one ingredient.'),
            'ingredients.array' => __('Ingredients must be provided as an array.'),
            'ingredients.min' => __('Please provide at least one ingredient.'),
            'ingredients.*.product_id.required' => __('Each ingredient must have a valid product.'),
            'ingredients.*.product_id.exists' => __('The selected product does not exist.'),
            'ingredients.*.quantity.required' => __('Each ingredient must have a quantity.'),
            'ingredients.*.quantity.numeric' => __('Quantity must be a number.'),
            'ingredients.*.quantity.min' => __('Quantity must be at least 0.'),
            'ingredients.*.loss_percent.numeric' => __('Loss percentage must be a number.'),
            'ingredients.*.loss_percent.min' => __('Loss percentage must be at least 0.'),
            'ingredients.*.loss_percent.max' => __('Loss percentage cannot exceed 100.'),
            'images.*.image' => __('Uploaded files must be images.'),
            'images.*.max' => __('Images must not exceed 2 MB in size.'),
        ];
    }

    /**
     * Handle failed validation response.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            respondError(
                __('Validation Error.'),
                400,
                $validator->errors()
            )
        );
    }
}
