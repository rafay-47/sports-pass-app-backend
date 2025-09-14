<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Club extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'owner_id',
        'name',
        'sport_id',
        'description',
        'address',
        'city',
        'latitude',
        'longitude',
        'phone',
        'email',
        'rating',
        'category',
        'qr_code',
        'status',
        'verification_status',
        'timings',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'timings' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'owner_id' => 'string',
        'sport_id' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the owner of the club.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the primary sport type of this club.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class, 'sport_id');
    }

    /**
     * Get the amenities available at this club.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'club_amenities')
            ->withPivot('custom_name');
    }

    /**
     * Get the facilities available at this club.
     */
    public function facilities(): BelongsToMany
    {
        return $this->belongsToMany(Facility::class, 'club_facilities')
            ->withPivot('custom_name');
    }

    /**
     * Get the trainers associated with this club.
     */
    public function trainers(): BelongsToMany
    {
        return $this->belongsToMany(TrainerProfile::class, 'trainer_clubs');
    }

    /**
     * Get the images for this club.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ClubImage::class)->orderBy('display_order');
    }

    /**
     * Get the primary image for this club.
     */
    public function primaryImage(): HasMany
    {
        return $this->hasMany(ClubImage::class)->where('is_primary', true);
    }

    /**
     * Get the check-ins for this club.
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Get the events hosted at this club.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Scope to filter active clubs.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    /**
     * Scope to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by verification status.
     */
    public function scopeByVerificationStatus($query, $status)
    {
        return $query->where('verification_status', $status);
    }

    /**
     * Scope to find clubs within a radius (in kilometers).
     */
    public function scopeWithinRadius($query, $latitude, $longitude, $radius = 10)
    {
        $distanceCalculation = '(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude))))';

        // Use a subquery to avoid PostgreSQL GROUP BY issues
        return $query->whereRaw(
            "{$distanceCalculation} <= ?",
            [$latitude, $longitude, $latitude, $radius]
        )->selectRaw(
            "*, {$distanceCalculation} AS distance",
            [$latitude, $longitude, $latitude]
        )->orderBy('distance');
    }

    /**
     * Scope to search clubs by name, description, or address.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'ILIKE', "%{$search}%")
              ->orWhere('description', 'ILIKE', "%{$search}%")
              ->orWhere('address', 'ILIKE', "%{$search}%")
              ->orWhere('city', 'ILIKE', "%{$search}%");
        });
    }

    /**
     * Get clubs owned by a specific user.
     */
    public function scopeOwnedBy($query, $userId)
    {
        return $query->where('owner_id', $userId);
    }

    /**
     * Get club statistics.
     */
    public function getStatistics(): array
    {
        $checkInsCount = $this->checkIns()->count();
        $uniqueVisitors = $this->checkIns()->distinct('user_id')->count();
        $eventsCount = $this->events()->count();
        $sportsCount = $this->sport ? 1 : 0;

        return [
            'total_check_ins' => $checkInsCount,
            'unique_visitors' => $uniqueVisitors,
            'total_events' => $eventsCount,
            'sports_offered' => $sportsCount,
            'average_rating' => $this->rating,
        ];
    }

    /**
     * Generate a unique QR code for the club.
     */
    public function generateQrCode(): string
    {
        do {
            $qrCode = 'CLUB-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }

    /**
     * Scope to find club by QR code.
     */
    public function scopeByQrCode($query, $qrCode)
    {
        return $query->where('qr_code', $qrCode);
    }

    /**
     * Find club by QR code.
     */
    public static function findByQrCode(string $qrCode): ?self
    {
        return self::byQrCode($qrCode)->first();
    }

    /**
     * Update club rating based on check-ins or reviews.
     */
    public function updateRating(): void
    {
        // This could be based on user reviews or check-in patterns
        // For now, we'll keep it simple
        $this->update(['rating' => $this->rating]);
    }

    /**
     * Check if the club is open at a specific time.
     */
    public function isOpenAt($dayOfWeek, $time): bool
    {
        $timings = $this->timings;

        if (!$timings || !isset($timings[$dayOfWeek])) {
            return false;
        }

        $daySchedule = $timings[$dayOfWeek];

        if (!$daySchedule['isOpen']) {
            return false;
        }

        $openTime = strtotime($daySchedule['open']);
        $closeTime = strtotime($daySchedule['close']);
        $checkTime = strtotime($time);

        return $checkTime >= $openTime && $checkTime <= $closeTime;
    }

    /**
     * Get the full address string.
     */
    public function getFullAddressAttribute(): string
    {
        return $this->address . ($this->city ? ', ' . $this->city : '');
    }
}
