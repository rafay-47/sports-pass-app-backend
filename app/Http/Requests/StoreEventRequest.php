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
        return $this->user() && in_array($this->user()->user_role, ['admin', 'owner']);
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
            'event_date' => 'required|date|after:today',
            'event_time' => 'required|date_format:Y-m-d H:i:s',
            'end_date' => 'nullable|date|after_or_equal:event_date',
            'end_time' => 'nullable|date_format:Y-m-d H:i:s',
            'type' => 'required|in:tournament,workshop,class,competition',
            'category' => 'nullable|in:beginner,intermediate,advanced',
            'difficulty' => 'nullable|in:easy,medium,hard',
            'fee' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1|max:1000',
            'location' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'prizes' => 'nullable|array',
            'prizes.*' => 'string|max:255',
            'registration_deadline' => 'nullable|date|before:event_date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Event title is required',
            'sport_id.required' => 'Sport is required',
            'sport_id.exists' => 'Selected sport does not exist',
            'club_id.exists' => 'Selected club does not exist',
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
            'registration_deadline.before' => 'Registration deadline must be before event date',
        ];
    }
}
