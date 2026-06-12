@extends('layouts.app')

@section('header_title', 'Bookings')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Bookings'],
]" />

<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-wrap gap-2 justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            Bookings
            @if(request('status'))
                <span class="text-sm font-normal text-[var(--cc-text-muted)]">— {{ ucfirst(str_replace('_', ' ', request('status'))) }}</span>
            @endif
        </h2>
        <div class="flex flex-wrap gap-2 items-center">
            {{-- Status filters --}}
            <div class="flex gap-1 text-xs">
                <a href="{{ route('bookings.index', \Illuminate\Support\Arr::except(request()->query(), ['status'])) }}"
                   class="{{ !request('status') ? 'bg-blue-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">All</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'active'])) }}"
                   class="{{ request('status') === 'active' ? 'bg-purple-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Active</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'pending'])) }}"
                   class="{{ request('status') === 'pending' ? 'bg-yellow-500 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Pending</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'completed'])) }}"
                   class="{{ request('status') === 'completed' ? 'bg-green-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Completed</a>
                <a href="{{ route('bookings.index', array_merge(request()->query(), ['status' => 'cancelled'])) }}"
                   class="{{ request('status') === 'cancelled' ? 'bg-red-600 text-gray-900' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] border border-[var(--cc-border)]/50 hover:bg-[var(--cc-surface)] hover:text-[var(--cc-text)]' }} px-3 py-1.5 rounded-lg transition-colors font-medium">Cancelled</a>
            </div>

            @if(auth()->user()->isSales() || auth()->user()->isGM() || auth()->user()->isOperational())
                <a href="{{ route('bookings.create') }}" class="btn-3d">
                    ➕ New Booking
                </a>
            @endif
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[var(--cc-bg-muted)] border-b">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="px-4 py-3 text-left font-semibold">Booking #</th>
                    <th class="px-4 py-3 text-left font-semibold">Client</th>
                    <th class="px-4 py-3 text-left font-semibold">Sales</th>
                    <th class="px-4 py-3 text-left font-semibold">Vehicle</th>
                    <th class="px-4 py-3 text-left font-semibold">Pickup</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="px-4 py-3">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono font-semibold">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-[var(--cc-text)] hover:text-blue-600 hover:underline">
                            {{ $booking->client->company_name }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sales.performance', $booking->sales_id) }}" class="text-blue-600 hover:underline">
                            {{ $booking->sales->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3">
                        @if(auth()->user()->isGM() || auth()->user()->isOperational())
                            <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $booking->vehicle->plate_number }}
                            </a>
                        @else
                            <span class="font-mono text-[var(--cc-text)]">{{ $booking->vehicle->plate_number }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-[var(--cc-text-muted)]">{{ $booking->pickup_datetime->format('d M Y') }}</td>
                    <td class="px-4 py-3">
                        <x-status-badge :status="$booking->status"
                            :link="route('bookings.index', array_merge(request()->query(), ['status' => $booking->status]))" />
                    </td>
                    <td class="px-4 py-3 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-[var(--cc-text-muted)]">No bookings found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
