<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Membership extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'membership_number',
        'user_id',
        'sport_id',
        'tier_id',
        'status',
        'purchase_date',
        'start_date',
        'expiry_date',
        'auto_renew',
        'purchase_amount',
        'monthly_check_ins',
        'total_spent',
        'monthly_spent',
        'total_earnings',
        'monthly_earnings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'purchase_date' => 'date',
        'start_date' => 'date',
        'expiry_date' => 'date',
        'auto_renew' => 'boolean',
        'purchase_amount' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'monthly_spent' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'monthly_earnings' => 'decimal:2',
        'monthly_check_ins' => 'integer',
    ];

    /**
     * The attributes that should be appended to arrays.
     */
    protected $appends = [
        'is_active',
        'is_expired',
        'days_remaining',
        'usage_percentage',
    ];

    /**
     * Generate membership number before creating.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($membership) {
            if (empty($membership->membership_number)) {
                $membership->membership_number = self::generateMembershipNumber();
            }
        });
    }

    /**
     * Relationship: Membership belongs to a user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Membership belongs to a sport.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Relationship: Membership belongs to a tier.
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /**
     * Relationship: Membership has many check-ins.
     */
    public function checkIns(): HasMany
    {
        return $this->hasMany(CheckIn::class);
    }

    /**
     * Relationship: Membership has many service purchases.
     */
    public function servicePurchases(): HasMany
    {
        return $this->hasMany(ServicePurchase::class);
    }

    /**
     * Get available services for this membership (through sport).
     */
    public function availableServices()
    {
        return $this->sport->services()->active();
    }

    /**
     * Scope: Active memberships.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Expired memberships.
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    /**
     * Scope: Expiring soon (within 30 days).
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>=', now());
    }

    /**
     * Scope: For a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: For a specific sport.
     */
    public function scopeForSport($query, $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Check if membership is currently active.
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expiry_date >= now();
    }

    /**
     * Check if membership is expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date < now();
    }

    /**
     * Get days remaining until expiry.
     */
    public function getDaysRemainingAttribute(): int
    {
        if ($this->is_expired) {
            return 0;
        }
        
        return max(0, now()->diffInDays($this->expiry_date, false));
    }

    /**
     * Get monthly usage percentage.
     */
    public function getUsagePercentageAttribute(): float
    {
        $maxCheckIns = 30; // Monthly limit
        return min(100, ($this->monthly_check_ins / $maxCheckIns) * 100);
    }

    /**
     * Generate unique membership number.
     */
    public static function generateMembershipNumber(): string
    {
        do {
            $number = 'MEM' . strtoupper(uniqid());
        } while (self::where('membership_number', $number)->exists());

        return $number;
    }

    /**
     * Renew the membership.
     */
    public function renew(): bool
    {
        if (!$this->tier) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'start_date' => now(),
            'expiry_date' => now()->addDays($this->tier->duration_days),
            'purchase_amount' => $this->tier->price,
            'monthly_check_ins' => 0,
            'monthly_spent' => 0,
        ]);

        return true;
    }

    /**
     * Pause the membership.
     */
    public function pause(): bool
    {
        return $this->update(['status' => 'paused']);
    }

    /**
     * Resume the membership.
     */
    public function resume(): bool
    {
        return $this->update(['status' => 'active']);
    }

    /**
     * Cancel the membership.
     */
    public function cancel(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Add spending to the membership.
     */
    public function addSpending(float $amount): void
    {
        $this->increment('total_spent', $amount);
        $this->increment('monthly_spent', $amount);
    }

    /**
     * Add earnings to the membership (for trainers).
     */
    public function addEarnings(float $amount): void
    {
        $this->increment('total_earnings', $amount);
        $this->increment('monthly_earnings', $amount);
    }

    /**
     * Increment check-in counter for the membership.
     */
    public function incrementCheckIn(): void
    {
        $this->increment('monthly_check_ins');
    }

    /**
     * Reset monthly counters.
     */
    public function resetMonthlyCounters(): void
    {
        $this->update([
            'monthly_check_ins' => 0,
            'monthly_spent' => 0,
            'monthly_earnings' => 0,
        ]);
    }

    /**
     * Check if this membership allows access to a specific club.
     */
    public function canAccessClub(string $clubId): bool
    {
        // Check if the membership's sport matches the club's sport
        $club = Club::find($clubId);
        return $club && $this->sport_id === $club->sport_id;
    }

    /**
     * Get the route key name for model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
