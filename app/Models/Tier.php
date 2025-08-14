<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class Tier extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sport_id',
        'tier_name',
        'display_name',
        'description',
        'icon',
        'color',
        'price',
        'duration_days',
        'discount_percentage',
        'start_date',
        'end_date',
        'features',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'duration_days' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['discounted_price', 'is_available'];

    /**
     * Get the sport that owns the tier.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Scope a query to only include active tiers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include available tiers (within date range).
     */
    public function scopeAvailable($query)
    {
        $now = Carbon::now()->toDateString();
        return $query->where(function ($q) use ($now) {
            $q->where(function ($subQ) use ($now) {
                $subQ->whereNull('start_date')
                     ->orWhere('start_date', '<=', $now);
            })->where(function ($subQ) use ($now) {
                $subQ->whereNull('end_date')
                     ->orWhere('end_date', '>=', $now);
            });
        });
    }

    /**
     * Scope a query to filter by price range.
     */
    public function scopePriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    /**
     * Calculate the discounted price.
     */
    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percentage <= 0) {
            return number_format($this->price, 2, '.', '');
        }

        $discountAmount = ($this->price * $this->discount_percentage) / 100;
        $discountedPrice = $this->price - $discountAmount;
        return number_format($discountedPrice, 2, '.', '');
    }

    /**
     * Check if the tier is currently available (within date range).
     */
    public function getIsAvailableAttribute(): bool
    {
        $now = Carbon::now()->toDateString();
        
        $startDateOk = !$this->start_date || $this->start_date <= $now;
        $endDateOk = !$this->end_date || $this->end_date >= $now;
        
        return $this->is_active && $startDateOk && $endDateOk;
    }

    /**
     * Get the expiration date for a membership starting today.
     */
    public function getMembershipExpirationDate(?Carbon $startDate = null): Carbon
    {
        $startDate = $startDate ?: Carbon::now();
        return $startDate->copy()->addDays($this->duration_days);
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return 'id';
    }
}
