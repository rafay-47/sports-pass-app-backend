<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Automatically set organizer_id to the authenticated user's ID
        // and status to 'draft' for new events
        $this->merge([
            'organizer_id' => $this->user()->id,
            'status' => $this->input('status', 'draft'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sport_id' => 'required|uuid|exists:sports,id',
            'club_id' => 'nullable|uuid|exists:clubs,id',
            'location_type' => 'required|in:club,custom',
            'custom_address' => 'nullable|string|max:255',
            'custom_city' => 'nullable|string|max:100',
            'custom_state' => 'nullable|string|max:100',
            'event_date' => 'required|date|after:today',
            'event_time' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'type' => 'required|in:tournament,workshop,class,competition',
            'category' => 'nullable|in:beginner,intermediate,advanced',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'fee' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1|max:1000',
            'organizer_id' => 'required|uuid|exists:users,id',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'prizes' => 'nullable|array',
            'prizes.*' => 'string|max:255',
            'registration_deadline' => 'nullable|date|before:event_date',
            'is_active' => 'boolean',
            'status' => 'in:draft,published',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $locationType = $this->input('location_type');
            $clubId = $this->input('club_id');
            $customAddress = $this->input('custom_address');

            if ($locationType === 'club') {
                if (!$clubId) {
                    $validator->errors()->add('club_id', 'Club ID is required when location type is club.');
                }
                if ($customAddress) {
                    $validator->errors()->add('custom_address', 'Custom address should not be provided when location type is club.');
                }
            } elseif ($locationType === 'custom') {
                if (!$customAddress) {
                    $validator->errors()->add('custom_address', 'Custom address is required when location type is custom.');
                }
                if ($clubId) {
                    $validator->errors()->add('club_id', 'Club ID should not be provided when location type is custom.');
                }
            }

            // If custom location is provided, ensure all required custom fields are present
            if ($locationType === 'custom' && $customAddress) {
                $customCity = $this->input('custom_city');
                $customState = $this->input('custom_state');
                
                if (!$customCity || !$customState) {
                    $validator->errors()->add('custom_location', 'When providing a custom address, both city and state are required.');
                }
            }
        });
    }
    public function messages(): array
    {
        return [
            'title.required' => 'Event title is required',
            'sport_id.required' => 'Sport is required',
            'sport_id.exists' => 'Selected sport does not exist',
            'club_id.exists' => 'Selected club does not exist',
            'location_type.required' => 'Location type is required',
            'location_type.in' => 'Location type must be either club or custom',
            'custom_address.max' => 'Custom address cannot exceed 255 characters',
            'custom_city.max' => 'Custom city cannot exceed 100 characters',
            'custom_state.max' => 'Custom state cannot exceed 100 characters',
            'event_date.required' => 'Event date is required',
            'event_date.after' => 'Event date must be in the future',
            'event_time.required' => 'Event time is required',
            'end_date.after_or_equal' => 'End date must be on or after event date',
            'type.required' => 'Event type is required',
            'type.in' => 'Event type must be one of: tournament, workshop, class, competition',
            'fee.required' => 'Event fee is required',
            'fee.numeric' => 'Fee must be a valid number',
            'fee.min' => 'Fee cannot be negative',
            'max_participants.required' => 'Maximum participants is required',
            'max_participants.integer' => 'Maximum participants must be a valid number',
            'max_participants.min' => 'Maximum participants must be at least 1',
            'max_participants.max' => 'Maximum participants cannot exceed 1000',
            'organizer_id.required' => 'Organizer is required',
            'organizer_id.exists' => 'Selected organizer does not exist',
            'registration_deadline.before' => 'Registration deadline must be before event date',
        ];
    }
}
