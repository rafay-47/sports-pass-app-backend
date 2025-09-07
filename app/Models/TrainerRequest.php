<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainerRequest extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'membership_id',
        'sport_id',
        'tier_id',
        'service_id',
        'request_type',
        'trainer_profile_id',
        'club_id',
        'preferred_time_slots',
        'message',
        'status',
        'accepted_by_trainer_id',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'preferred_time_slots' => 'array',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(SportService::class, 'service_id');
    }

    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class, 'trainer_profile_id');
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function acceptedByTrainer(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class, 'accepted_by_trainer_id');
    }
}
