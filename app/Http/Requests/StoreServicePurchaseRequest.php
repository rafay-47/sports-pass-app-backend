<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicePurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can create their own service purchases, admins can create for anyone
        return $this->user() && (
            $this->user()->user_role === 'admin' ||
            $this->user()->user_role === 'owner' ||
            $this->input('user_id') === $this->user()->id
        );
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    // Non-admin users can only create service purchases for themselves
                    if (!in_array($this->user()->user_role, ['admin', 'owner']) && $value !== $this->user()->id) {
                        $fail('You can only create service purchases for yourself.');
                    }
                },
            ],
            'membership_id' => [
                'required',
                'exists:memberships,id',
                function ($attribute, $value, $fail) {
                    // Check if membership belongs to the user
                    $membership = \App\Models\Membership::find($value);
                    if ($membership && $membership->user_id !== $this->input('user_id')) {
                        $fail('Selected membership does not belong to the specified user.');
                    }
                    
                    // Check if membership is active
                    if ($membership && $membership->status !== 'active') {
                        $fail('Selected membership is not active.');
                    }
                    
                    // Check if membership is not expired
                    if ($membership && $membership->expiry_date < now()) {
                        $fail('Selected membership has expired.');
                    }
                },
            ],
            'sport_service_id' => [
                'required',
                'exists:sport_services,id',
                function ($attribute, $value, $fail) {
                    // Check if sport service is active
                    $sportService = \App\Models\SportService::find($value);
                    if ($sportService && !$sportService->is_active) {
                        $fail('Selected sport service is not currently available.');
                    }
                    
                    // Check if sport service belongs to the membership's sport
                    $membership = \App\Models\Membership::find($this->input('membership_id'));
                    if ($sportService && $membership && $sportService->sport_id !== $membership->sport_id) {
                        $fail('Selected sport service does not belong to the membership\'s sport.');
                    }
                },
            ],
            'amount' => 'nullable|numeric|min:0|max:99999999.99',
            'status' => 'nullable|in:completed,cancelled,upcoming,expired',
            'service_date' => 'nullable|date',
            'service_time' => 'nullable|date_format:H:i',
            'provider' => 'nullable|string|max:200',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'membership_id' => 'membership',
            'sport_service_id' => 'sport service',
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
            'user_id.exists' => 'The selected user does not exist.',
            'membership_id.exists' => 'The selected membership does not exist.',
            'sport_service_id.exists' => 'The selected sport service does not exist.',
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

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If user_id is not provided and user is not admin/owner, set it to current user
        if (!$this->has('user_id') && !in_array($this->user()->user_role, ['admin', 'owner'])) {
            $this->merge(['user_id' => $this->user()->id]);
        }
    }
}