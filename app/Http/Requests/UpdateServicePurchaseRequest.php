<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServicePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $servicePurchase = $this->route('servicePurchase');
        
        // Users can update their own service purchases, admins can update any
        return $this->user() && (
            $this->user()->user_role === 'admin' ||
            $this->user()->user_role === 'owner' ||
            ($servicePurchase && $servicePurchase->user_id === $this->user()->id)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => 'sometimes|numeric|min:0|max:99999999.99',
            'status' => 'sometimes|in:completed,cancelled,upcoming,expired',
            'service_date' => 'sometimes|nullable|date',
            'service_time' => 'sometimes|nullable|date_format:H:i',
            'provider' => 'sometimes|nullable|string|max:200',
            'location' => 'sometimes|nullable|string|max:200',
            'notes' => 'sometimes|nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'service_date' => 'service date',
            'service_time' => 'service time',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.',
            'amount.max' => 'The amount is too large.',
            'status.in' => 'The status must be one of: completed, cancelled, upcoming, expired.',
            'service_date.date' => 'The service date must be a valid date.',
            'service_time.date_format' => 'The service time must be in HH:MM format.',
            'provider.max' => 'The provider name may not be greater than 200 characters.',
            'location.max' => 'The location may not be greater than 200 characters.',
            'notes.max' => 'The notes may not be greater than 1000 characters.',
        ];
    }
}