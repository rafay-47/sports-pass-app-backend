<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrainerRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            'membership' => $this->whenLoaded('membership', function () {
                return [
                    'id' => $this->membership->id,
                    'membership_number' => $this->membership->membership_number,
                    'status' => $this->membership->status,
                ];
            }),
            'sport' => $this->whenLoaded('sport', function () {
                return [
                    'id' => $this->sport->id,
                    'name' => $this->sport->display_name,
                ];
            }),
            'tier' => $this->whenLoaded('tier', function () {
                return [
                    'id' => $this->tier->id,
                    'name' => $this->tier->display_name,
                ];
            }),
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id' => $this->service->id,
                    'name' => $this->service->service_name,
                ];
            }),
            'request_type' => $this->request_type,
            'trainer_profile' => $this->whenLoaded('trainerProfile', function () {
                return [
                    'id' => $this->trainerProfile->id,
                    'user' => [
                        'id' => $this->trainerProfile->user->id,
                        'name' => $this->trainerProfile->user->name,
                    ],
                    'rating' => $this->trainerProfile->rating,
                ];
            }),
            'club' => $this->whenLoaded('club', function () {
                return [
                    'id' => $this->club->id,
                    'name' => $this->club->name,
                    'address' => $this->club->address,
                ];
            }),
            'preferred_time_slots' => $this->preferred_time_slots,
            'message' => $this->message,
            'status' => $this->status,
            'accepted_by_trainer' => $this->whenLoaded('acceptedByTrainer', function () {
                return [
                    'id' => $this->acceptedByTrainer->id,
                    'user' => [
                        'id' => $this->acceptedByTrainer->user->id,
                        'name' => $this->acceptedByTrainer->user->name,
                    ],
                ];
            }),
            'accepted_at' => $this->accepted_at,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
