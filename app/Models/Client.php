<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'pic_name',
        'phone',
        'email',
        'address',
        'industry',
        'status',
        'assigned_sales_id',
        'notes',
        'tier',
        'first_contact_date',
        'company_size',
    ];

    protected $casts = [
        'first_contact_date' => 'date',
    ];

    public function assignedSales()
    {
        return $this->belongsTo(User::class, 'assigned_sales_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function meetingLogs()
    {
        return $this->hasMany(MeetingLog::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

}
