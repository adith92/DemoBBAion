<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_number',
        'brand',
        'model',
        'capacity',
        'year',
        'status',
        'pool_id',
        'notes',
        'color',
        'transmission',
        'stnk_expiry',
        'pajak_expiry',
        'bbm_type',
        'current_km',
        'year_manufactured',
        'fuel_indicator',
        'insurance_expiry',
        'assigned_opportunity_id',
    ];

    protected $casts = [
        'stnk_expiry' => 'date',
        'pajak_expiry' => 'date',
        'current_km' => 'integer',
        'insurance_expiry' => 'date',
        'assigned_opportunity_id' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope('pool', function ($query) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->isOperational() && $user->pool_id !== null) {
                    $query->where('vehicles.pool_id', $user->pool_id);
                }
            }
        });
    }

    public function pool()
    {
        return $this->belongsTo(Pool::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function assignedOpportunity()
    {
        return $this->belongsTo(Opportunity::class, 'assigned_opportunity_id');
    }
}
