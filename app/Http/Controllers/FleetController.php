<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Booking;
use App\Models\MaintenanceLog;

class FleetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->isSales() || $user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $vehicles = Vehicle::with('pool')
            ->withCount([
                'bookings',
                'bookings as active_booking_count' => fn($q) => $q->whereIn('status', ['confirmed', 'on_trip']),
            ])
            ->when(request('status'), fn($q, $s) => $q->where('status', $s))
            ->orderBy('brand')
            ->paginate(20);

        $stats = [
            'available'   => Vehicle::where('status', 'available')->count(),
            'on_trip'     => Vehicle::where('status', 'on_trip')->count(),
            'maintenance' => Vehicle::where('status', 'maintenance')->count(),
            'inactive'    => Vehicle::where('status', 'inactive')->count(),
        ];

        return view('fleet.index', compact('vehicles', 'stats'));
    }

    public function show(Vehicle $vehicle)
    {
        $user = auth()->user();

        if ($user->isSales() || $user->isFinance()) {
            abort(403, 'Unauthorized');
        }

        $vehicle->load(['pool']);

        $bookings = Booking::where('vehicle_id', $vehicle->id)
            ->with(['client', 'sales', 'driver'])
            ->orderBy('pickup_datetime', 'desc')
            ->limit(10)
            ->get();

        $maintenanceLogs = MaintenanceLog::where('vehicle_id', $vehicle->id)
            ->orderBy('scheduled_date', 'desc')
            ->get();

        $activeBooking = Booking::where('vehicle_id', $vehicle->id)
            ->whereIn('status', ['confirmed', 'on_trip'])
            ->with('client', 'driver', 'sales')
            ->first();

        $nextMaintenance = MaintenanceLog::where('vehicle_id', $vehicle->id)
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date')
            ->first();

        return view('fleet.show', compact('vehicle', 'bookings', 'maintenanceLogs', 'activeBooking', 'nextMaintenance'));
    }

    public function apiAvailable()
    {
        $query = Vehicle::with('pool')
            ->where('status', 'available');
            
        if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }

    public function apiDriversAvailable()
    {
        $query = \App\Models\Driver::with('pool')
            ->where('status', 'available');

        if (request()->has('pool_id')) {
            $query->where('pool_id', request('pool_id'));
        }

        return response()->json($query->get());
    }
}
