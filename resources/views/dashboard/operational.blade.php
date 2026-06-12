@extends('layouts.app')

@section('header_title', 'Operational Dashboard')

@section('content')
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- KPI Cards --}}
    <div class="grid-stack-item" gs-id="w-available-fleet" gs-x="0" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-green-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Available Fleet</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $availableFleet }}</p>
                <p class="text-xs text-green-600 dark:text-green-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View available →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-on-trip" gs-x="3" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">On Trip</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $onTripFleet }}</p>
                <p class="text-xs text-blue-600 dark:text-blue-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View on trip →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-maintenance" gs-x="6" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('maintenance.index') }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-yellow-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Maintenance</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $maintenanceFleet }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View maintenance →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-active-bookings" gs-x="9" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-purple-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Active Bookings</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $activeBookings }}</p>
                <p class="text-xs text-purple-600 dark:text-purple-400 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View active →</p>
            </a>
        </div>
    </div>

    {{-- Active Trips Table --}}
    <div class="grid-stack-item" gs-id="w-active-trips" gs-x="0" gs-y="2" gs-w="12" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full overflow-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold text-[var(--cc-text)]">Active Trips</h3>
                    <a href="{{ route('bookings.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-xs font-medium">View all →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b" style="border-color:var(--cc-border)">
                            <tr style="color:var(--cc-text-muted)">
                                <th class="text-left py-2">Booking #</th>
                                <th class="text-left py-2">Client</th>
                                <th class="text-left py-2">Vehicle</th>
                                <th class="text-left py-2">Driver</th>
                                <th class="text-left py-2">Pickup</th>
                                <th class="text-left py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeBookingList as $booking)
                            <tr class="border-b hover:bg-black/5 dark:hover:bg-gray-100/5 transition-colors" style="border-color:var(--cc-border)">
                                <td class="py-2">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono">
                                        {{ $booking->booking_number }}
                                    </a>
                                </td>
                                <td class="py-2 text-[var(--cc-text)]">{{ $booking->client->company_name }}</td>
                                <td class="py-2">
                                    <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-mono">
                                        {{ $booking->vehicle->plate_number }}
                                    </a>
                                </td>
                                <td class="py-2 text-[var(--cc-text)]">{{ $booking->driver->name }}</td>
                                <td class="py-2 text-[var(--cc-text-muted)]">{{ $booking->pickup_datetime->format('d M H:i') }}</td>
                                <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="py-4 text-center text-[var(--cc-text-muted)]">No active trips right now</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-dashboard-grid>
@endsection
