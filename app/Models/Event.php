<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'title',
        'description',
        'sport_id',
        'club_id',
        'location_type',
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
        'custom_address',
        'custom_city',
        'custom_state',
        'organizer_id',
        'requirements',
        'prizes',
        'is_active',
        'registration_deadline',
    ];

    protected $appends = ['formatted_location'];

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
        'location_type' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'sport_id' => 'string',
        'club_id' => 'string',
        'organizer_id' => 'string',
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
     * Get the organizer (user) for this event.
     */
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    /**
     * Get the event registrations.
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the club that hosts this event (if location type is club).
     */
    public function club()
    {
        return $this->belongsTo(Club::class);
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

    /**
     * Get the formatted location string.
     */
    public function getFormattedLocationAttribute(): string
    {
        switch ($this->location_type) {
            case 'club':
                return $this->club ? $this->club->name . ' - ' . $this->club->address : 'Club location';
            
            case 'custom':
                if ($this->custom_address) {
                    $location = $this->custom_address;
                    if ($this->custom_city) {
                        $location .= ', ' . $this->custom_city;
                    }
                    if ($this->custom_state) {
                        $location .= ', ' . $this->custom_state;
                    }
                    return $location;
                }
                return 'Custom location not specified';
            
            case 'legacy':
            default:
                return 'Location not specified';
        }
    }

    /**
     * Check if event has a club location.
     */
    public function hasClubLocation(): bool
    {
        return $this->location_type === 'club' && !is_null($this->club_id);
    }

    /**
     * Check if event has a custom location.
     */
    public function hasCustomLocation(): bool
    {
        return $this->location_type === 'custom' && !is_null($this->custom_address);
    }
}
