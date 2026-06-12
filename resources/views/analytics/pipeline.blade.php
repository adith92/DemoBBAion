@extends('layouts.app')

@section('header_title', 'Pipeline Analytics')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex items-center gap-3">
        <span class="material-symbols-outlined text-secondary text-[28px]">monitoring</span>
        <div>
            <h2 class="text-xl font-bold text-cc">Pipeline Analytics</h2>
            <p class="text-xs text-cc-muted">Konversi &amp; performa funnel penjualan</p>
        </div>
    </div>
    @include('components.analytics-nav')
    {{-- Win rate banner --}}
    <div class="bg-gradient-to-r from-[var(--color-secondary)] via-[#1e4fa8] to-secondary text-gray-900 rounded-2xl p-6 shadow-xl flex items-center justify-between">
        <div>
            <p class="text-blue-100 text-xs font-semibold uppercase tracking-wider">Overall Win Rate</p>
            <p class="text-4xl font-extrabold mt-1">{{ $overallWinRate }}%</p>
        </div>
        <span class="material-symbols-outlined text-[64px] opacity-20">trophy</span>
    </div>

    {{-- Stage cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @php
            $stageMeta = [
                'prospecting' => ['search','indigo'],
                'proposal'    => ['description','blue'],
                'negotiation' => ['gavel','amber'],
                'won'         => ['check_circle','emerald'],
                'lost'        => ['cancel','red'],
            ];
        @endphp
        @foreach($stages as $stage)
        @php [$icon,$color] = $stageMeta[$stage] ?? ['circle','slate']; $data = $stageData[$stage] ?? null; @endphp
        <div class="cc-card rounded-2xl shadow-sm border border-cc p-5">
            <div class="flex items-center justify-between mb-3">
                <span class="material-symbols-outlined text-{{ $color }}-600">{{ $icon }}</span>
                <span class="text-2xl font-extrabold text-cc">{{ $counts[$stage] ?? 0 }}</span>
            </div>
            <p class="text-xs font-bold text-cc-muted capitalize">{{ $stage }}</p>
            <p class="text-[11px] text-cc-muted mt-1">{{ \App\Helpers\FormatHelper::formatIDR($data->total_value ?? 0) }}</p>
        </div>
        @endforeach
    </div>

    {{-- Conversion rates --}}
    <div class="cc-card rounded-2xl shadow-sm border border-cc p-6">
        <h3 class="text-base font-bold text-cc mb-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-[20px] text-secondary">conversion_path</span>
            Conversion Rates
        </h3>
        <div class="space-y-4">
            @foreach($conversionRates as $key => $rate)
            <div>
                <div class="flex justify-between text-xs font-semibold text-cc-muted mb-1">
                    <span>{{ ucfirst(str_replace('_',' → ',str_replace('_to_',' to ',$key))) }}</span>
                    <span class="text-secondary font-bold">{{ $rate }}%</span>
                </div>
                <div class="w-full bg-cc-card rounded-full h-2.5 overflow-hidden">
                    <div class="bg-gradient-to-r from-[var(--color-secondary)] to-secondary h-full rounded-full" style="width: {{ min($rate,100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
