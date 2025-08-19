<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TrainerAvailability extends Model
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
    protected $table = 'trainer_availability';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainer_profile_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_available',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'trainer_profile_id' => 'string',
        'day_of_week' => 'string', // Fixed: Changed from 'integer' to 'string'
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
        'created_at' => 'datetime',
    ];

    /**
     * Day names mapping for integer to string conversion.
     *
     * @var array
     */
    public static $dayNames = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    /**
     * Valid day names.
     *
     * @var array
     */
    public static $validDayNames = [
        'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
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
     * Get the trainer profile that owns this availability.
     */
    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    /**
     * Scope a query to only include available time slots.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to filter by day of week.
     */
    public function scopeByDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope a query to filter by time range.
     */
    public function scopeByTimeRange($query, $startTime, $endTime)
    {
        return $query->where('start_time', '>=', $startTime)
                    ->where('end_time', '<=', $endTime);
    }

    /**
     * Get the day name for this availability.
     */
    public function getDayNameAttribute(): string
    {
        // If day_of_week is already a string (day name), return it
        if (is_string($this->day_of_week) && in_array($this->day_of_week, self::$validDayNames)) {
            return $this->day_of_week;
        }
        
        // If day_of_week is an integer, convert it to day name
        if (is_numeric($this->day_of_week)) {
            return self::$dayNames[(int)$this->day_of_week] ?? 'Unknown';
        }
        
        return 'Unknown';
    }

    /**
     * Get formatted time range.
     */
    public function getTimeRangeAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' . 
               Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Check if a specific time falls within this availability slot.
     */
    public function isTimeAvailable(string $time): bool
    {
        if (!$this->is_available) {
            return false;
        }

        $checkTime = Carbon::parse($time);
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $checkTime->between($startTime, $endTime);
    }

    /**
     * Get duration in minutes.
     */
    public function getDurationInMinutes(): int
    {
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        return $startTime->diffInMinutes($endTime);
    }

    /**
     * Check if this availability overlaps with another time slot.
     */
    public function overlapsWithTimeSlot(string $startTime, string $endTime): bool
    {
        $slotStart = Carbon::parse($startTime);
        $slotEnd = Carbon::parse($endTime);
        $availStart = Carbon::parse($this->start_time);
        $availEnd = Carbon::parse($this->end_time);

        return $slotStart->lt($availEnd) && $slotEnd->gt($availStart);
    }

    /**
     * Convert integer day of week to day name.
     */
    public static function integerToDayName(int $dayOfWeek): string
    {
        return self::$dayNames[$dayOfWeek] ?? 'Unknown';
    }

    /**
     * Convert day name to integer day of week.
     */
    public static function dayNameToInteger(string $dayName): int
    {
        $mapping = array_flip(self::$dayNames);
        return $mapping[$dayName] ?? -1;
    }
}
