<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubImage extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'club_id',
        'image_url',
        'alt_text',
        'is_primary',
        'display_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'display_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'club_id' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the club that owns this image.
     */
    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    /**
     * Scope to get primary images.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute($value)
    {
        // If the value already contains a full URL, return it
        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }
        
        // Otherwise, prepend the storage URL
        return asset('storage/' . $value);
    }

    /**
     * Get the storage path for the image.
     */
    public function getImagePathAttribute()
    {
        return $this->attributes['image_url'];
    }
}