<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrainerCertification extends Model
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
    protected $table = 'trainer_certifications';

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
        'certification_name',
        'issuing_organization',
        'issue_date',
        'expiry_date',
        'certificate_url',
        'is_verified',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'trainer_profile_id' => 'string',
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
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
     * Get the trainer profile that owns this certification.
     */
    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    /**
     * Scope a query to only include verified certifications.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope a query to only include unverified certifications.
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope a query to only include non-expired certifications.
     */
    public function scopeValid($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expiry_date')
              ->orWhere('expiry_date', '>=', now()->toDateString());
        });
    }

    /**
     * Scope a query to only include expired certifications.
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expiry_date')
                    ->where('expiry_date', '<', now()->toDateString());
    }

    /**
     * Check if the certification is expired.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now()->toDateString();
    }

    /**
     * Check if the certification is valid (not expired).
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }

    /**
     * Get the number of days until expiry.
     */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Check if the certification is expiring soon (within 30 days).
     */
    public function isExpiringSoon(): bool
    {
        $daysUntilExpiry = $this->daysUntilExpiry();
        return $daysUntilExpiry !== null && $daysUntilExpiry <= 30 && $daysUntilExpiry >= 0;
    }
}
