@extends('layouts.app')

@section('header_title', $driver->name)

@section('content')
@php
    $canModify = auth()->user()->isOperational() || auth()->user()->isManager() || auth()->user()->isGM();
    $statusColors = [
        'available' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
        'assigned'  => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
        'reserved'  => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
        'inactive'  => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
    ];
@endphp

<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('drivers.index'), 'label' => 'Supir'],
    ['url' => '#', 'label' => $driver->name],
]" />

{{-- Hero --}}
<div class="rounded-2xl p-6 mb-5 text-gray-900 relative overflow-hidden"
     style="background: linear-gradient(135deg, var(--color-secondary) 0%, #1468a8 60%, #0052cc 100%);">
    <div class="absolute inset-0 opacity-10"
         style="background: radial-gradient(ellipse at 80% 50%, var(--color-primary) 0%, transparent 60%)"></div>
    <div class="relative flex flex-col md:flex-row justify-between items-start gap-4">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-full bg-gray-100/10 text-gray-900 flex items-center justify-center font-bold text-2xl border border-white/20 shadow-inner">
                {{ strtoupper(substr($driver->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-blue-200 text-xs uppercase tracking-widest mb-1 font-semibold">Supir Fleet</p>
                <h2 class="text-3xl font-extrabold tracking-tight">{{ $driver->name }}</h2>
                <p class="text-blue-100 mt-1 text-sm">{{ $driver->phone ?? 'No phone number' }}</p>
                <p class="text-blue-200 text-xs mt-1">Pool: {{ $driver->pool?->name ?? 'Tidak ditentukan' }}</p>
            </div>
        </div>
        <div class="flex flex-col items-end gap-2 shrink-0">
            <x-status-badge :status="$driver->status" />
            @if($canModify)
            <a href="#"
               class="flex items-center gap-1 text-xs bg-gray-100/10 hover:bg-gray-100/20 border border-white/20 rounded-lg px-3 py-1.5 transition-colors">
                <span class="material-symbols-outlined text-[14px]">edit</span> Edit Profil
            </a>
            @endif
        </div>
    </div>
</div>

{{-- Detail Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    {{-- Profil & Identitas --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-primary">account_circle</span>
            Profil Supir
        </h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Nama Lengkap</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $driver->name }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Nomor Telepon</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $driver->phone ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Nomor SIM</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium font-mono">{{ $driver->license_number ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Lokasi Pool</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $driver->pool?->name ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Dibuat Pada</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $driver->created_at ? $driver->created_at->format('d M Y') : '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Status Penugasan --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-indigo-400">assignment_ind</span>
            Penugasan
        </h3>
        <dl class="space-y-3 text-sm">
            @if($driver->assignedOpportunity)
            <div>
                <dt class="text-slate-500 mb-1">Klien Aktif</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-bold">
                    <a href="{{ route('clients.show', $driver->assignedOpportunity->client_id) }}" class="text-blue-600 dark:text-primary hover:underline">
                        {{ $driver->assignedOpportunity->client->company_name ?? 'Unknown Client' }}
                    </a>
                </dd>
            </div>
            <div class="border-t border-white/5 pt-3">
                <dt class="text-slate-500 mb-1">Nama Kontrak / Opportunity</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $driver->assignedOpportunity->title }}</dd>
            </div>
            <div class="flex justify-between border-t border-white/5 pt-3">
                <dt class="text-slate-500">Stage Kontrak</dt>
                <dd class="text-indigo-600 dark:text-indigo-400 font-semibold uppercase text-xs">{{ $driver->assignedOpportunity->stage }}</dd>
            </div>
            @else
            <div class="text-center py-6 text-slate-500 italic">
                <span class="material-symbols-outlined text-3xl block mb-2 opacity-35">info</span>
                Tidak ada penugasan kontrak aktif saat ini.
            </div>
            @endif
        </dl>
    </div>

    {{-- Catatan Operasional --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-amber-500">notes</span>
            Catatan Operasional
        </h3>
        <div class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed min-h-[100px]">
            {{ $driver->notes ?? 'Tidak ada catatan operasional khusus untuk supir ini.' }}
        </div>
    </div>
</div>

{{-- Riwayat Booking --}}
<div class="cc-card rounded-xl overflow-hidden mb-4">
    <div class="flex justify-between items-center px-5 py-4 border-b border-slate-200 dark:border-white/5">
        <h3 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Riwayat Booking Supir</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                    <th class="text-left py-3 px-4">Booking #</th>
                    <th class="text-left py-3 px-4">Klien</th>
                    <th class="text-left py-3 px-4">Pickup</th>
                    <th class="text-left py-3 px-4">Tujuan</th>
                    <th class="text-center py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Unit Armada</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $driverBookings = $driver->bookings()->with(['client', 'vehicle'])->latest('pickup_datetime')->limit(10)->get();
                @endphp
                @forelse($driverBookings as $booking)
                <tr class="border-b border-slate-200 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/3 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 dark:text-primary hover:underline font-mono text-[12px]">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        @if($booking->client)
                            <a href="{{ route('clients.show', $booking->client_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-[13px]">
                                {{ $booking->client->company_name }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-[12px]">{{ $booking->pickup_datetime ? $booking->pickup_datetime->format('d M Y') : '—' }}</td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-[12px] max-w-[160px] truncate">{{ $booking->destination }}</td>
                    <td class="py-3 px-4 text-center"><x-status-badge :status="$booking->status" /></td>
                    <td class="py-3 px-4 text-right">
                        @if($booking->vehicle)
                            <a href="{{ route('fleet.show', $booking->vehicle->id) }}" class="text-blue-600 dark:text-primary hover:underline">
                                {{ $booking->vehicle->plate_number }}
                            </a>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-slate-500">
                        <span class="material-symbols-outlined text-3xl block mb-2 opacity-30">event_busy</span>
                        Belum ada riwayat booking untuk supir ini
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
