<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RevenueController extends Controller
{
    /** Page view for /revenue — required by revenue.index route */
    public function index()
    {
        return redirect()->route('analytics.index');
    }

    public function getRevenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $user = auth()->user();

        $query = \App\Models\Opportunity::where('stage', 'won');
        
        if ($user->isSales()) {
            $query->where('sales_id', $user->id);
        } elseif ($user->isFinance()) {
            // Finance lihat aggregate only
        }

        $data = match ($period) {
            'daily' => $this->getDailyRevenue($query),
            'weekly' => $this->getWeeklyRevenue($query),
            'monthly' => $this->getMonthlyRevenue($query),
            'yearly' => $this->getYearlyRevenue($query),
            default => $this->getMonthlyRevenue($query),
        };

        return response()->json($data);
    }

    public function getRevenuePerSales(Request $request)
    {
        abort_if(!auth()->user()->isGM(), 403);

        $data = \App\Models\Opportunity::where('stage', 'won')
            ->with('sales:id,name')
            ->selectRaw('sales_id, SUM(COALESCE(final_value, estimated_value, 0)) as total_revenue, COUNT(*) as total_bookings')
            ->groupBy('sales_id')
            ->orderByDesc('total_revenue')
            ->get()
            ->map(function($item) {
                return [
                    'sales_id' => $item->sales_id,
                    'sales_name' => $item->sales->name ?? 'Unknown',
                    'total_revenue' => (int)$item->total_revenue,
                    'total_bookings' => $item->total_bookings,
                    'avg_per_booking' => $item->total_bookings > 0 ? (int)($item->total_revenue / $item->total_bookings) : 0,
                ];
            });

        return response()->json($data);
    }

    /**
     * Cross-DB date format helper.
     * SQLite  → STRFTIME(fmt, col)
     * MySQL   → DATE_FORMAT(col, fmt)  [%Y-%m-%d / %Y-%m / %Y-%u / %Y]
     * PgSQL   → TO_CHAR(col, fmt)      [YYYY-MM-DD / YYYY-MM / YYYY]
     */
    private function dateExpr(string $type, string $column = 'created_at'): string
    {
        $driver = \DB::connection()->getDriverName();

        return match (true) {
            $driver === 'pgsql' => match ($type) {
                'day'   => "TO_CHAR({$column}, 'YYYY-MM-DD')",
                'week'  => "TO_CHAR({$column}, 'IYYY-IW')",
                'month' => "TO_CHAR({$column}, 'YYYY-MM')",
                'year'  => "TO_CHAR({$column}, 'YYYY')",
            },
            $driver === 'mysql' || $driver === 'mariadb' => match ($type) {
                'day'   => "DATE_FORMAT({$column}, '%Y-%m-%d')",
                'week'  => "DATE_FORMAT({$column}, '%Y-%u')",
                'month' => "DATE_FORMAT({$column}, '%Y-%m')",
                'year'  => "DATE_FORMAT({$column}, '%Y')",
            },
            default => match ($type) { // sqlite
                'day'   => "STRFTIME('%Y-%m-%d', {$column})",
                'week'  => "STRFTIME('%Y-%W', {$column})",
                'month' => "STRFTIME('%Y-%m', {$column})",
                'year'  => "STRFTIME('%Y', {$column})",
            },
        };
    }

    private function getDailyRevenue($query)
    {
        return $query->where('actual_close_date', '>=', Carbon::now()->subDays(30))
            ->selectRaw($this->dateExpr('day', 'actual_close_date') . " as date, SUM(COALESCE(final_value, estimated_value, 0)) as total")
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function getWeeklyRevenue($query)
    {
        return $query->where('actual_close_date', '>=', Carbon::now()->subWeeks(12))
            ->selectRaw($this->dateExpr('week', 'actual_close_date') . " as week, SUM(COALESCE(final_value, estimated_value, 0)) as total")
            ->groupBy('week')
            ->orderBy('week')
            ->get();
    }

    private function getMonthlyRevenue($query)
    {
        return $query->where('actual_close_date', '>=', Carbon::now()->subMonths(12))
            ->selectRaw($this->dateExpr('month', 'actual_close_date') . " as month, SUM(COALESCE(final_value, estimated_value, 0)) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    private function getYearlyRevenue($query)
    {
        return $query->selectRaw($this->dateExpr('year', 'actual_close_date') . " as year, SUM(COALESCE(final_value, estimated_value, 0)) as total")
            ->groupBy('year')
            ->orderBy('year')
            ->get();
    }
}
