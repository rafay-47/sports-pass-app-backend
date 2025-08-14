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
    protected $appends = ['active_services_count'];

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
}
