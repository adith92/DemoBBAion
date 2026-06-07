<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetPreference extends Model
{
    protected $fillable = ['user_id', 'widgets'];
    protected $casts    = ['widgets' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /** Return defaults if no user record exists */
    public static function defaultWidgets(): array
    {
        return [
            ['id' => 'kpi-row',       'label' => '📊 KPI Cards',          'visible' => true,  'order' => 1],
            ['id' => 'quick-shortcuts','label' => '⚡ Quick Shortcuts',     'visible' => true,  'order' => 2],
            ['id' => 'exec-summary',  'label' => '🤖 Executive Summary',   'visible' => true,  'order' => 3],
            ['id' => 'fleet-league',  'label' => '🏆 Fleet League',        'visible' => true,  'order' => 4],
            ['id' => 'revenue-chart', 'label' => '📈 Revenue Chart',       'visible' => true,  'order' => 5],
            ['id' => 'sales-ranking', 'label' => '🥇 Sales Ranking',       'visible' => true,  'order' => 6],
            ['id' => 'recent-books',  'label' => '🚌 Recent Bookings',     'visible' => true,  'order' => 7],
            ['id' => 'approval-q',    'label' => '✅ Approval Queue',      'visible' => true,  'order' => 8],
            ['id' => 'charts-section','label' => '📉 Analytics Charts',    'visible' => true,  'order' => 9],
        ];
    }

    public static function forUser(int $userId): array
    {
        $pref = static::where('user_id', $userId)->first();
        return $pref ? $pref->widgets : static::defaultWidgets();
    }
}
