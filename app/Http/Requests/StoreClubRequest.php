<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClubRequest extends FormRequest
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
            'name' => 'required|string|max:200',
            'type' => 'required|string|max:100',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'price_range' => 'nullable|string|max:20',
            'category' => 'required|in:male,female,mixed',
            'timings' => 'nullable|array',
            'pricing' => 'nullable|array',
            'is_active' => 'boolean'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Club name is required',
            'name.max' => 'Club name cannot exceed 200 characters',
            'type.required' => 'Club type is required',
            'type.max' => 'Club type cannot exceed 100 characters',
            'address.required' => 'Club address is required',
            'city.max' => 'City cannot exceed 100 characters',
            'latitude.required' => 'Latitude is required',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.required' => 'Longitude is required',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'email.email' => 'Please provide a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'price_range.max' => 'Price range cannot exceed 20 characters',
            'category.required' => 'Category is required',
            'category.in' => 'Category must be one of: male, female, mixed',
        ];
    }
}
