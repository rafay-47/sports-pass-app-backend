<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainerRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'membership_id' => 'required|uuid|exists:memberships,id',
            'service_id' => 'required|uuid|exists:sport_services,id',
            'request_type' => 'required|in:specific_trainer,open_request',
            'trainer_profile_id' => 'nullable|uuid|exists:trainer_profiles,id|required_if:request_type,specific_trainer',
            'club_id' => 'nullable|uuid|exists:clubs,id|required_if:request_type,open_request',
            'preferred_time_slots' => 'required|array|min:1',
            'preferred_time_slots.*.start' => 'required|date_format:H:i',
            'preferred_time_slots.*.end' => 'required|date_format:H:i|after:preferred_time_slots.*.start',
            'message' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'request_type.required' => 'Please specify the request type.',
            'trainer_profile_id.required_if' => 'Please select a trainer for specific requests.',
            'club_id.required_if' => 'Please select a club for open requests.',
            'preferred_time_slots.required' => 'Please provide at least one preferred time slot.',
        ];
    }
}
