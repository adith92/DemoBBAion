@extends('layouts.app')

@section('header_title', $vehicle->plate_number)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('fleet.index'), 'label' => 'Fleet'],
    ['url' => '#', 'label' => $vehicle->plate_number],
]" />

{{-- Hero --}}
<div class="rounded-2xl p-6 mb-5 text-gray-900 relative overflow-hidden"
     style="background: linear-gradient(135deg, var(--color-secondary) 0%, #1468a8 60%, #0052cc 100%);">
    <div class="absolute inset-0 opacity-10"
         style="background: radial-gradient(ellipse at 80% 50%, var(--color-primary) 0%, transparent 60%)"></div>
    <div class="relative flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <p class="text-blue-200 text-xs uppercase tracking-widest mb-1 font-semibold">{{ ucfirst($vehicle->brand) }}</p>
            <h2 class="text-3xl font-extrabold tracking-tight">{{ $vehicle->plate_number }}</h2>
            <p class="text-blue-100 mt-1.5 text-sm">{{ $vehicle->model }} · {{ $vehicle->capacity }} pax · {{ $vehicle->year }}</p>
            <p class="text-blue-200 text-xs mt-1">Pool: {{ $vehicle->pool?->name ?? 'Tidak ditentukan' }}</p>
        </div>
        <div class="flex flex-col items-end gap-2">
            <x-status-badge :status="$vehicle->status" />
            @if($activeBooking)
            <div class="bg-gray-100/10 backdrop-blur rounded-xl px-4 py-2 text-sm border border-white/20">
                <p class="text-blue-200 text-xs">Sedang digunakan oleh:</p>
                <a href="{{ route('clients.show', $activeBooking->client_id) }}" class="text-gray-900 font-semibold hover:underline">
                    {{ $activeBooking->client->company_name }}
                </a>
            </div>
            @endif
            @can('update', $vehicle)
            <a href="{{ route('fleet.edit', $vehicle->id) }}"
               class="flex items-center gap-1 text-xs bg-gray-100/10 hover:bg-gray-100/20 border border-white/20 rounded-lg px-3 py-1.5 transition-colors">
                <span class="material-symbols-outlined text-[14px]">edit</span> Edit
            </a>
            @endcan
        </div>
    </div>
</div>

{{-- Complete Vehicle Detail Grid --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5">

    {{-- Spesifikasi Kendaraan --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-primary">directions_bus</span>
            Spesifikasi
        </h3>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Merek / Model</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $vehicle->brand }} {{ $vehicle->model }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Tahun Produksi</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $vehicle->year_manufactured ?? $vehicle->year ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Kapasitas</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $vehicle->capacity }} penumpang</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Warna</dt>
                <dd class="flex items-center gap-2">
                    @if($vehicle->color)
                    <span class="w-4 h-4 rounded-full border border-slate-200 dark:border-white/20" style="background:{{ strtolower($vehicle->color) }}"></span>
                    <span class="text-slate-800 dark:text-slate-200 font-medium capitalize">{{ $vehicle->color }}</span>
                    @else
                    <span class="text-slate-600">—</span>
                    @endif
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Transmisi</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium capitalize">{{ $vehicle->transmission ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">BBM</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium capitalize">{{ $vehicle->bbm_type ?? '—' }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">KM Saat Ini</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $vehicle->current_km ? number_format($vehicle->current_km, 0, ',', '.') . ' km' : '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Dokumen & Legalitas --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-amber-400">description</span>
            Dokumen
        </h3>
        <dl class="space-y-3 text-sm">
            @php
                $today = \Carbon\Carbon::today();

                $stnkExpiry   = $vehicle->stnk_expiry   ? \Carbon\Carbon::parse($vehicle->stnk_expiry)   : null;
                $pajakExpiry  = $vehicle->pajak_expiry  ? \Carbon\Carbon::parse($vehicle->pajak_expiry)  : null;

                $stnkStatus  = $stnkExpiry  ? ($stnkExpiry->isPast()  ? 'expired' : ($stnkExpiry->diffInDays($today) < 30  ? 'soon' : 'ok')) : null;
                $pajakStatus = $pajakExpiry ? ($pajakExpiry->isPast() ? 'expired' : ($pajakExpiry->diffInDays($today) < 30 ? 'soon' : 'ok')) : null;

                $statusColor = fn($s) => match ($s) {
                    'expired' => 'text-red-400 font-bold',
                    'soon'    => 'text-yellow-400 font-semibold',
                    'ok'      => 'text-green-400',
                    default   => 'text-slate-600',
                };
                $statusBadge = fn($s) => match ($s) {
                    'expired' => '<span class="ml-2 px-1.5 py-0.5 rounded text-[10px] bg-red-900/40 text-red-400 font-bold">EXPIRED</span>',
                    'soon'    => '<span class="ml-2 px-1.5 py-0.5 rounded text-[10px] bg-yellow-900/40 text-yellow-400 font-bold">SEGERA</span>',
                    default   => '',
                };
            @endphp
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">STNK Berlaku s/d</dt>
                <dd class="flex items-center {{ $statusColor($stnkStatus) }}">
                    {{ $stnkExpiry ? $stnkExpiry->format('d M Y') : '—' }}
                    @if($stnkStatus) {!! $statusBadge($stnkStatus) !!} @endif
                </dd>
            </div>
            <div class="flex justify-between items-center">
                <dt class="text-slate-500">Pajak s/d</dt>
                <dd class="flex items-center {{ $statusColor($pajakStatus) }}">
                    {{ $pajakExpiry ? $pajakExpiry->format('d M Y') : '—' }}
                    @if($pajakStatus) {!! $statusBadge($pajakStatus) !!} @endif
                </dd>
            </div>
            <div class="border-t border-white/5 pt-3">
                <dt class="text-slate-500 mb-1">Status Armada</dt>
                <dd class="mt-1"><x-status-badge :status="$vehicle->status" /></dd>
            </div>
            <div>
                <dt class="text-slate-500 mb-1">Pool</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $vehicle->pool?->name ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Trip Type / Kontrak --}}
    <div class="cc-card rounded-xl p-5">
        <h3 class="text-[13px] font-bold text-slate-800 dark:text-slate-300 mb-4 flex items-center gap-2 uppercase tracking-wide">
            <span class="material-symbols-outlined text-[16px] text-purple-400">assignment</span>
            Penggunaan
        </h3>
        @php
            $tripShort = $bookings->filter(fn($b) => str_contains(strtolower($b->vehicle_type ?? ''), 'short'))->count();
            $tripLong  = $bookings->filter(fn($b) => str_contains(strtolower($b->vehicle_type ?? ''), 'long'))->count();
            $totalBookings = $bookings->count();
            $totalRevenue  = $bookings->where('status','completed')->sum('price');
        @endphp
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
                <dt class="text-slate-500">Total Booking</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-bold text-lg">{{ $totalBookings }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Short Trip</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $tripShort }}</dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-slate-500">Long Trip</dt>
                <dd class="text-slate-800 dark:text-slate-200 font-medium">{{ $tripLong }}</dd>
            </div>
            <div class="flex justify-between border-t border-slate-200 dark:border-white/5 pt-3">
                <dt class="text-slate-500">Total Revenue</dt>
                <dd class="text-green-600 dark:text-green-400 font-bold">{{ \App\Helpers\FormatHelper::formatIDR($totalRevenue) }}</dd>
            </div>
            @if($vehicle->notes)
            <div class="border-t border-slate-200 dark:border-white/5 pt-3">
                <dt class="text-slate-500 mb-1">Catatan</dt>
                <dd class="text-slate-600 dark:text-slate-400 text-xs leading-relaxed">{{ $vehicle->notes }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>

{{-- Active Booking Alert --}}
@if($activeBooking)
<div class="rounded-xl p-4 mb-4 border border-purple-200 bg-purple-50 dark:border-purple-500/30 dark:bg-purple-900/20">
    <h3 class="font-semibold text-purple-700 dark:text-purple-300 mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">directions_bus</span>
        Sedang Dalam Perjalanan
    </h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-purple-600 dark:text-purple-400 text-xs mb-1">Booking</p>
            <a href="{{ route('bookings.show', $activeBooking->id) }}" class="font-semibold text-purple-800 dark:text-purple-200 hover:underline font-mono">
                {{ $activeBooking->booking_number }}
            </a>
        </div>
        <div>
            <p class="text-purple-600 dark:text-purple-400 text-xs mb-1">Klien</p>
            <a href="{{ route('clients.show', $activeBooking->client_id) }}" class="font-semibold text-blue-600 dark:text-blue-300 hover:underline">
                {{ $activeBooking->client->company_name }}
            </a>
        </div>
        <div>
            <p class="text-purple-600 dark:text-purple-400 text-xs mb-1">Pengemudi</p>
            <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $activeBooking->driver->name }}</p>
        </div>
        <div>
            <p class="text-purple-600 dark:text-purple-400 text-xs mb-1">Sales</p>
            @if(auth()->user()->isGM())
                <a href="{{ route('sales.performance', $activeBooking->sales_id) }}" class="font-semibold text-blue-600 dark:text-blue-300 hover:underline">
                    {{ $activeBooking->sales->name }}
                </a>
            @else
                <p class="font-semibold text-slate-800 dark:text-slate-200">{{ $activeBooking->sales->name }}</p>
            @endif
        </div>
    </div>
</div>
@endif

{{-- Next Maintenance Alert --}}
@if($nextMaintenance)
<div class="rounded-xl p-4 mb-4 border border-yellow-200 bg-yellow-50 dark:border-yellow-500/30 dark:bg-yellow-900/15">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-semibold text-yellow-700 dark:text-yellow-300 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">build</span>
                Maintenance Terjadwal
            </h3>
            <p class="text-sm text-yellow-800/80 dark:text-yellow-200/70 mt-1 capitalize">{{ $nextMaintenance->type }} — {{ $nextMaintenance->description }}</p>
        </div>
        <div class="text-right">
            <p class="text-yellow-700 dark:text-yellow-300 font-bold">{{ \Carbon\Carbon::parse($nextMaintenance->scheduled_date)->format('d M Y') }}</p>
            <p class="text-xs text-yellow-600 dark:text-yellow-500">{{ $nextMaintenance->vendor ?? 'Vendor belum ditentukan' }}</p>
        </div>
    </div>
</div>
@endif

{{-- Booking History --}}
<div class="cc-card rounded-xl overflow-hidden mb-4">
    <div class="flex justify-between items-center px-5 py-4 border-b border-slate-200 dark:border-white/5">
        <h3 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Riwayat Booking (10 Terakhir)</h3>
        <a href="{{ route('bookings.index', ['vehicle_id' => $vehicle->id]) }}"
           class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                    <th class="text-left py-3 px-4">Booking #</th>
                    <th class="text-left py-3 px-4">Klien</th>
                    <th class="text-left py-3 px-4">Sales</th>
                    <th class="text-left py-3 px-4">Pickup</th>
                    <th class="text-left py-3 px-4">Tujuan</th>
                    <th class="text-left py-3 px-4">Tipe</th>
                    <th class="text-center py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                <tr class="border-b border-slate-200 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/3 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-600 dark:text-primary hover:underline font-mono text-[12px]">
                            {{ $booking->booking_number }}
                        </a>
                    </td>
                    <td class="py-3 px-4">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-[13px]">
                            {{ $booking->client->company_name }}
                        </a>
                    </td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-[13px]">
                        @if(auth()->user()->isGM())
                            <a href="{{ route('sales.performance', $booking->sales_id) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $booking->sales->name }}
                            </a>
                        @else
                            {{ $booking->sales->name }}
                        @endif
                    </td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-[12px]">{{ $booking->pickup_datetime->format('d M Y') }}</td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 text-[12px] max-w-[140px] truncate">{{ $booking->destination }}</td>
                    <td class="py-3 px-4 text-slate-500 text-[11px] capitalize">{{ $booking->vehicle_type }}</td>
                    <td class="py-3 px-4 text-center"><x-status-badge :status="$booking->status" /></td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-850 dark:text-slate-200">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-slate-500">
                        <span class="material-symbols-outlined text-3xl block mb-2 opacity-30">event_busy</span>
                        Belum ada riwayat booking
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Maintenance History --}}
<div class="cc-card rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-200 dark:border-white/5">
        <h3 class="text-[14px] font-bold text-slate-800 dark:text-slate-200">Riwayat Maintenance</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-200 dark:border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                    <th class="text-left py-3 px-4">Tipe</th>
                    <th class="text-left py-3 px-4">Deskripsi</th>
                    <th class="text-left py-3 px-4">Vendor</th>
                    <th class="text-left py-3 px-4">Jadwal</th>
                    <th class="text-left py-3 px-4">Selesai</th>
                    <th class="text-center py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($maintenanceLogs as $log)
                <tr class="border-b border-slate-200 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/3 transition-colors">
                    <td class="py-3 px-4 capitalize text-slate-800 dark:text-slate-300">{{ $log->type }}</td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 max-w-[200px] truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $log->vendor ?? '—' }}</td>
                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400">{{ \Carbon\Carbon::parse($log->scheduled_date)->format('d M Y') }}</td>
                    <td class="py-3 px-4 {{ $log->completed_date ? 'text-green-600 dark:text-green-400' : 'text-slate-600' }}">
                        {{ $log->completed_date ? \Carbon\Carbon::parse($log->completed_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="py-3 px-4 text-center"><x-status-badge :status="$log->status" /></td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-800 dark:text-slate-200">{{ $log->cost ? \App\Helpers\FormatHelper::formatIDR($log->cost) : '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-8 text-center text-slate-500">
                        <span class="material-symbols-outlined text-3xl block mb-2 opacity-30">build_circle</span>
                        Belum ada riwayat maintenance
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
