@extends('layouts.app')

@section('header_title', 'Clients')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Clients'],
]" />

<div class="cc-card rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-900">
            All Clients
            <span class="text-sm font-normal text-gray-500 ml-2">({{ $clients->total() }} total)</span>
        </h2>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm resizable-table" data-table-id="clients-table">
            <thead class="border-b bg-gray-50">
                <tr class="text-gray-600">
                    <th class="text-left py-3 px-4">Company</th>
                    <th class="text-left py-3 px-4">PIC</th>
                    <th class="text-left py-3 px-4">Industry</th>
                    <th class="text-left py-3 px-4">Sales</th>
                    <th class="text-left py-3 px-4">Bookings</th>
                    <th class="text-left py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $client)
                <tr class="border-b hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('clients.show', $client->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-medium hover:underline">
                            {{ $client->company_name }}
                        </a>
                        <div class="text-xs text-gray-400">{{ $client->email }}</div>
                    </td>
                    <td class="py-3 px-4 text-gray-700">
                        <a href="mailto:{{ $client->email }}" class="text-blue-600 hover:underline">
                            {{ $client->pic_name }}
                        </a>
                    </td>
                    <td class="py-3 px-4 text-gray-500">{{ $client->industry ?? '—' }}</td>
                    <td class="py-3 px-4">
                        @if($client->assignedSales)
                            <a href="{{ route('sales.performance', $client->assignedSales->id) }}"
                               class="text-blue-600 hover:underline text-sm">
                                {{ $client->assignedSales->name }}
                            </a>
                        @else
                            <span class="text-gray-400 text-sm">Unassigned</span>
                        @endif
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('bookings.index', ['client_id' => $client->id]) }}"
                           class="text-blue-600 hover:underline font-medium">
                            {{ $client->bookings_count }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <x-status-badge :status="$client->status" />
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-gray-500">No clients found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clients->links() }}</div>
</div>
@endsection
