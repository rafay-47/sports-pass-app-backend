<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->user_role === 'admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sport_id' => 'sometimes|required|exists:sports,id',
            'tier_name' => 'sometimes|required|string|max:50',
            'display_name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'price' => 'sometimes|required|numeric|min:0|max:99999999.99',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'features' => 'nullable|array',
            'features.*' => 'string',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'sport_id.required' => 'The sport field is required.',
            'sport_id.exists' => 'The selected sport does not exist.',
            'tier_name.required' => 'The tier name is required.',
            'tier_name.max' => 'The tier name must not exceed 50 characters.',
            'display_name.required' => 'The display name is required.',
            'display_name.max' => 'The display name must not exceed 100 characters.',
            'price.required' => 'The price is required.',
            'price.numeric' => 'The price must be a valid number.',
            'price.min' => 'The price must be at least 0.',
            'price.max' => 'The price must not exceed 99,999,999.99.',
            'duration_days.integer' => 'The duration must be a whole number.',
            'duration_days.min' => 'The duration must be at least 1 day.',
            'duration_days.max' => 'The duration must not exceed 3650 days (10 years).',
            'discount_percentage.numeric' => 'The discount percentage must be a valid number.',
            'discount_percentage.min' => 'The discount percentage must be at least 0.',
            'discount_percentage.max' => 'The discount percentage must not exceed 100.',
            'start_date.date' => 'The start date must be a valid date.',
            'end_date.date' => 'The end date must be a valid date.',
            'end_date.after' => 'The end date must be after the start date.',
            'features.array' => 'Features must be an array.',
            'features.*.string' => 'Each feature must be a string.',
        ];
    }
}
