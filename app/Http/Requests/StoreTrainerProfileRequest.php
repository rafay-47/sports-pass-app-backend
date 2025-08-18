<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Users can create their own trainer profile, admins can create for anyone
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
                    // Non-admin users can only create profiles for themselves
                    if (!in_array($this->user()->user_role, ['admin', 'owner']) && $value !== $this->user()->id) {
                        $fail('You can only create trainer profiles for yourself.');
                    }

                    // Check if user already has a trainer profile
                    $existingProfile = \App\Models\TrainerProfile::where('user_id', $value)->first();
                    if ($existingProfile) {
                        $fail('User already has a trainer profile.');
                    }

                    // Check if user is marked as trainer
                    $user = \App\Models\User::find($value);
                    if ($user && !$user->is_trainer) {
                        $fail('User must be marked as a trainer to create a trainer profile.');
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
                        $fail('Selected sport is not currently active.');
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
                },
            ],
            'experience_years' => 'required|integer|min:0|max:50',
            'bio' => 'nullable|string|max:1000',
            'hourly_rate' => 'nullable|numeric|min:0|max:99999.99',
            'gender_preference' => 'nullable|in:male,female,both',
            'is_available' => 'boolean',
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
            'experience_years' => 'years of experience',
            'hourly_rate' => 'hourly rate',
            'gender_preference' => 'gender preference',
            'is_available' => 'availability status',
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
            'experience_years.integer' => 'Experience years must be a valid number.',
            'experience_years.min' => 'Experience years cannot be negative.',
            'experience_years.max' => 'Experience years cannot exceed 50 years.',
            'hourly_rate.numeric' => 'Hourly rate must be a valid number.',
            'hourly_rate.min' => 'Hourly rate cannot be negative.',
            'hourly_rate.max' => 'Hourly rate is too high.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
            'gender_preference.in' => 'Gender preference must be male, female, or both.',
        ];
    }
}
