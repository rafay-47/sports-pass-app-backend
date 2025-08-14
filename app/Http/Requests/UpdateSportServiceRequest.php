<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSportServiceRequest extends FormRequest
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
            'service_name' => 'sometimes|required|string|max:100',
            'description' => 'nullable|string',
            'base_price' => 'nullable|numeric|min:0|max:99999999.99',
            'duration_minutes' => 'nullable|integer|min:1|max:1440',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
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
            'service_name.required' => 'The service name is required.',
            'service_name.max' => 'The service name must not exceed 100 characters.',
            'base_price.numeric' => 'The base price must be a valid number.',
            'base_price.min' => 'The base price must be at least 0.',
            'base_price.max' => 'The base price must not exceed 99,999,999.99.',
            'duration_minutes.integer' => 'The duration must be a whole number.',
            'duration_minutes.min' => 'The duration must be at least 1 minute.',
            'duration_minutes.max' => 'The duration must not exceed 1440 minutes (24 hours).',
            'discount_percentage.numeric' => 'The discount percentage must be a valid number.',
            'discount_percentage.min' => 'The discount percentage must be at least 0.',
            'discount_percentage.max' => 'The discount percentage must not exceed 100.',
        ];
    }
}
