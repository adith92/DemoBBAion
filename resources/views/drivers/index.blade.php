@extends('layouts.app')

@section('header_title', 'Operational Supir')

@push('styles')
<style>
    .driver-card {
        transition: all 0.2s ease-in-out;
    }
    .driver-card:hover {
        border-color: rgba(99, 102, 241, 0.2);
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
@php
    $canModify = auth()->user()->isOperational() || auth()->user()->isManager() || auth()->user()->isGM();
    $statusColors = [
        'available' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'assigned'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'reserved'  => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'inactive'  => 'bg-rose-500/10 text-rose-400 border-rose-500/20', // leave
    ];
@endphp

<div class="space-y-6 pb-20">
    
    {{-- Header Panel --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined h-8 w-8 text-indigo-400" style="font-size: 32px">person</span>
                Operational Supir
            </h1>
            <p class="text-[var(--cc-text-muted)] max-w-2xl text-sm">
                Operational dashboard to register, allocate, and monitor driver (Supir) fleets.
            </p>
        </div>
        
        @if($canModify)
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition-all">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Register Driver
            </button>
        </div>
        @endif
    </div>

    {{-- Stats Grid --}}
    @php
        $currentStatus = request('status', 'All');
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'All']) }}" 
           class="block rounded-2xl border bg-[var(--cc-surface)] p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'All' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-[var(--cc-border)] hover:border-indigo-500/40' }}">
            <div class="text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-wider">Total Supir</div>
            <div class="text-3xl font-mono font-bold text-[var(--cc-text)] mt-1">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Registered drivers</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" 
           class="block rounded-2xl border bg-emerald-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'available' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-emerald-500/20 hover:border-emerald-500/50' }}">
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Available</div>
            <div class="text-3xl font-mono font-bold text-emerald-400 mt-1">{{ $stats['available'] }}</div>
            <div class="text-[10px] text-emerald-500 mt-1">Ready for assignment</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'assigned']) }}" 
           class="block rounded-2xl border bg-blue-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'assigned' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-blue-500/20 hover:border-blue-500/50' }}">
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider">Assigned</div>
            <div class="text-3xl font-mono font-bold text-blue-400 mt-1">{{ $stats['assigned'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">On active duty</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'reserved']) }}" 
           class="block rounded-2xl border bg-purple-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'reserved' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-purple-500/20 hover:border-purple-500/50' }}">
            <div class="text-xs font-bold text-purple-400 uppercase tracking-wider">Reserved</div>
            <div class="text-3xl font-mono font-bold text-purple-400 mt-1">{{ $stats['reserved'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Booked for contract</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'inactive']) }}" 
           class="block rounded-2xl border bg-rose-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'inactive' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-rose-500/20 hover:border-rose-500/50' }}">
            <div class="text-xs font-bold text-rose-400 uppercase tracking-wider">Leave</div>
            <div class="text-3xl font-mono font-bold text-rose-400 mt-1">{{ $stats['leave'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Not available</div>
        </a>
    </div>

    {{-- Control Filters Panel --}}
    <form id="driver-filter-form" method="GET" action="{{ route('drivers.index') }}" class="flex flex-col md:flex-row gap-4 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-2xl p-4 backdrop-blur-md">
        <div class="flex-1 relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--cc-text-muted)]" style="font-size: 16px;">search</span>
            <input
                type="text"
                name="search"
                placeholder="Search by name, phone..."
                value="{{ request('search') }}"
                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 pl-10 pr-4 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
            />
            <button type="submit" class="hidden"></button>
        </div>
        
        <div class="flex flex-wrap gap-3">
            <select
                name="location"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('location') === 'All' ? 'selected' : '' }}>All Locations</option>
                <option value="Jakarta" {{ request('location') === 'Jakarta' ? 'selected' : '' }}>Jakarta Pool</option>
                <option value="Surabaya" {{ request('location') === 'Surabaya' ? 'selected' : '' }}>Surabaya Pool</option>
            </select>

            <select
                name="status"
                onchange="this.form.submit()"
                class="bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl px-3 py-2 text-sm text-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500"
            >
                <option value="All" {{ request('status') === 'All' ? 'selected' : '' }}>All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available Only</option>
                <option value="assigned" {{ request('status') === 'assigned' ? 'selected' : '' }}>Assigned Only</option>
                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Reserved Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Leave Only</option>
            </select>
        </div>
    </form>

    {{-- Drivers Grid --}}
    @if($drivers->isEmpty())
        <div class="text-center py-16 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl backdrop-blur-md">
            <span class="material-symbols-outlined mx-auto text-slate-500 mb-3" style="font-size: 48px;">person_off</span>
            <h3 class="text-lg font-bold text-[var(--cc-text)] mb-1">No Drivers Found</h3>
            <p class="text-sm text-[var(--cc-text-muted)]">Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($drivers as $d)
            <div class="group relative rounded-3xl border border-[var(--cc-border)] bg-[var(--cc-surface)] p-6 backdrop-blur-lg driver-card flex flex-col justify-between h-full">
                <div>
                    {{-- Top Row: Avatar & Status Badge --}}
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-full bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-lg shrink-0 border border-indigo-500/30">
                            {{ strtoupper(substr($d->name, 0, 1)) }}
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $statusColors[$d->status] ?? $statusColors['available'] }}">
                            {{ str_replace('_', ' ', $d->status === 'inactive' ? 'leave' : $d->status) }}
                        </span>
                    </div>
                    
                    {{-- Middle: Driver Details --}}
                    <div class="space-y-1">
                        <h3 class="font-bold text-[var(--cc-text)] text-lg tracking-tight group-hover:text-indigo-400 transition-colors">
                            <a href="{{ route('drivers.show', $d->id) }}" class="hover:underline">
                                {{ $d->name }}
                            </a>
                        </h3>
                        <div class="text-xs text-[var(--cc-text-muted)] flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px]">phone</span>
                            <span>{{ $d->phone ?? 'No phone' }}</span>
                        </div>
                    </div>

                    {{-- Bottom: Pool Info Banner --}}
                    <div class="mt-4 flex items-center gap-2 text-xs text-[var(--cc-text-muted)] bg-[var(--cc-bg-muted)] border border-[var(--cc-border)]/50 rounded-xl px-3 py-2">
                        <span class="material-symbols-outlined text-[16px] text-indigo-400">home</span>
                        <span class="font-medium">Pool: <span class="text-[var(--cc-text)] font-semibold">{{ $d->pool?->name ?? '—' }}</span></span>
                    </div>

                    {{-- Relational Linked Contract --}}
                    @if(!in_array($d->status, ['available', 'inactive']))
                        @if($d->assignedOpportunity)
                            <div class="mt-3.5 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-indigo-400 mb-1">Assigned Client</span>
                                <div class="font-bold text-[var(--cc-text)] mb-0.5">{{ $d->assignedOpportunity->client->company_name ?? 'Unknown Company' }}</div>
                                <div class="text-[var(--cc-text-muted)] flex items-center gap-1.5 mt-1">
                                    <span class="truncate">{{ $d->assignedOpportunity->title }}</span>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
                
                {{-- Action Buttons --}}
                <div class="mt-5 flex gap-2">
                    @if($canModify)
                    <button class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-bg-muted)] hover:bg-[var(--cc-surface)] py-2 text-xs font-semibold text-[var(--cc-text)] transition-all">
                        <span class="material-symbols-outlined text-[14px]">edit</span>
                        Edit
                    </button>
                    @endif
                    <a href="{{ route('drivers.show', $d->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:bg-[var(--cc-bg-muted)] py-2 text-xs font-semibold text-[var(--cc-text)] text-center transition-all">
                        <span class="material-symbols-outlined text-[14px] text-[var(--cc-text-muted)]">visibility</span>
                        Detail
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
