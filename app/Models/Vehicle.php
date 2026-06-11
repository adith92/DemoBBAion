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
    ];

    protected $casts = [
        'stnk_expiry' => 'date',
        'pajak_expiry' => 'date',
        'current_km' => 'integer',
    ];

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

    public function contracts()
    {
        return $this->hasMany(VehicleContract::class);
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
