<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TrainerProfile extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'sport_id',
        'tier_id',
        'experience_years',
        'bio',
        'hourly_rate',
        'rating',
        'total_sessions',
        'total_earnings',
        'monthly_earnings',
        'is_verified',
        'is_available',
        'gender_preference',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'experience_years' => 'integer',
        'hourly_rate' => 'decimal:2',
        'rating' => 'decimal:2',
        'total_sessions' => 'integer',
        'total_earnings' => 'decimal:2',
        'monthly_earnings' => 'decimal:2',
        'is_verified' => 'boolean',
        'is_available' => 'boolean',
    ];

    /**
     * The attributes that should be appended to arrays.
     */
    protected $appends = [
        'is_active_trainer',
        'availability_status',
        'rating_category',
        'experience_level',
    ];

    /**
     * Relationship: TrainerProfile belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: TrainerProfile belongs to a sport.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Relationship: TrainerProfile belongs to a tier.
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /**
     * Relationship: TrainerProfile has many certifications.
     */
    public function certifications(): HasMany
    {
        return $this->hasMany(TrainerCertification::class);
    }

    /**
     * Relationship: TrainerProfile has many specialties.
     */
    public function specialties(): HasMany
    {
        return $this->hasMany(TrainerSpecialty::class);
    }

    /**
     * Relationship: TrainerProfile has many availability slots.
     */
    public function availability(): HasMany
    {
        return $this->hasMany(TrainerAvailability::class);
    }

    /**
     * Relationship: TrainerProfile has many locations.
     */
    public function locations(): HasMany
    {
        return $this->hasMany(TrainerLocation::class);
    }

    /**
     * Relationship: TrainerProfile has many trainer sessions.
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(TrainerSession::class);
    }

    /**
     * Scope: Verified trainers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope: Available trainers.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope: Active trainers (verified and available).
     */
    public function scopeActive($query)
    {
        return $query->verified()->available();
    }

    /**
     * Scope: Trainers for a specific sport.
     */
    public function scopeForSport($query, $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Scope: Trainers by rating.
     */
    public function scopeByRating($query, $minRating = null, $maxRating = null)
    {
        if ($minRating !== null) {
            $query->where('rating', '>=', $minRating);
        }
        if ($maxRating !== null) {
            $query->where('rating', '<=', $maxRating);
        }
        return $query;
    }

    /**
     * Scope: Trainers by experience level.
     */
    public function scopeByExperience($query, $minYears = null, $maxYears = null)
    {
        if ($minYears !== null) {
            $query->where('experience_years', '>=', $minYears);
        }
        if ($maxYears !== null) {
            $query->where('experience_years', '<=', $maxYears);
        }
        return $query;
    }

    /**
     * Scope: Trainers by hourly rate range.
     */
    public function scopeByHourlyRate($query, $minRate = null, $maxRate = null)
    {
        if ($minRate !== null) {
            $query->where('hourly_rate', '>=', $minRate);
        }
        if ($maxRate !== null) {
            $query->where('hourly_rate', '<=', $maxRate);
        }
        return $query;
    }

    /**
     * Scope: Trainers by gender preference.
     */
    public function scopeByGenderPreference($query, $preference)
    {
        return $query->where(function ($q) use ($preference) {
            $q->where('gender_preference', $preference)
              ->orWhere('gender_preference', 'both');
        });
    }

    /**
     * Check if trainer is currently active (verified and available).
     */
    public function getIsActiveTrainerAttribute(): bool
    {
        return $this->is_verified && $this->is_available;
    }

    /**
     * Get availability status.
     */
    public function getAvailabilityStatusAttribute(): string
    {
        if (!$this->is_verified) {
            return 'pending_verification';
        }
        
        if (!$this->is_available) {
            return 'unavailable';
        }
        
        return 'available';
    }

    /**
     * Get rating category.
     */
    public function getRatingCategoryAttribute(): string
    {
        if ($this->rating >= 4.5) {
            return 'excellent';
        } elseif ($this->rating >= 4.0) {
            return 'very_good';
        } elseif ($this->rating >= 3.5) {
            return 'good';
        } elseif ($this->rating >= 3.0) {
            return 'average';
        } else {
            return 'below_average';
        }
    }

    /**
     * Get experience level.
     */
    public function getExperienceLevelAttribute(): string
    {
        if ($this->experience_years >= 10) {
            return 'expert';
        } elseif ($this->experience_years >= 5) {
            return 'senior';
        } elseif ($this->experience_years >= 2) {
            return 'intermediate';
        } else {
            return 'beginner';
        }
    }

    /**
     * Update trainer statistics.
     */
    public function updateStatistics(): void
    {
        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        $averageRating = $this->sessions()->where('status', 'completed')
            ->whereNotNull('trainee_rating')
            ->avg('trainee_rating');

        $this->update([
            'total_sessions' => $completedSessions,
            'rating' => $averageRating ? round($averageRating, 2) : 0.0,
        ]);
    }

    /**
     * Add earnings to the trainer profile.
     */
    public function addEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
        $this->increment('monthly_earnings', $amount);
    }

    /**
     * Reset monthly counters.
     */
    public function resetMonthlyCounters(): void
    {
        $this->update([
            'monthly_earnings' => 0,
        ]);
    }

    /**
     * Check if trainer is available on specific day and time.
     */
    public function isAvailableAt(int $dayOfWeek, string $time): bool
    {
        if (!$this->is_available) {
            return false;
        }

        return $this->availability()
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<=', $time)
            ->where('end_time', '>=', $time)
            ->where('is_available', true)
            ->exists();
    }

    /**
     * Get the route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
