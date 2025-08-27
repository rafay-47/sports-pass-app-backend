<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|in:pending,confirmed,cancelled',
            'payment_status' => 'sometimes|in:pending,paid,refunded',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, confirmed, cancelled',
            'payment_status.in' => 'Payment status must be one of: pending, paid, refunded',
            'payment_amount.numeric' => 'Payment amount must be a valid number',
            'payment_amount.min' => 'Payment amount cannot be negative',
            'payment_method.max' => 'Payment method cannot exceed 50 characters',
            'notes.max' => 'Notes cannot exceed 500 characters',
        ];
    }
}
