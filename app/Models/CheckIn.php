<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckIn extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'membership_id',
        'club_id',
        'check_in_date',
        'check_in_time',
        'sport_type',
        'qr_code_used',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_in_time' => 'datetime',
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'user_id' => 'string',
        'membership_id' => 'string',
        'club_id' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the user that owns this check-in.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the membership for this check-in.
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }

    /**
     * Get the club for this check-in.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('check_in_date', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by club.
     */
    public function scopeByClub($query, $clubId)
    {
        return $query->where('club_id', $clubId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
