<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Role can be: gm|manager|sales|operational|finance
 * (Director dihapus — wewenangnya digabung ke GM sebagai pucuk pimpinan.)
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'manager_id',
        'sales_level',
        'dashboard_settings',
        'billing_pin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'billing_pin',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'dashboard_settings' => 'array',
    ];

    // Relationships
    public function clients()
    {
        return $this->hasMany(Client::class, 'assigned_sales_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'sales_id');
    }

    public function meetingLogs()
    {
        return $this->hasMany(MeetingLog::class, 'sales_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class, 'sales_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'sales_id');
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function approvalRequests()
    {
        return $this->hasMany(ApprovalRequest::class, 'requested_by');
    }

    // Role Checks
    /**
     * @deprecated Director dihapus. Selalu false. Disimpan sebagai alias aman
     * agar kode lama yang masih memanggil isDirector() tidak fatal error.
     */
    public function isDirector(): bool
    {
        return false;
    }

    public function isGM(): bool
    {
        return $this->role === 'gm';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isSales(): bool
    {
        return $this->role === 'sales';
    }

    public function isOperational(): bool
    {
        return $this->role === 'operational';
    }

    public function isFinance(): bool
    {
        return $this->role === 'finance';
    }
}
