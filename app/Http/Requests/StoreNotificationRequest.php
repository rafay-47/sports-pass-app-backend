<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|uuid|exists:users,id',
            'title' => 'required|string|max:200',
            'message' => 'required|string',
            'type' => 'required|string|in:info,success,warning,error,membership,event,trainer,checkin,payment',
            'action_url' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
            'expires_at' => 'nullable|date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.uuid' => 'User ID must be a valid UUID',
            'user_id.exists' => 'User does not exist',
            'title.required' => 'Notification title is required',
            'title.max' => 'Notification title cannot exceed 200 characters',
            'message.required' => 'Notification message is required',
            'type.required' => 'Notification type is required',
            'type.in' => 'Notification type must be one of: info, success, warning, error, membership, event, trainer, checkin, payment',
            'action_url.max' => 'Action URL cannot exceed 500 characters',
            'metadata.array' => 'Metadata must be an array',
            'expires_at.date' => 'Expiration date must be a valid date',
        ];
    }
}
