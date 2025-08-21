<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ServicePurchase extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'membership_id',
        'sport_service_id',
        'amount',
        'status',
        'service_date',
        'service_time',
        'provider',
        'location',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'service_date' => 'date',
        'service_time' => 'datetime:H:i',
    ];

    /**
     * The attributes that should be appended to arrays.
     */
    protected $appends = [
        'is_upcoming',
        'is_expired',
        'is_completed',
        'formatted_status',
        'service_datetime',
    ];

    /**
     * Relationship: Service purchase belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Service purchase belongs to a membership.
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Relationship: Service purchase belongs to a sport service.
     */
    public function sportService(): BelongsTo
    {
        return $this->belongsTo(SportService::class);
    }

    /**
     * Scope: Active service purchases.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['completed', 'upcoming']);
    }

    /**
     * Scope: Completed service purchases.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope: Upcoming service purchases.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming');
    }

    /**
     * Scope: Expired service purchases.
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    /**
     * Scope: Cancelled service purchases.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: For a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: For a specific membership.
     */
    public function scopeForMembership($query, $membershipId)
    {
        return $query->where('membership_id', $membershipId);
    }

    /**
     * Scope: For a specific sport service.
     */
    public function scopeForSportService($query, $sportServiceId)
    {
        return $query->where('sport_service_id', $sportServiceId);
    }

    /**
     * Scope: Within date range.
     */
    public function scopeWithinDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('service_date', [$startDate, $endDate]);
    }

    /**
     * Scope: This month's purchases.
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    /**
     * Check if service purchase is upcoming.
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->status === 'upcoming' && 
               $this->service_date && 
               $this->service_date >= now()->toDateString();
    }

    /**
     * Check if service purchase is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->status === 'expired' || 
               ($this->service_date && $this->service_date < now()->toDateString() && $this->status === 'upcoming');
    }

    /**
     * Check if service purchase is completed.
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Get formatted status.
     */
    public function getFormattedStatusAttribute(): string
    {
        return match($this->status) {
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'upcoming' => 'Upcoming',
            'expired' => 'Expired',
            default => 'Unknown'
        };
    }

    /**
     * Get combined service datetime.
     */
    public function getServiceDatetimeAttribute(): ?Carbon
    {
        if (!$this->service_date) {
            return null;
        }

        $date = Carbon::parse($this->service_date);
        
        if ($this->service_time) {
            $time = Carbon::parse($this->service_time);
            $date->setTime($time->hour, $time->minute, $time->second);
        }

        return $date;
    }

    /**
     * Mark service as completed.
     */
    public function markCompleted(): bool
    {
        return $this->update([
            'status' => 'completed',
            'service_date' => $this->service_date ?? now()->toDateString(),
        ]);
    }

    /**
     * Cancel the service purchase.
     */
    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Mark service as expired.
     */
    public function markExpired(): bool
    {
        return $this->update(['status' => 'expired']);
    }

    /**
     * Reschedule the service.
     */
    public function reschedule($newDate, $newTime = null): bool
    {
        return $this->update([
            'service_date' => $newDate,
            'service_time' => $newTime,
            'status' => 'upcoming',
        ]);
    }

    /**
     * Update service provider and location.
     */
    public function updateServiceDetails($provider = null, $location = null): bool
    {
        $updates = [];
        
        if ($provider !== null) {
            $updates['provider'] = $provider;
        }
        
        if ($location !== null) {
            $updates['location'] = $location;
        }

        return empty($updates) ? true : $this->update($updates);
    }

    /**
     * Get the route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Update membership spending when service purchase is created
        static::created(function ($servicePurchase) {
            $servicePurchase->updateMembershipSpending();
        });

        // Update membership spending when amount is changed
        static::updated(function ($servicePurchase) {
            if ($servicePurchase->isDirty('amount')) {
                $servicePurchase->updateMembershipSpending();
            }
        });
    }

    /**
     * Update membership spending totals.
     */
    protected function updateMembershipSpending(): void
    {
        if ($this->membership && $this->status === 'completed') {
            $this->membership->addSpending($this->amount);
        }
    }
}