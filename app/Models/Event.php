<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'sport_id',
        'event_date',
        'event_time',
        'end_date',
        'end_time',
        'type',
        'category',
        'difficulty',
        'fee',
        'max_participants',
        'current_participants',
        'location',
        'organizer',
        'requirements',
        'prizes',
        'is_active',
        'registration_deadline',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
        'end_date' => 'date',
        'end_time' => 'datetime',
        'fee' => 'decimal:2',
        'max_participants' => 'integer',
        'current_participants' => 'integer',
        'requirements' => 'array',
        'prizes' => 'array',
        'is_active' => 'boolean',
        'registration_deadline' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'sport_id' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the sport for this event.
     */
    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the event registrations.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the club that hosts this event (based on location).
     */
    public function club()
    {
        return $this->belongsTo(Club::class, 'location', 'address');
    }

    /**
     * Scope to filter active events.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now()->toDateString());
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by sport.
     */
    public function scopeBySport($query, $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('event_date', [$startDate, $endDate]);
    }

    /**
     * Check if event is full.
     */
    public function isFull(): bool
    {
        return $this->current_participants >= $this->max_participants;
    }

    /**
     * Get available spots.
     */
    public function availableSpots(): int
    {
        return max(0, $this->max_participants - $this->current_participants);
    }
}
