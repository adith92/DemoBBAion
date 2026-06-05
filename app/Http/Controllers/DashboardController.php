<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Client;
use App\Models\PurchaseOrder;
use App\Models\Vehicle;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:gm']);
    }

    public function gm()
    {
        // Quick shortcut stats
        $pendingPO = PurchaseOrder::where('status', 'submitted')->count();
        $availableVehicles = Vehicle::where('status', 'available')->count();
        $pendingDispatch = Booking::where('status', 'pending')->count();
        $upcomingMeetings = 0; // Placeholder jika table tidak ada

        // KPI Stats
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd = Carbon::now()->endOfMonth();

        $totalMonthlyRevenue = Booking::whereBetween('created_at', [$monthStart, $monthEnd])
            ->where('status', 'completed')
            ->sum('price');

        $completedBookings = Booking::whereMonth('created_at', Carbon::now()->month)
            ->where('status', 'completed')
            ->count();

        $avgRevenuePerBooking = $completedBookings > 0 
            ? $totalMonthlyRevenue / $completedBookings 
            : 0;

        $activeClients = Client::where('status', 'active')->count();

        $todayRevenue = Booking::whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('price');

        return view('dashboard.gm', [
            'pendingPO' => $pendingPO,
            'availableVehicles' => $availableVehicles,
            'pendingDispatch' => $pendingDispatch,
            'upcomingMeetings' => $upcomingMeetings,
            'totalMonthlyRevenue' => $totalMonthlyRevenue,
            'completedBookings' => $completedBookings,
            'avgRevenuePerBooking' => $avgRevenuePerBooking,
            'activeClients' => $activeClients,
            'todayRevenue' => $todayRevenue,
        ]);
    }
}
