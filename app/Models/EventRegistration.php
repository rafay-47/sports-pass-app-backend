<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EventRegistration extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'event_id',
        'user_id',
        'registration_date',
        'status',
        'payment_status',
        'payment_amount',
        'notes',
    ];

    protected $casts = [
        'registration_date' => 'datetime',
        'payment_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id' => 'string',
        'event_id' => 'string',
        'user_id' => 'string',
    ];

    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get the event for this registration.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user for this registration.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by payment status.
     */
    public function scopeByPaymentStatus($query, $paymentStatus)
    {
        return $query->where('payment_status', $paymentStatus);
    }
}
