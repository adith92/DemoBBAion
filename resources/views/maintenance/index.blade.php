@extends('layouts.app')

@section('header_title', 'Maintenance')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => 'Maintenance'],
]" />

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('maintenance.index', ['tab' => 'antrian']) }}"
       class="group block rounded-xl p-4 border-l-4 border-yellow-500 hover:shadow-lg transition-all text-center cc-card">
        <p class="text-2xl font-bold text-yellow-400">{{ $stats['scheduled'] + $stats['in_progress'] }}</p>
        <p class="text-sm text-slate-400 mt-1">Antrian</p>
    </a>
    <a href="{{ route('maintenance.index', ['tab' => 'antrian']) }}"
       class="group block rounded-xl p-4 border-l-4 border-blue-500 hover:shadow-lg transition-all text-center cc-card">
        <p class="text-2xl font-bold text-blue-400">{{ $stats['in_progress'] }}</p>
        <p class="text-sm text-slate-400 mt-1">Dikerjakan</p>
    </a>
    <a href="{{ route('maintenance.index', ['tab' => 'selesai']) }}"
       class="group block rounded-xl p-4 border-l-4 border-green-500 hover:shadow-lg transition-all text-center cc-card">
        <p class="text-2xl font-bold text-green-400">{{ $stats['completed'] }}</p>
        <p class="text-sm text-slate-400 mt-1">Selesai</p>
    </a>
    <div class="rounded-xl p-4 border-l-4 border-slate-600 text-center cc-card">
        <p class="text-lg font-bold text-slate-200">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_cost']) }}</p>
        <p class="text-sm text-slate-400 mt-1">Total Biaya</p>
    </div>
</div>

{{-- Tabs + Filter --}}
<div class="cc-card rounded-xl overflow-hidden mb-6">
    <div class="flex items-center justify-between px-5 py-3 border-b border-white/5 flex-wrap gap-3">

        {{-- Tab Pills --}}
        <div class="flex gap-1">
            <a href="{{ route('maintenance.index', array_merge(request()->except('tab','selesai_page'), ['tab' => 'antrian'])) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5
                      {{ $activeTab === 'antrian' ? 'bg-yellow-500/20 text-yellow-300 border border-yellow-500/30' : 'text-slate-400 hover:bg-gray-100/5' }}">
                <span class="material-symbols-outlined text-[16px]">schedule</span>
                Antrian
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[11px] font-bold
                             {{ $activeTab === 'antrian' ? 'bg-yellow-500/30 text-yellow-300' : 'bg-gray-100/10 text-slate-500' }}">
                    {{ $stats['scheduled'] + $stats['in_progress'] }}
                </span>
            </a>
            <a href="{{ route('maintenance.index', array_merge(request()->except('tab','selesai_page'), ['tab' => 'selesai'])) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-1.5
                      {{ $activeTab === 'selesai' ? 'bg-green-500/20 text-green-300 border border-green-500/30' : 'text-slate-400 hover:bg-gray-100/5' }}">
                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                Selesai
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[11px] font-bold
                             {{ $activeTab === 'selesai' ? 'bg-green-500/30 text-green-300' : 'bg-gray-100/10 text-slate-500' }}">
                    {{ $stats['completed'] }}
                </span>
            </a>
        </div>

        {{-- Kendaraan Filter + New Button --}}
        <div class="flex items-center gap-2">
            <form method="GET" action="{{ route('maintenance.index') }}" class="flex items-center gap-2">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <select name="vehicle_id" onchange="this.form.submit()"
                        class="dark-input text-[13px] py-1.5 px-3 rounded-lg">
                    <option value="">Semua Kendaraan</option>
                    @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" {{ request('vehicle_id') == $v->id ? 'selected' : '' }}>
                        {{ $v->plate_number }} — {{ $v->brand }} {{ $v->model }}
                    </option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('maintenance.create') }}" class="btn-primary text-[13px] py-1.5 px-3 flex items-center gap-1">
                <span class="material-symbols-outlined text-[15px]">add</span> Tambah
            </a>
        </div>
    </div>

    {{-- ANTRIAN TAB --}}
    @if($activeTab === 'antrian')
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                    <th class="text-left py-3 px-4">Kendaraan</th>
                    <th class="text-left py-3 px-4">Tipe</th>
                    <th class="text-left py-3 px-4">Deskripsi</th>
                    <th class="text-left py-3 px-4">Vendor</th>
                    <th class="text-left py-3 px-4">Jadwal</th>
                    <th class="text-center py-3 px-4">Status</th>
                    <th class="text-right py-3 px-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($antrian as $log)
                <tr class="border-b border-white/5 hover:bg-white/3 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $log->vehicle_id) }}"
                           class="font-mono font-semibold text-primary hover:underline">
                            {{ $log->vehicle->plate_number }}
                        </a>
                        <div class="text-xs text-slate-500">{{ $log->vehicle->model }}</div>
                    </td>
                    <td class="py-3 px-4 capitalize text-slate-300">{{ $log->type }}</td>
                    <td class="py-3 px-4 text-slate-400 max-w-[200px] truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $log->vendor ?? '—' }}</td>
                    <td class="py-3 px-4">
                        @php $sched = \Carbon\Carbon::parse($log->scheduled_date); @endphp
                        <span class="text-slate-300">{{ $sched->format('d M Y') }}</span>
                        @if($sched->isPast() && $log->status === 'scheduled')
                        <span class="ml-1 text-[10px] font-bold text-red-400">TELAT</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-center"><x-status-badge :status="$log->status" /></td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('maintenance.edit', $log->id) }}"
                           class="text-slate-400 hover:text-primary transition-colors">
                            <span class="material-symbols-outlined text-[16px]">edit</span>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">build</span>
                        Tidak ada pekerjaan dalam antrian
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- SELESAI TAB --}}
    @if($activeTab === 'selesai')
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                    <th class="text-left py-3 px-4">Kendaraan</th>
                    <th class="text-left py-3 px-4">Tipe</th>
                    <th class="text-left py-3 px-4">Deskripsi</th>
                    <th class="text-left py-3 px-4">Vendor</th>
                    <th class="text-left py-3 px-4">Jadwal</th>
                    <th class="text-left py-3 px-4">Selesai</th>
                    <th class="text-right py-3 px-4">Biaya</th>
                </tr>
            </thead>
            <tbody>
                @forelse($selesai as $log)
                <tr class="border-b border-white/5 hover:bg-white/3 transition-colors">
                    <td class="py-3 px-4">
                        <a href="{{ route('fleet.show', $log->vehicle_id) }}"
                           class="font-mono font-semibold text-primary hover:underline">
                            {{ $log->vehicle->plate_number }}
                        </a>
                        <div class="text-xs text-slate-500">{{ $log->vehicle->model }}</div>
                    </td>
                    <td class="py-3 px-4 capitalize text-slate-300">{{ $log->type }}</td>
                    <td class="py-3 px-4 text-slate-400 max-w-[200px] truncate" title="{{ $log->description }}">{{ $log->description }}</td>
                    <td class="py-3 px-4 text-slate-500">{{ $log->vendor ?? '—' }}</td>
                    <td class="py-3 px-4 text-slate-400">{{ \Carbon\Carbon::parse($log->scheduled_date)->format('d M Y') }}</td>
                    <td class="py-3 px-4 text-green-400 font-semibold">
                        {{ $log->completed_date ? \Carbon\Carbon::parse($log->completed_date)->format('d M Y') : '—' }}
                    </td>
                    <td class="py-3 px-4 text-right font-semibold text-slate-200">
                        {{ $log->cost ? \App\Helpers\FormatHelper::formatIDR($log->cost) : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-12 text-center text-slate-500">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-30">check_circle</span>
                        Belum ada maintenance yang selesai
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($selesai instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="px-5 py-3 border-t border-white/5">{{ $selesai->appends(request()->except('selesai_page'))->links() }}</div>
    @endif
    @endif
</div>

{{-- Pending Purchase Orders --}}
@if($upcomingPOs->count())
<div class="cc-card rounded-xl p-5 mt-4">
    <h3 class="text-[14px] font-bold text-slate-200 mb-3 flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px] text-amber-400">receipt_long</span>
        Purchase Orders Pending
    </h3>
    <div class="space-y-2">
        @foreach($upcomingPOs as $po)
        <div class="flex justify-between items-center p-3 rounded-lg border border-white/5 bg-white/3">
            <div>
                <p class="font-mono font-semibold text-slate-200 text-sm">{{ $po->po_number }}</p>
                <p class="text-xs text-slate-500">{{ $po->vendor }} — {{ Str::limit($po->item_description, 50) }}</p>
            </div>
            <div class="text-right">
                <p class="font-semibold text-slate-200">{{ \App\Helpers\FormatHelper::formatIDR($po->amount) }}</p>
                <x-status-badge :status="$po->status" />
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
