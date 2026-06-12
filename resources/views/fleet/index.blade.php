@extends('layouts.app')

@section('header_title', 'Operational Pool & Long-Term Fleet')

@push('styles')
<style>
    /* Add slight transition for smooth hover effects */
    .fleet-card {
        transition: all 0.2s ease-in-out;
    }
    .fleet-card:hover {
        border-color: rgba(99, 102, 241, 0.2); /* indigo-500/20 */
        background-color: rgba(255, 255, 255, 0.1);
    }
</style>
@endpush

@section('content')
@php
    $canModify = auth()->user()->isOperational() || auth()->user()->isManager() || auth()->user()->isGM();
    $statusColors = [
        'available'   => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'maintenance' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
        'rent_out'    => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'assigned'    => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'booked'      => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'hold'        => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
        'inactive'    => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
    ];
@endphp

<div class="space-y-6 pb-20" x-data="{ showMaintenanceDetails: false }">
    
    {{-- Header Panel --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-2 flex items-center gap-2">
                <span class="material-symbols-outlined h-8 w-8 text-indigo-400" style="font-size: 32px">directions_car</span>
                Operational Pool & Long-Term Fleet
            </h1>
            <p class="text-[var(--cc-text-muted)] max-w-2xl text-sm">
                Operational dashboard to register, allocate, and monitor vehicle fleets assigned exclusively to <strong class="text-indigo-400">Mobil Long Term</strong> contracts.
            </p>
        </div>
        
        @if($canModify)
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/30 hover:bg-indigo-500 transition-all">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Register Vehicle
            </button>
        </div>
        @endif
    </div>

    {{-- Fleet Stats Grid --}}
    @php
        $currentStatus = request('status', 'All');
    @endphp
    <div class="grid gap-4" :class="showMaintenanceDetails ? 'grid-cols-2 lg:grid-cols-8' : 'grid-cols-2 lg:grid-cols-6'">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'All']) }}" 
           class="block rounded-2xl border bg-[var(--cc-surface)] p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'All' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-[var(--cc-border)] hover:border-indigo-500/40' }}">
            <div class="text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-wider">Total Fleet</div>
            <div class="text-3xl font-mono font-bold text-[var(--cc-text)] mt-1">{{ $stats['total'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Mobil Long Term units</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'available']) }}" 
           class="block rounded-2xl border bg-emerald-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'available' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-emerald-500/20 hover:border-emerald-500/50' }}">
            <div class="text-xs font-bold text-emerald-400 uppercase tracking-wider">Available</div>
            <div class="text-3xl font-mono font-bold text-emerald-400 mt-1">{{ $stats['available'] }}</div>
            <div class="text-[10px] text-emerald-500 mt-1">Ready for assignment</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'rent_out']) }}" 
           class="block rounded-2xl border bg-blue-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'rent_out' ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-blue-500/20 hover:border-blue-500/50' }}">
            <div class="text-xs font-bold text-blue-400 uppercase tracking-wider">Rented Out</div>
            <div class="text-3xl font-mono font-bold text-blue-400 mt-1">{{ $stats['rented'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">On active contract</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'booked']) }}" 
           class="block rounded-2xl border bg-purple-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'booked' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-purple-500/20 hover:border-purple-500/50' }}">
            <div class="text-xs font-bold text-purple-400 uppercase tracking-wider">Booked</div>
            <div class="text-3xl font-mono font-bold text-purple-400 mt-1">{{ $stats['booked'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Earmarked/Reserved</div>
        </a>
        
        <a href="{{ request()->fullUrlWithQuery(['status' => 'hold']) }}" 
           class="block rounded-2xl border bg-pink-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'hold' ? 'border-pink-500 ring-2 ring-pink-500/20' : 'border-pink-500/20 hover:border-pink-500/50' }}">
            <div class="text-xs font-bold text-pink-400 uppercase tracking-wider">Hold</div>
            <div class="text-3xl font-mono font-bold text-pink-400 mt-1">{{ $stats['hold'] }}</div>
            <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Pending negotiation</div>
        </a>
 
        <template x-if="!showMaintenanceDetails">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'maintenance']) }}" 
               @click="showMaintenanceDetails = true" 
               class="block rounded-2xl border bg-amber-500/10 p-4 backdrop-blur-md cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'maintenance' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-amber-500/20 hover:border-amber-500/50' }}">
                <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Maintenance</div>
                <div class="text-3xl font-mono font-bold text-amber-400 mt-1">{{ $stats['maintenance'] }}</div>
                <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Click to view details <span class="text-[10px]">▼</span></div>
            </a>
        </template>
        <template x-if="showMaintenanceDetails">
            <div class="contents">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'maintenance']) }}" 
                   @click="showMaintenanceDetails = false" 
                   class="block rounded-2xl border bg-amber-500/10 p-4 backdrop-blur-md cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-lg {{ $currentStatus === 'maintenance' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-amber-500/20 hover:border-amber-500/50' }}">
                    <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Maintenance</div>
                    <div class="text-3xl font-mono font-bold text-amber-400 mt-1">{{ $stats['maintenance'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Total in workshop <span class="text-[10px]">▲</span></div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'Being Serviced']) }}" 
                   class="block rounded-2xl border bg-rose-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg animate-in fade-in slide-in-from-left-4 duration-300 {{ $currentStatus === 'Being Serviced' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-rose-500/20 hover:border-rose-500/50' }}">
                    <div class="text-xs font-bold text-rose-400 uppercase tracking-wider">Servicing</div>
                    <div class="text-3xl font-mono font-bold text-rose-400 mt-1">{{ $stats['beingServiced'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">In repair</div>
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'In Queue']) }}" 
                   class="block rounded-2xl border bg-orange-500/10 p-4 backdrop-blur-md transition-all duration-200 hover:scale-[1.02] hover:shadow-lg animate-in fade-in slide-in-from-left-4 duration-300 {{ $currentStatus === 'In Queue' ? 'border-orange-500 ring-2 ring-orange-500/20' : 'border-orange-500/20 hover:border-orange-500/50' }}">
                    <div class="text-xs font-bold text-orange-400 uppercase tracking-wider">In Queue</div>
                    <div class="text-3xl font-mono font-bold text-orange-400 mt-1">{{ $stats['inQueue'] }}</div>
                    <div class="text-[10px] text-[var(--cc-text-muted)] mt-1">Workshop queue</div>
                </a>
            </div>
        </template>
    </div>

    {{-- Control Filters Panel --}}
    <form id="filter-form" method="GET" action="{{ route('fleet.index') }}" class="flex flex-col md:flex-row gap-4 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-2xl p-4 backdrop-blur-md">
        <div class="flex-1 relative">
            <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-[var(--cc-text-muted)]" style="font-size: 16px;">search</span>
            <input
                type="text"
                name="search"
                placeholder="Search by plate number, car model, details..."
                value="{{ request('search') }}"
                class="w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] rounded-xl py-2 pl-10 pr-4 text-sm text-[var(--cc-text)] placeholder-[var(--cc-text-muted)] focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all"
            />
            {{-- Invisible submit button to allow Enter to search --}}
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
                <option value="rent_out" {{ request('status') === 'rent_out' ? 'selected' : '' }}>Rent Out Only</option>
                <option value="booked" {{ request('status') === 'booked' ? 'selected' : '' }}>Booked Only</option>
                <option value="hold" {{ request('status') === 'hold' ? 'selected' : '' }}>Hold Only</option>
                <optgroup label="Maintenance">
                    <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance (All)</option>
                    <option value="Being Serviced" {{ request('status') === 'Being Serviced' ? 'selected' : '' }}>↳ Being Serviced</option>
                    <option value="In Queue" {{ request('status') === 'In Queue' ? 'selected' : '' }}>↳ In Queue</option>
                </optgroup>
            </select>
        </div>
    </form>

    {{-- Vehicles Grid --}}
    @if($vehicles->isEmpty())
        <div class="text-center py-16 bg-[var(--cc-surface)] border border-[var(--cc-border)] rounded-3xl backdrop-blur-md">
            <span class="material-symbols-outlined mx-auto text-slate-500 mb-3" style="font-size: 48px;">directions_car</span>
            <h3 class="text-lg font-bold text-[var(--cc-text)] mb-1">No Vehicles Found</h3>
            <p class="text-sm text-[var(--cc-text-muted)]">Try adjusting your filters or search criteria.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vehicles as $u)
            <div class="group relative rounded-3xl border border-[var(--cc-border)] bg-[var(--cc-surface)] p-6 backdrop-blur-lg fleet-card flex flex-col justify-between">
                <div>
                    {{-- Top row: plate status & location --}}
                    <div class="flex items-start justify-between mb-4">
                        {{-- Indonesian Plate Number Representation --}}
                        <div class="flex flex-col items-center border border-slate-700 bg-slate-900 text-white font-mono px-3 py-1 rounded shadow-md select-none shrink-0 border-t-2 border-t-indigo-500">
                            <span class="text-base font-bold tracking-widest">{{ $u->plate_number }}</span>
                            <div class="w-full h-px bg-slate-800 my-0.5"></div>
                            <span class="text-[8px] tracking-widest text-slate-400">06.31</span>
                        </div>

                        <div class="flex flex-col items-end gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-black border uppercase tracking-wider {{ $statusColors[$u->status] ?? $statusColors['available'] }}">
                                {{ str_replace('_', ' ', $u->status) }}
                            </span>
                            
                            @if($u->status === 'maintenance')
                                @php
                                    $mStatus = str_contains($u->notes ?? '', 'Servicing') ? 'Being Serviced' : 'In Queue';
                                    $mClass = $mStatus === 'Being Serviced' ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-orange-500/10 text-orange-400 border-orange-500/20';
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold border {{ $mClass }}">
                                    {{ $mStatus }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1 text-xs text-[var(--cc-text-muted)] font-medium">
                                <span class="material-symbols-outlined text-[12px] text-red-400">location_on</span>
                                {{ $u->pool?->name ?? 'Unknown' }}
                            </span>
                        </div>
                    </div>

                    {{-- Car info --}}
                    <div class="mb-4">
                        <h3 class="font-bold text-[var(--cc-text)] text-lg tracking-tight group-hover:text-indigo-400 transition-colors">
                            <a href="{{ route('fleet.show', $u->id) }}" class="hover:underline">
                                {{ $u->brand }} {{ $u->model }}
                            </a>
                        </h3>
                        <div class="inline-flex items-center gap-1.5 mt-1 bg-[var(--cc-bg-muted)] px-2 py-0.5 rounded text-[10px] text-[var(--cc-text-muted)] uppercase font-black tracking-wider">
                            <span class="material-symbols-outlined text-[12px] text-indigo-400">sell</span>
                            Mobil Long Term
                        </div>
                    </div>

                    {{-- Relational Linked Contract --}}
                    @if(!in_array($u->status, ['available', 'maintenance', 'inactive']))
                        @if($u->assignedOpportunity)
                            <div class="mt-4 p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-xs">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-indigo-400 mb-1">Assigned Client & Contract</span>
                                <div class="font-bold text-[var(--cc-text)] mb-0.5">{{ $u->assignedOpportunity->client->company_name ?? 'Unknown Company' }}</div>
                                <div class="text-[var(--cc-text-muted)] flex items-center gap-1.5 mt-1">
                                    <span class="truncate">{{ $u->assignedOpportunity->title }}</span>
                                    <span class="text-[10px] bg-indigo-500/20 px-1.5 py-0.5 rounded text-white shrink-0">{{ ucfirst($u->assignedOpportunity->stage) }}</span>
                                </div>
                            </div>
                        @else
                            <div class="mt-4 p-3.5 rounded-2xl bg-[var(--cc-surface)] border border-dashed border-[var(--cc-border)] text-xs text-[var(--cc-text-muted)] italic">
                                No active customer contract assigned.
                            </div>
                        @endif
                    @endif

                    {{-- Details Grid --}}
                    @if($u->year_manufactured || $u->color || $u->transmission || $u->current_km !== null)
                        <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-[var(--cc-text-muted)] bg-[var(--cc-bg-muted)] p-3 rounded-2xl border border-[var(--cc-border)]">
                            @if($u->year_manufactured) <div><span class="font-bold text-slate-500">Year:</span> {{ $u->year_manufactured }}</div> @endif
                            @if($u->color) <div><span class="font-bold text-slate-500">Color:</span> {{ $u->color }}</div> @endif
                            @if($u->transmission) <div><span class="font-bold text-slate-500">Transmission:</span> {{ $u->transmission }}</div> @endif
                            @if($u->current_km !== null) <div><span class="font-bold text-slate-500">Odo:</span> {{ number_format($u->current_km) }} km</div> @endif
                        </div>
                    @endif

                    {{-- Operational Logs --}}
                    @if($u->notes)
                        <div class="mt-2 bg-[var(--cc-bg-muted)] p-3 rounded-2xl text-xs text-[var(--cc-text-muted)] border border-[var(--cc-border)]">
                            <span class="font-bold text-[var(--cc-text)] block mb-1">Operational Logs:</span>
                            {{ $u->notes }}
                        </div>
                    @endif

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if($u->pajak_expiry)
                            <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px] text-indigo-400/70">description</span>
                                Tax Exp: {{ $u->pajak_expiry->format('Y-m-d') }}
                            </div>
                        @endif
                        @if($u->stnk_expiry)
                            <div class="text-[10px] text-slate-500 font-medium flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px] text-indigo-400/70">description</span>
                                STNK Exp: {{ $u->stnk_expiry->format('Y-m-d') }}
                            </div>
                        @endif
                    </div>

                    @php
                        // Fetch latest maintenance log if any (Requires relationship to be loaded or just placeholder if not)
                        $lastService = null;
                        if ($u->relationLoaded('maintenanceLogs') && $u->maintenanceLogs->isNotEmpty()) {
                            $lastService = $u->maintenanceLogs->first()->scheduled_date;
                        }
                    @endphp
                    @if($lastService)
                        <div class="mt-2 text-[11px] text-slate-500 font-medium flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[14px] text-amber-500/70">build</span>
                            Last serviced: {{ \Carbon\Carbon::parse($lastService)->format('Y-m-d') }}
                        </div>
                    @endif
                </div>

                {{-- Bottom Actions --}}
                <div class="mt-6 pt-4 border-t border-[var(--cc-border)] flex gap-2">
                    @if($canModify)
                    <button class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-indigo-500/20 bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 py-2 text-xs font-semibold transition-all">
                        <span class="material-symbols-outlined text-[14px]">build</span>
                        Status
                    </button>
                    @endif
                    <a href="{{ route('fleet.show', $u->id) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] hover:bg-[var(--cc-bg-muted)] py-2 text-xs font-semibold text-[var(--cc-text)] text-center transition-all">
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
