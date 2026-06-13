<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'license_number',
        'status',
        'notes',
        'pool_id',
        'assigned_opportunity_id',
    ];

    protected $casts = [
        'pool_id' => 'integer',
        'assigned_opportunity_id' => 'integer',
    ];

    protected static function booted()
    {
        static::addGlobalScope('pool', function ($query) {
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->isOperational() && $user->pool_id !== null) {
                    $query->where('drivers.pool_id', $user->pool_id);
                }
            }
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function pool()
    {
        return $this->belongsTo(Pool::class);
    }

    public function assignedOpportunity()
    {
        return $this->belongsTo(Opportunity::class, 'assigned_opportunity_id');
    }
}
