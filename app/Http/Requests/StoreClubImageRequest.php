<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubImageRequest extends FormRequest
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
            'club_id' => 'required|uuid|exists:clubs,id',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
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
            'club_id.required' => 'Club is required',
            'club_id.exists' => 'Selected club does not exist',
            'image.required' => 'Image is required',
            'image.image' => 'File must be an image',
            'image.mimes' => 'Image must be of type: jpeg, png, jpg, gif, webp',
            'image.max' => 'Image size cannot exceed 5MB',
            'alt_text.max' => 'Alt text cannot exceed 255 characters',
            'sort_order.integer' => 'Sort order must be a valid number',
            'sort_order.min' => 'Sort order cannot be negative',
        ];
    }
}
