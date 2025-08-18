<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can create their own memberships, admins can create for anyone
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
                    // Non-admin users can only create memberships for themselves
                    if (!in_array($this->user()->user_role, ['admin', 'owner']) && $value !== $this->user()->id) {
                        $fail('You can only create memberships for yourself.');
                    }
                },
            ],
            'sport_id' => [
                'required',
                'exists:sports,id',
                function ($attribute, $value, $fail) {
                    // Check if sport is active
                    $sport = \App\Models\Sport::find($value);
                    if ($sport && !$sport->is_active) {
                        $fail('Selected sport is not currently available.');
                    }
                },
            ],
            'tier_id' => [
                'required',
                'exists:tiers,id',
                function ($attribute, $value, $fail) {
                    // Check if tier belongs to the selected sport
                    $tier = \App\Models\Tier::find($value);
                    if ($tier && $tier->sport_id !== $this->input('sport_id')) {
                        $fail('Selected tier does not belong to the selected sport.');
                    }
                    
                    // Check if tier is active and available
                    if ($tier && (!$tier->is_active || !$tier->is_available)) {
                        $fail('Selected tier is not currently available.');
                    }

                    // Check for existing active membership for the same sport
                    $existingMembership = \App\Models\Membership::where('user_id', $this->input('user_id'))
                        ->where('sport_id', $this->input('sport_id'))
                        ->where('status', 'active')
                        ->where('expiry_date', '>=', now())
                        ->exists();
                    
                    if ($existingMembership) {
                        $fail('User already has an active membership for this sport.');
                    }
                },
            ],
            'start_date' => 'nullable|date|after_or_equal:today',
            'auto_renew' => 'boolean',
            'purchase_amount' => 'nullable|numeric|min:0|max:99999999.99',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'user_id' => 'user',
            'sport_id' => 'sport',
            'tier_id' => 'tier',
            'start_date' => 'start date',
            'auto_renew' => 'auto renewal',
            'purchase_amount' => 'purchase amount',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'sport_id.exists' => 'The selected sport does not exist.',
            'tier_id.exists' => 'The selected tier does not exist.',
            'start_date.after_or_equal' => 'The start date must be today or a future date.',
            'purchase_amount.numeric' => 'The purchase amount must be a valid number.',
            'purchase_amount.min' => 'The purchase amount must be at least 0.',
            'purchase_amount.max' => 'The purchase amount is too large.',
        ];
    }
}
