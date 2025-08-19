<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrainerLocation extends Model
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
    protected $table = 'trainer_locations';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainer_profile_id',
        'location_name',
        'location_type',
        'address',
        'city',
        'area',
        'latitude',
        'longitude',
        'is_primary',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'trainer_profile_id' => 'string',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_primary' => 'boolean',
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
    }

    /**
     * Get the trainer profile that owns this location.
     */
    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    /**
     * Scope a query to filter by location name.
     */
    public function scopeByLocationName($query, $name)
    {
        return $query->where('location_name', 'ILIKE', "%{$name}%");
    }

    /**
     * Scope a query to filter by address.
     */
    public function scopeByAddress($query, $address)
    {
        return $query->where('address', 'ILIKE', "%{$address}%");
    }

    /**
     * Scope a query to find locations within a certain radius.
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radiusKm = 10)
    {
        return $query->whereRaw(
            '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) <= ?',
            [$latitude, $longitude, $latitude, $radiusKm]
        );
    }

    /**
     * Calculate distance to another point in kilometers.
     */
    public function distanceTo($latitude, $longitude): float
    {
        if (!$this->latitude || !$this->longitude) {
            return 0;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Get formatted coordinates.
     */
    public function getCoordinatesAttribute(): ?string
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        return "{$this->latitude}, {$this->longitude}";
    }

    /**
     * Check if location has valid coordinates.
     */
    public function hasValidCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * Get Google Maps URL for this location.
     */
    public function getGoogleMapsUrl(): string
    {
        if ($this->hasValidCoordinates()) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }

        return "https://www.google.com/maps/search/" . urlencode($this->address ?? $this->location_name);
    }
}
