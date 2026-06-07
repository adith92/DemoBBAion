@extends('layouts.app')

@section('header_title', $client->company_name)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('clients.index'), 'label' => 'Clients'],
    ['url' => '#', 'label' => $client->company_name],
]" />

{{-- Hero --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">{{ $client->company_name }}</h2>
            <p class="text-gray-500 mt-1">{{ $client->industry }} · {{ $client->address }}</p>
        </div>
        <x-status-badge :status="$client->status" />
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t">
        <div>
            <p class="text-xs text-gray-500">PIC Name</p>
            <a href="mailto:{{ $client->email }}" class="font-semibold text-blue-600 hover:underline">{{ $client->pic_name }}</a>
        </div>
        <div>
            <p class="text-xs text-gray-500">Phone</p>
            <p class="font-semibold text-gray-900">{{ $client->phone }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Email</p>
            <p class="font-semibold text-gray-900 text-sm">{{ $client->email }}</p>
        </div>
        <div>
            <p class="text-xs text-gray-500">Assigned Sales</p>
            @if($client->assignedSales)
                <a href="{{ route('sales.performance', $client->assignedSales->id) }}"
                   class="font-semibold text-blue-600 hover:underline">
                    {{ $client->assignedSales->name }}
                </a>
            @else
                <p class="text-gray-400">Unassigned</p>
            @endif
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <a href="{{ route('finance.index', ['status' => 'paid']) }}" class="group block bg-green-50 rounded-lg shadow p-5 border-l-4 border-green-500 hover:shadow-md transition-all">
        <p class="text-sm text-gray-600">Total Paid</p>
        <p class="text-xl font-bold text-green-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_spend']) }}</p>
        <p class="text-xs text-green-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View paid invoices →</p>
    </a>

    <div class="bg-yellow-50 rounded-lg shadow p-5 border-l-4 border-yellow-500">
        <p class="text-sm text-gray-600">Pending</p>
        <p class="text-xl font-bold text-yellow-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_pending']) }}</p>
    </div>

    <div class="bg-red-50 rounded-lg shadow p-5 border-l-4 border-red-500">
        <p class="text-sm text-gray-600">Overdue</p>
        <p class="text-xl font-bold text-red-700 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_overdue']) }}</p>
    </div>

    <a href="{{ route('bookings.index', ['client_id' => $client->id]) }}" class="group block bg-blue-50 rounded-lg shadow p-5 border-l-4 border-blue-500 hover:shadow-md transition-all">
        <p class="text-sm text-gray-600">Total Bookings</p>
        <p class="text-xl font-bold text-blue-700 mt-1">{{ $stats['booking_count'] }}</p>
        <p class="text-xs text-blue-600 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
    </a>
</div>

{{-- Booking History --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Booking History</h3>
        <a href="{{ route('bookings.index', ['client_id' => $client->id]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr class="text-gray-500">
                    <th class="text-left py-2">Booking #</th>
                    <th class="text-left py-2">Vehicle</th>
                    <th class="text-left py-2">Sales</th>
                    <th class="text-left py-2">Pickup</th>
                    <th class="text-left py-2">Destination</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Price</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->bookings->sortByDesc('pickup_datetime')->take(10) as $booking)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 hover:underline font-mono">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-2">
                        @if(auth()->user()->isGM() || auth()->user()->isOperational())
                            <a href="{{ route('fleet.show', $booking->vehicle_id) }}" class="text-blue-600 hover:underline font-mono">
                                {{ $booking->vehicle->plate_number }}
                            </a>
                        @else
                            <span class="font-mono text-gray-700">{{ $booking->vehicle->plate_number }}</span>
                        @endif
                    </td>
                    <td class="py-2">
                        <a href="{{ route('sales.performance', $booking->sales_id) }}" class="text-blue-600 hover:underline">
                            {{ $booking->sales->name }}
                        </a>
                    </td>
                    <td class="py-2 text-gray-600">{{ $booking->pickup_datetime->format('d M Y') }}</td>
                    <td class="py-2 text-gray-600">{{ $booking->destination }}</td>
                    <td class="py-2"><x-status-badge :status="$booking->status" /></td>
                    <td class="py-2 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="py-4 text-center text-gray-500">No bookings yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Invoice Summary --}}
<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Invoice History</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b">
                <tr class="text-gray-500">
                    <th class="text-left py-2">Invoice #</th>
                    <th class="text-left py-2">Due Date</th>
                    <th class="text-left py-2">Status</th>
                    <th class="text-right py-2">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->invoices->sortByDesc('created_at')->take(10) as $invoice)
                <tr class="border-b hover:bg-gray-50">
                    <td class="py-2">
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="text-blue-600 hover:underline font-mono">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="py-2 text-gray-600">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                    <td class="py-2"><x-status-badge :status="$invoice->status" /></td>
                    <td class="py-2 text-right font-semibold">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-gray-500">No invoices yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Meeting Logs --}}
@if($client->meetingLogs->count())
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">Meeting Log</h3>
    <div class="space-y-3">
        @foreach($client->meetingLogs->sortByDesc('meeting_date')->take(5) as $meeting)
        <div class="border-l-4 border-blue-200 pl-4 py-2">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $meeting->outcome }}</p>
                    @if($meeting->notes)
                        <p class="text-xs text-gray-400 mt-1">{{ $meeting->notes }}</p>
                    @endif
                </div>
                <x-status-badge :status="$meeting->status" />
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
