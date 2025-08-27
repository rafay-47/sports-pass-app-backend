<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sport extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'display_name',
        'icon',
        'color',
        'description',
        'number_of_services',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number_of_services' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [];

    /**
     * Get the services for the sport.
     */
    public function services(): HasMany
    {
        return $this->hasMany(SportService::class);
    }

    /**
     * Get the active services for the sport.
     */
    public function activeServices(): HasMany
    {
        return $this->hasMany(SportService::class)->where('is_active', true);
    }

    /**
     * Get the tiers for the sport.
     */
    public function tiers(): HasMany
    {
        return $this->hasMany(Tier::class);
    }

    /**
     * Get the active tiers for the sport.
     */
    public function activeTiers(): HasMany
    {
        return $this->hasMany(Tier::class)->where('is_active', true);
    }

    /**
     * Get the available tiers for the sport (active and within date range).
     * Note: This uses Carbon::now() so it's not efficient for eager loading.
     * Use availableOnDate() for better performance with specific dates.
     */
    public function availableTiers(): HasMany
    {
        return $this->activeTiers()->available();
    }

    /**
     * Get the trainer profiles for the sport.
     */
    public function trainerProfiles(): HasMany
    {
        return $this->hasMany(TrainerProfile::class);
    }

    /**
     * Get the events for the sport.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Get tiers available on a specific date (more efficient for eager loading).
     */
    public function availableOnDate($date = null): HasMany
    {
        $date = $date ?: now()->toDateString();
        return $this->activeTiers()
            ->where(function ($query) use ($date) {
                $query->where(function ($q) use ($date) {
                    $q->whereNull('start_date')
                      ->orWhere('start_date', '<=', $date);
                })->where(function ($q) use ($date) {
                    $q->whereNull('end_date')
                      ->orWhere('end_date', '>=', $date);
                });
            });
    }

    /**
     * Scope a query to only include active sports.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Update the number_of_services count based on actual services.
     */
    public function updateServiceCount(): void
    {
        $this->update([
            'number_of_services' => $this->services()->count()
        ]);
    }

    /**
     * Get the count of active services for this sport.
     */
    public function getActiveServicesCountAttribute(): int
    {
        return $this->activeServices()->count();
    }

    /**
     * Get the count of active tiers for this sport.
     */
    public function getActiveTiersCountAttribute(): int
    {
        return $this->activeTiers()->count();
    }

    /**
     * Calculate pricing information for active services.
     * This method should be used when services are already loaded to avoid N+1 queries.
     */
    public function getServicesPricingInfo(): array
    {
        // Use loaded services if available, otherwise query
        $services = $this->relationLoaded('activeServices') 
            ? $this->activeServices 
            : $this->activeServices()->get();

        $totalBasePrice = 0;
        $totalDiscountedPrice = 0;

        foreach ($services as $service) {
            $totalBasePrice += $service->base_price;
            $discountedPrice = $service->base_price;
            
            if ($service->discount_percentage > 0) {
                $discountAmount = ($service->base_price * $service->discount_percentage) / 100;
                $discountedPrice = $service->base_price - $discountAmount;
            }
            
            $totalDiscountedPrice += $discountedPrice;
        }

        return [
            'total_services_base_price' => round($totalBasePrice, 2),
            'total_services_discounted_price' => round($totalDiscountedPrice, 2),
            'total_services_savings' => round($totalBasePrice - $totalDiscountedPrice, 2),
            'services_count' => $services->count()
        ];
    }
}
