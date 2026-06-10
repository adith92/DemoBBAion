<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'opp_number',
        'client_id',
        'sales_id',
        'product_id',
        'title',
        'stage',
        'estimated_value',
        'final_value',
        'pax',
        'discount_percent',
        'discount_approved',
        'approved_by',
        'expected_close_date',
        'actual_close_date',
        'lost_reason',
        'notes',
        'booking_id',
        'subscription_id',
        'products',
        'history_timeline',
        'stage_changed_at',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
        'final_value' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discount_approved' => 'boolean',
        'expected_close_date' => 'date',
        'actual_close_date' => 'date',
        'pax' => 'integer',
        'products' => 'array',
        'history_timeline' => 'array',
        'stage_changed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opportunity) {
            if (empty($opportunity->opp_number)) {
                $yearMonth = now()->format('Ym');
                $prefix = 'OPP-' . $yearMonth . '-';
                $lastOpp = static::where('opp_number', 'like', $prefix . '%')
                    ->orderByDesc('opp_number')
                    ->first();

                if ($lastOpp) {
                    $lastSeq = (int) substr($lastOpp->opp_number, -4);
                    $seq = $lastSeq + 1;
                } else {
                    $seq = 1;
                }

                $opportunity->opp_number = $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
            }
            if (empty($opportunity->stage_changed_at)) {
                $opportunity->stage_changed_at = now();
            }
        });
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function sales()
    {
        return $this->belongsTo(User::class, 'sales_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }


    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // Scopes
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    // Accessors
    protected function stageColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->stage) {
                    'call_meeting' => 'purple',
                    'prospecting'  => 'blue',
                    'proposal'     => 'yellow',
                    'negotiation'  => 'orange',
                    'won'          => 'green',
                    'lost'         => 'red',
                    default        => 'gray',
                };
            }
        );
    }

    protected function stageLabel(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match ($this->stage) {
                    'call_meeting' => 'Call Meeting',
                    'prospecting'  => 'Prospekting',
                    'proposal'     => 'Proposal',
                    'negotiation'  => 'Negosiasi',
                    'won'          => 'Menang',
                    'lost'         => 'Kalah',
                    default        => ucfirst($this->stage),
                };
            }
        );
    }
}
