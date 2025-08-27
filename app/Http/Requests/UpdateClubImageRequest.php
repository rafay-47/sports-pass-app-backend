<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClubImageRequest extends FormRequest
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
            'type' => 'nullable|in:gallery,logo,banner,interior,exterior',
            'caption' => 'nullable|string|max:255',
            'alt_text' => 'nullable|string|max:255',
            'is_primary' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.in' => 'Image type must be one of: gallery, logo, banner, interior, exterior',
            'caption.max' => 'Caption cannot exceed 255 characters',
            'alt_text.max' => 'Alt text cannot exceed 255 characters',
            'sort_order.integer' => 'Sort order must be a valid number',
            'sort_order.min' => 'Sort order cannot be negative',
        ];
    }
}
