@extends('layouts.app')

@section('header_title', 'Fleet Management')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Fleet'],
]" />

{{-- Fleet Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <!-- Available -->
    <a href="{{ route('fleet.index', ['status' => 'available']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-emerald-200 dark:border-emerald-900/30 bg-gradient-to-br from-emerald-50 to-emerald-100/30 dark:from-emerald-950/20 dark:to-emerald-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(16,185,129,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(16,185,129,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-emerald-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-emerald-500/20">check_circle</span>
        </div>
        <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $stats['available'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-emerald-800/60 dark:text-emerald-400/60 mt-1">Available</p>
    </a>

    <!-- On Trip -->
    <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-blue-200 dark:border-blue-900/30 bg-gradient-to-br from-blue-50 to-blue-100/30 dark:from-blue-950/20 dark:to-blue-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(59,130,246,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(59,130,246,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-blue-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-blue-500/20">local_shipping</span>
        </div>
        <p class="text-3xl font-black text-blue-600 dark:text-blue-400 tracking-tight">{{ $stats['on_trip'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-blue-800/60 dark:text-blue-400/60 mt-1">On Trip</p>
    </a>

    <!-- Maintenance -->
    <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" 
       class="group relative block overflow-hidden rounded-2xl p-5 border border-orange-200 dark:border-orange-900/30 bg-gradient-to-br from-orange-50 to-orange-100/30 dark:from-orange-950/20 dark:to-orange-900/5 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(249,115,22,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(249,115,22,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-orange-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-orange-500/20">build</span>
        </div>
        <p class="text-3xl font-black text-orange-600 dark:text-orange-400 tracking-tight">{{ $stats['maintenance'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-orange-800/60 dark:text-orange-400/60 mt-1">Maintenance</p>
    </a>

    <!-- Inactive -->
    <div class="group relative block overflow-hidden rounded-2xl p-5 border border-slate-200 dark:border-slate-800 bg-gradient-to-br from-slate-50 to-slate-100/30 dark:from-slate-900/40 dark:to-slate-850/10 hover:-translate-y-1 hover:shadow-[0_12px_24px_-8px_rgba(100,116,139,0.3)] dark:hover:shadow-[0_12px_24px_-8px_rgba(100,116,139,0.15)] transition-all duration-300 text-center">
        <div class="absolute -right-3 -top-3 w-16 h-16 bg-slate-500/5 rounded-full flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
            <span class="material-symbols-outlined text-[32px] text-slate-500/20">cancel</span>
        </div>
        <p class="text-3xl font-black text-slate-600 dark:text-slate-400 tracking-tight">{{ $stats['inactive'] }}</p>
        <p class="text-xs font-bold uppercase tracking-widest text-slate-800/60 dark:text-slate-400/60 mt-1">Inactive</p>
    </div>
</div>

{{-- Vehicle List / Grid --}}
<div class="cc-card rounded-lg shadow p-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <h2 class="text-xl font-semibold text-[var(--cc-text)]">
            All Vehicles
            <span class="text-sm font-normal text-[var(--cc-text-muted)] ml-2">({{ $vehicles->total() }} total)</span>
        </h2>
        
        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-end">
            {{-- Filter by status --}}
            <div class="flex gap-1 text-sm bg-[var(--cc-bg-muted)] rounded-lg p-1 border border-[var(--cc-border)]">
                <a href="{{ route('fleet.index') }}" class="{{ !request('status') ? 'bg-blue-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">All</a>
                <a href="{{ route('fleet.index', ['status' => 'available']) }}" class="{{ request('status') === 'available' ? 'bg-green-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">Available</a>
                <a href="{{ route('fleet.index', ['status' => 'on_trip']) }}" class="{{ request('status') === 'on_trip' ? 'bg-blue-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">On Trip</a>
                <a href="{{ route('fleet.index', ['status' => 'maintenance']) }}" class="{{ request('status') === 'maintenance' ? 'bg-orange-600 text-white' : 'text-[var(--cc-text-muted)] hover:text-[var(--cc-text)]' }} px-3 py-1 rounded-md transition-colors font-medium">Maint.</a>
            </div>
            
            {{-- Grid/List Toggle --}}
            <div class="flex border border-[var(--cc-border)] rounded-lg overflow-hidden bg-[var(--cc-bg-muted)] p-0.5">
                <button type="button" id="btn-toggle-list" onclick="setFleetView('list')" class="p-1.5 rounded-md flex items-center justify-center transition-all" title="List View">
                    <span class="material-symbols-outlined text-[18px]">list</span>
                </button>
                <button type="button" id="btn-toggle-grid" onclick="setFleetView('grid')" class="p-1.5 rounded-md flex items-center justify-center transition-all" title="Grid View">
                    <span class="material-symbols-outlined text-[18px]">grid_view</span>
                </button>
            </div>
        </div>
    </div>

    {{-- List Layout --}}
    <div id="fleet-list-view" class="overflow-x-auto">
        <table class="w-full text-sm resizable-table" data-table-id="fleet-table">
            <thead class="border-b bg-[var(--cc-bg-muted)]">
                <tr class="text-[var(--cc-text-muted)]">
                    <th class="text-left py-3 px-4">Plate Number</th>
                    <th class="text-left py-3 px-4">Brand</th>
                    <th class="text-left py-3 px-4">Model</th>
                    <th class="text-center py-3 px-4">Capacity</th>
                    <th class="text-center py-3 px-4">Year</th>
                    <th class="text-left py-3 px-4">Pool</th>
                    <th class="text-left py-3 px-4">Status</th>
                    <th class="text-center py-3 px-4">Bookings</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr class="border-b hover:bg-[var(--cc-row-hover)] transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $vehicle->id) }}"
                           class="text-blue-600 hover:text-blue-800 font-mono font-semibold hover:underline">
                            {{ $vehicle->plate_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <span class="capitalize text-[var(--cc-text)]">{{ $vehicle->brand }}</span>
                    </td>
                    <td class="py-3 px-4 text-[var(--cc-text)]">{{ $vehicle->model }}</td>
                    <td class="py-3 px-4 text-center text-[var(--cc-text-muted)]">{{ $vehicle->capacity }} pax</td>
                    <td class="py-3 px-4 text-center text-[var(--cc-text-muted)]">{{ $vehicle->year }}</td>
                    <td class="py-3 px-4 text-[var(--cc-text-muted)]">{{ $vehicle->pool?->name ?? '—' }}</td>
                    <td class="py-3 px-4"><x-status-badge :status="$vehicle->status" /></td>
                    <td class="py-3 px-4 text-center">
                        <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}"
                           class="text-blue-600 hover:underline font-medium">
                            {{ $vehicle->bookings_count }}
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="py-8 text-center text-[var(--cc-text-muted)]">No vehicles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Grid Layout --}}
    <div id="fleet-grid-view" class="hidden">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse($vehicles as $vehicle)
            <div class="cc-card rounded-2xl border border-[var(--cc-border)] overflow-hidden shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                <!-- Top Content -->
                <div class="p-5 flex-1">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="font-mono font-bold text-sm tracking-wide bg-[var(--cc-bg-muted)] text-[var(--cc-text)] px-2.5 py-1 rounded-lg border border-[var(--cc-border)]">
                            {{ $vehicle->plate_number }}
                        </span>
                        <x-status-badge :status="$vehicle->status" />
                    </div>

                    <h3 class="text-base font-extrabold text-[var(--cc-text)] capitalize">{{ $vehicle->brand }}</h3>
                    <p class="text-xs text-[var(--cc-text-muted)] mb-4">{{ $vehicle->model }} ({{ $vehicle->year }})</p>

                    <!-- Features -->
                    <div class="grid grid-cols-2 gap-2 text-xs text-[var(--cc-text-muted)] border-t border-[var(--cc-border)] pt-4">
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-blue-500">groups</span>
                            <span>{{ $vehicle->capacity }} pax</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-blue-500">directions_car</span>
                            <span>{{ $vehicle->color ?? '—' }}</span>
                        </div>
                        <div class="col-span-2 flex items-center gap-1.5 mt-1">
                            <span class="material-symbols-outlined text-[16px] text-blue-500">location_on</span>
                            <span class="truncate">{{ $vehicle->pool?->name ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="px-5 py-3 border-t border-[var(--cc-border)] bg-[var(--cc-bg-muted)]/50 flex items-center justify-between text-xs">
                    <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}" class="text-[var(--cc-text-muted)] hover:text-blue-600 transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">book_online</span>
                        <span><strong>{{ $vehicle->bookings_count }}</strong> Bookings</span>
                    </a>
                    <a href="{{ route('fleet.show', $vehicle->id) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-bold flex items-center gap-0.5">
                        <span>Detail</span>
                        <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                    </a>
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-[var(--cc-text-muted)]">
                <div class="text-3xl mb-2">🚗</div>
                <div>No vehicles found</div>
            </div>
            @endforelse
        </div>
    </div>

    <div class="mt-6">{{ $vehicles->links() }}</div>

    @include('fleet.charts')
</div>

@push('scripts')
<script>
    function setFleetView(view) {
        localStorage.setItem('fleet-view-preference', view);
        applyFleetView(view);
    }

    function applyFleetView(view) {
        const listView = document.getElementById('fleet-list-view');
        const gridView = document.getElementById('fleet-grid-view');
        const btnList = document.getElementById('btn-toggle-list');
        const btnGrid = document.getElementById('btn-toggle-grid');

        if (view === 'grid') {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');
            
            // Toggle active classes on buttons
            btnGrid.classList.add('bg-blue-600', 'text-white');
            btnGrid.classList.remove('text-[var(--cc-text-muted)]');
            btnList.classList.remove('bg-blue-600', 'text-white');
            btnList.classList.add('text-[var(--cc-text-muted)]');
        } else {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');

            // Toggle active classes on buttons
            btnList.classList.add('bg-blue-600', 'text-white');
            btnList.classList.remove('text-[var(--cc-text-muted)]');
            btnGrid.classList.remove('bg-blue-600', 'text-white');
            btnGrid.classList.add('text-[var(--cc-text-muted)]');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const savedView = localStorage.getItem('fleet-view-preference') || 'list';
        applyFleetView(savedView);
    });
</script>
@endpush
@endsection
