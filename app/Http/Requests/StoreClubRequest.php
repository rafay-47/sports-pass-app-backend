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
            'sport_id' => 'required|uuid|exists:sports,id',
            'description' => 'nullable|string',
            'address' => 'required|string',
            'city' => 'nullable|string|max:100',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'category' => 'required|in:male,female,mixed',
            'timings' => 'nullable|array',
            'is_active' => 'boolean',
            'amenities' => 'nullable|array',
            'amenities.*' => 'uuid|exists:amenities,id',
            'facilities' => 'nullable|array',
            'facilities.*' => 'uuid|exists:facilities,id'
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
            'sport_id.required' => 'Sport type is required',
            'sport_id.uuid' => 'Sport ID must be a valid UUID',
            'sport_id.exists' => 'Selected sport does not exist',
            'address.required' => 'Club address is required',
            'city.max' => 'City cannot exceed 100 characters',
            'latitude.required' => 'Latitude is required',
            'latitude.between' => 'Latitude must be between -90 and 90',
            'longitude.required' => 'Longitude is required',
            'longitude.between' => 'Longitude must be between -180 and 180',
            'phone.max' => 'Phone number cannot exceed 20 characters',
            'email.email' => 'Please provide a valid email address',
            'email.max' => 'Email cannot exceed 255 characters',
            'category.required' => 'Category is required',
            'category.in' => 'Category must be one of: male, female, mixed',
            'amenities.array' => 'Amenities must be an array',
            'amenities.*.uuid' => 'Each amenity ID must be a valid UUID',
            'amenities.*.exists' => 'Selected amenity does not exist',
            'facilities.array' => 'Facilities must be an array',
            'facilities.*.uuid' => 'Each facility ID must be a valid UUID',
            'facilities.*.exists' => 'Selected facility does not exist',
        ];
    }
}
