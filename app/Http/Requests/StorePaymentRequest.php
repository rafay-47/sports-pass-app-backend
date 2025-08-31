<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
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
            'user_id' => 'required|uuid|exists:users,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'string|size:3',
            'payment_method' => 'required|string|max:50',
            'payment_type' => 'required|string|max:50',
            'reference_id' => 'nullable|uuid',
            'payment_gateway_response' => 'nullable|array',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.uuid' => 'User ID must be a valid UUID',
            'user_id.exists' => 'User does not exist',
            'amount.required' => 'Payment amount is required',
            'amount.numeric' => 'Payment amount must be a number',
            'amount.min' => 'Payment amount must be greater than 0',
            'currency.size' => 'Currency must be exactly 3 characters',
            'payment_method.required' => 'Payment method is required',
            'payment_method.max' => 'Payment method cannot exceed 50 characters',
            'payment_type.required' => 'Payment type is required',
            'payment_type.max' => 'Payment type cannot exceed 50 characters',
            'reference_id.uuid' => 'Reference ID must be a valid UUID',
            'payment_gateway_response.array' => 'Payment gateway response must be an array',
        ];
    }
}
