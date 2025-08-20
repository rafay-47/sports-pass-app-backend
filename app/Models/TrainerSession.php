<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainerSession extends Model
{
    use HasFactory;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trainer_sessions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainer_profile_id',
        'trainee_user_id',
        'trainee_membership_id',
        'session_date',
        'session_time',
        'duration_minutes',
        'start_time',
        'end_time',
        'status',
        'fee_amount',
        'payment_status',
        'location',
        'notes',
        'trainee_rating',
        'trainee_feedback',
        'trainer_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'trainer_profile_id' => 'string',
        'trainee_user_id' => 'string',
        'trainee_membership_id' => 'string',
        'session_date' => 'date',
        'session_time' => 'datetime:H:i',
        'duration_minutes' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'fee_amount' => 'decimal:2',
        'trainee_rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });

        // Validate business rules before saving
        static::saving(function ($model) {
            $model->validateBusinessRules();
        });
    }

    /**
     * Get the trainer profile that owns this session.
     */
    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    /**
     * Get the trainee user for this session.
     */
    public function traineeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainee_user_id');
    }

    /**
     * Get the trainee membership for this session.
     */
    public function traineeMembership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'trainee_membership_id');
    }

    /**
     * Scope a query to only include completed sessions.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include scheduled sessions.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope a query to only include cancelled sessions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to filter by session date.
     */
    public function scopeByDate($query, $date)
    {
        return $query->where('session_date', $date);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('session_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by payment status.
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Check if the session is in the past.
     */
    public function isPast(): bool
    {
        $sessionDateTime = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->session_time);
        return $sessionDateTime->isPast();
    }

    /**
     * Check if the session is today.
     */
    public function isToday(): bool
    {
        return $this->session_date->isToday();
    }

    /**
     * Check if the session is upcoming.
     */
    public function isUpcoming(): bool
    {
        $sessionDateTime = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->session_time);
        return $sessionDateTime->isFuture();
    }

    /**
     * Get the full session datetime.
     */
    public function getSessionDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->session_time);
    }

    /**
     * Check if the session can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['scheduled']) && $this->isUpcoming();
    }

    /**
     * Check if the session can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'scheduled' && ($this->isPast() || $this->isToday());
    }

    /**
     * Check if the session can be rated.
     */
    public function canBeRated(): bool
    {
        return $this->status === 'completed' && is_null($this->trainee_rating);
    }

    /**
     * Get formatted session time range.
     */
    public function getTimeRangeAttribute(): string
    {
        $startTime = Carbon::parse($this->session_time);
        $endTime = $startTime->copy()->addMinutes($this->duration_minutes);
        
        return $startTime->format('H:i') . ' - ' . $endTime->format('H:i');
    }

    /**
     * Get the session end time.
     */
    public function getEndTimeAttribute(): Carbon
    {
        return Carbon::parse($this->session_time)->addMinutes($this->duration_minutes);
    }

    /**
     * Validate business rules for trainer sessions.
     */
    public function validateBusinessRules(): void
    {
        // Validate that trainer and trainee membership belong to the same sport
        if ($this->trainer_profile_id && $this->trainee_membership_id) {
            $trainerProfile = TrainerProfile::find($this->trainer_profile_id);
            $membership = Membership::find($this->trainee_membership_id);
            
            if ($trainerProfile && $membership && $trainerProfile->sport_id !== $membership->sport_id) {
                throw new \InvalidArgumentException(
                    "Trainer and trainee membership must be for the same sport. Trainer sport: {$trainerProfile->sport_id}, Membership sport: {$membership->sport_id}"
                );
            }
        }

        // Validate session is not scheduled in the past
        if ($this->status === 'scheduled' && $this->session_date && $this->session_date < now()->toDateString()) {
            throw new \InvalidArgumentException(
                "Cannot schedule sessions in the past. Session date: {$this->session_date}"
            );
        }

        // Validate duration is positive
        if ($this->duration_minutes !== null && $this->duration_minutes <= 0) {
            throw new \InvalidArgumentException("Session duration must be positive. Got: {$this->duration_minutes}");
        }

        // Validate fee amount is non-negative
        if ($this->fee_amount !== null && $this->fee_amount < 0) {
            throw new \InvalidArgumentException("Session fee cannot be negative. Got: {$this->fee_amount}");
        }

        // Validate trainee rating is within valid range
        if ($this->trainee_rating !== null && ($this->trainee_rating < 1 || $this->trainee_rating > 5)) {
            throw new \InvalidArgumentException("Trainee rating must be between 1 and 5. Got: {$this->trainee_rating}");
        }
    }

    /**
     * Boot the model.
     */

}
