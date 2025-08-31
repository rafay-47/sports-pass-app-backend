<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,processing,completed,failed,cancelled,refunded',
            'failure_reason' => 'nullable|string',
            'refund_amount' => 'nullable|numeric|min:0',
            'refund_date' => 'nullable|date',
            'payment_date' => 'nullable|date',
            'payment_gateway_response' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'Status must be one of: pending, processing, completed, failed, cancelled, refunded',
            'failure_reason.string' => 'Failure reason must be a string',
            'refund_amount.numeric' => 'Refund amount must be a number',
            'refund_amount.min' => 'Refund amount must be greater than or equal to 0',
            'refund_date.date' => 'Refund date must be a valid date',
            'payment_date.date' => 'Payment date must be a valid date',
            'payment_gateway_response.array' => 'Payment gateway response must be an array',
        ];
    }
}
