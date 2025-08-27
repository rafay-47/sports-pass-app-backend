<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_active;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'club_id' => 'required|uuid|exists:clubs,id',
            'user_id' => 'required|uuid|exists:users,id',
            'membership_id' => 'nullable|uuid|exists:memberships,id',
            'check_in_method' => 'nullable|in:manual,qr_code,app',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
            'qr_code_used' => 'nullable|string|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'club_id.required' => 'Club is required',
            'club_id.exists' => 'Selected club does not exist',
            'user_id.required' => 'User is required',
            'user_id.exists' => 'Selected user does not exist',
            'membership_id.exists' => 'Selected membership does not exist',
            'check_in_method.in' => 'Check-in method must be one of: manual, qr_code, app',
            'location.max' => 'Location cannot exceed 255 characters',
            'notes.max' => 'Notes cannot exceed 500 characters',
            'qr_code_used.max' => 'QR code cannot exceed 100 characters',
        ];
    }
}
