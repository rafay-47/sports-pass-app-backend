<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TrainerSpecialty extends Model
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
    protected $table = 'trainer_specialties';

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
        'specialty',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'string',
        'trainer_profile_id' => 'string',
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
     * Get the trainer profile that owns this specialty.
     */
    public function trainerProfile(): BelongsTo
    {
        return $this->belongsTo(TrainerProfile::class);
    }

    /**
     * Scope a query to filter by specialty.
     */
    public function scopeBySpecialty($query, $specialty)
    {
        return $query->where('specialty', 'ILIKE', "%{$specialty}%");
    }

    /**
     * Get popular specialties across all trainers.
     */
    public static function getPopularSpecialties($limit = 10)
    {
        return static::selectRaw('specialty, COUNT(*) as trainer_count')
                    ->groupBy('specialty')
                    ->orderByDesc('trainer_count')
                    ->limit($limit)
                    ->get();
    }
}
