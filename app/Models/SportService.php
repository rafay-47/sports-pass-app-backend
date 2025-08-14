<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SportService extends Model
{
    use HasFactory, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Update sport service count when a service is created
        static::created(function (SportService $sportService) {
            $sportService->updateSportServiceCount();
        });

        // Update sport service count when a service is deleted
        static::deleted(function (SportService $sportService) {
            $sportService->updateSportServiceCount();
        });

        // Update sport service count when sport_id is changed
        static::updated(function (SportService $sportService) {
            if ($sportService->isDirty('sport_id')) {
                // Update count for old sport
                $oldSportId = $sportService->getOriginal('sport_id');
                if ($oldSportId) {
                    Sport::where('id', $oldSportId)->first()?->updateServiceCount();
                }
                // Update count for new sport
                $sportService->updateSportServiceCount();
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sport_id',
        'service_name',
        'description',
        'base_price',
        'duration_minutes',
        'discount_percentage',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the sport that owns the service.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Scope a query to only include active services.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate the discounted price.
     */
    public function getDiscountedPriceAttribute()
    {
        if (!$this->base_price || $this->discount_percentage <= 0) {
            return $this->base_price;
        }

        $discountAmount = ($this->base_price * $this->discount_percentage) / 100;
        return round($this->base_price - $discountAmount, 2);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Update the service count for the related sport.
     */
    public function updateSportServiceCount(): void
    {
        if ($this->sport_id) {
            $this->sport()->first()?->updateServiceCount();
        }
    }
}
