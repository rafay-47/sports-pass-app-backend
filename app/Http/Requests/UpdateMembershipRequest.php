<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $membership = $this->route('membership');
        
        // Admins and owners can update any membership
        if (in_array($this->user()->user_role, ['admin', 'owner'])) {
            return true;
        }
        
        // Users can only update their own memberships (limited fields)
        return $membership && $membership->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $membership = $this->route('membership');
        $isAdminOrOwner = in_array($this->user()->user_role, ['admin', 'owner']);

        $rules = [];

        // Only admins/owners can change these fields
        if ($isAdminOrOwner) {
            $rules = array_merge($rules, [
                'status' => 'sometimes|string|in:active,paused,expired,cancelled',
                'expiry_date' => 'sometimes|date|after:start_date',
                'purchase_amount' => 'sometimes|numeric|min:0|max:99999999.99',
                'monthly_check_ins' => 'sometimes|integer|min:0|max:100',
                'total_spent' => 'sometimes|numeric|min:0|max:99999999.99',
                'monthly_spent' => 'sometimes|numeric|min:0|max:99999999.99',
                'total_earnings' => 'sometimes|numeric|min:0|max:99999999.99',
                'monthly_earnings' => 'sometimes|numeric|min:0|max:99999999.99',
            ]);
        }

        // All authenticated users can update these fields for their own membership
        $rules = array_merge($rules, [
            'auto_renew' => 'sometimes|boolean',
        ]);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'status' => 'membership status',
            'expiry_date' => 'expiry date',
            'purchase_amount' => 'purchase amount',
            'monthly_check_ins' => 'monthly check-ins',
            'total_spent' => 'total spent',
            'monthly_spent' => 'monthly spent',
            'total_earnings' => 'total earnings',
            'monthly_earnings' => 'monthly earnings',
            'auto_renew' => 'auto renewal',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'status.in' => 'The status must be one of: active, paused, expired, cancelled.',
            'expiry_date.after' => 'The expiry date must be after the start date.',
            'purchase_amount.numeric' => 'The purchase amount must be a valid number.',
            'purchase_amount.min' => 'The purchase amount must be at least 0.',
            'purchase_amount.max' => 'The purchase amount is too large.',
            'monthly_check_ins.min' => 'Monthly check-ins cannot be negative.',
            'monthly_check_ins.max' => 'Monthly check-ins cannot exceed 100.',
            'total_spent.numeric' => 'Total spent must be a valid number.',
            'monthly_spent.numeric' => 'Monthly spent must be a valid number.',
            'total_earnings.numeric' => 'Total earnings must be a valid number.',
            'monthly_earnings.numeric' => 'Monthly earnings must be a valid number.',
        ];
    }
}
