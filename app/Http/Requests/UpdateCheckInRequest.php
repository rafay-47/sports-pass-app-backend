<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCheckInRequest extends FormRequest
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
            'check_out_time' => 'nullable|date|after:check_in_time',
            'location' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'check_out_time.after' => 'Check-out time must be after check-in time',
            'location.max' => 'Location cannot exceed 255 characters',
            'notes.max' => 'Notes cannot exceed 500 characters',
        ];
    }
}
