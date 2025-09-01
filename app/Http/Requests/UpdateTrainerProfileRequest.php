<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainerProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $trainerProfile = $this->route('trainerProfile');
        
        // Admins and owners can update any trainer profile
        if (in_array($this->user()->user_role, ['admin', 'owner'])) {
            return true;
        }
        
        // Users can only update their own trainer profile
        return $trainerProfile && $trainerProfile->user_id === $this->user()->id;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $trainerProfile = $this->route('trainerProfile');
        $isAdminOrOwner = in_array($this->user()->user_role, ['admin', 'owner']);

        $rules = [];

        // Only admins/owners can change these sensitive fields
        if ($isAdminOrOwner) {
            $rules = array_merge($rules, [
                'sport_id' => [
                    'sometimes',
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
                    'sometimes',
                    'exists:tiers,id',
                    function ($attribute, $value, $fail) {
                        // Check if tier belongs to the selected sport
                        $sportId = $this->input('sport_id') ?? $this->route('trainerProfile')->sport_id;
                        $tier = \App\Models\Tier::find($value);
                        if ($tier && $tier->sport_id !== $sportId) {
                            $fail('Selected tier does not belong to the selected sport.');
                        }
                        
                        // Check if tier is active and available
                        if ($tier && (!$tier->is_active || !$tier->is_available)) {
                            $fail('Selected tier is not currently available.');
                        }
                    },
                ],
                'is_verified' => 'sometimes|boolean',
                'rating' => 'sometimes|numeric|min:0|max:5',
                'total_sessions' => 'sometimes|integer|min:0',
                'total_earnings' => 'sometimes|numeric|min:0|max:9999999.99',
                'monthly_earnings' => 'sometimes|numeric|min:0|max:9999999.99',
            ]);
        }

        // All authenticated users can update these fields for their own profile
        $rules = array_merge($rules, [
            'experience_years' => 'sometimes|integer|min:0|max:50',
            'bio' => 'sometimes|nullable|string|max:1000',
            'gender_preference' => 'sometimes|nullable|in:male,female,both',
            'is_available' => 'sometimes|boolean',
            'club_ids' => 'sometimes|array',
            'club_ids.*' => 'uuid|exists:clubs,id',
            'certificates' => 'sometimes|array',
            'certificates.*.certification_name' => 'required|string|max:255',
            'certificates.*.issuing_organization' => 'required|string|max:255',
            'certificates.*.issue_date' => 'required|date|before_or_equal:today',
            'certificates.*.expiry_date' => 'nullable|date|after:issue_date',
            'certificates.*.certificate_url' => 'nullable|url|max:500',
            'certificates.*.is_verified' => 'boolean',
        ]);

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'sport_id' => 'sport',
            'tier_id' => 'tier',
            'experience_years' => 'years of experience',
            'gender_preference' => 'gender preference',
            'is_verified' => 'verification status',
            'is_available' => 'availability status',
            'rating' => 'rating',
            'total_sessions' => 'total sessions',
            'total_earnings' => 'total earnings',
            'monthly_earnings' => 'monthly earnings',
            'club_ids' => 'club IDs',
            'certificates' => 'certificates',
            'certificates.*.certification_name' => 'certificate name',
            'certificates.*.issuing_organization' => 'issuing organization',
            'certificates.*.issue_date' => 'issue date',
            'certificates.*.expiry_date' => 'expiry date',
            'certificates.*.certificate_url' => 'certificate URL',
            'certificates.*.is_verified' => 'verification status',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'sport_id.exists' => 'The selected sport does not exist.',
            'tier_id.exists' => 'The selected tier does not exist.',
            'experience_years.integer' => 'Experience years must be a valid number.',
            'experience_years.min' => 'Experience years cannot be negative.',
            'experience_years.max' => 'Experience years cannot exceed 50 years.',
            'bio.max' => 'Bio cannot exceed 1000 characters.',
            'gender_preference.in' => 'Gender preference must be male, female, or both.',
            'rating.numeric' => 'Rating must be a valid number.',
            'rating.min' => 'Rating cannot be negative.',
            'rating.max' => 'Rating cannot exceed 5.',
            'total_sessions.integer' => 'Total sessions must be a valid number.',
            'total_sessions.min' => 'Total sessions cannot be negative.',
            'total_earnings.numeric' => 'Total earnings must be a valid number.',
            'monthly_earnings.numeric' => 'Monthly earnings must be a valid number.',
            'club_ids.array' => 'Club IDs must be provided as an array.',
            'club_ids.*.exists' => 'One or more selected clubs do not exist.',
            'certificates.array' => 'Certificates must be provided as an array.',
            'certificates.*.certification_name.required' => 'Certificate name is required.',
            'certificates.*.issuing_organization.required' => 'Issuing organization is required.',
            'certificates.*.issue_date.required' => 'Issue date is required.',
            'certificates.*.issue_date.before_or_equal' => 'Issue date cannot be in the future.',
            'certificates.*.expiry_date.after' => 'Expiry date must be after the issue date.',
            'certificates.*.certificate_url.url' => 'Certificate URL must be a valid URL.',
            'certificates.*.certificate_url.max' => 'Certificate URL cannot exceed 500 characters.',
        ];
    }
}
