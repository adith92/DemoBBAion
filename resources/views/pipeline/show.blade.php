@extends('layouts.app')

@section('header_title', $opportunity->opp_number . ' - ' . $opportunity->title)

@section('content')
@php
$stages     = ['prospecting','proposal','negotiation','won','lost'];
$stageIndex = array_search($opportunity->stage, $stages);
$stageLabels = [
    'prospecting' => 'Prospekting',
    'proposal'    => 'Proposal',
    'negotiation' => 'Negosiasi',
    'won'         => 'Menang',
    'lost'        => 'Kalah',
];
$stageBadge = [
    'prospecting' => 'bg-blue-100 text-blue-700 border border-blue-200',
    'proposal'    => 'bg-amber-100 text-amber-700 border border-amber-200',
    'negotiation' => 'bg-orange-100 text-orange-700 border border-orange-200',
    'won'         => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
    'lost'        => 'bg-red-100 text-red-700 border border-red-200',
];
@endphp

<div class="p-4 md:p-6 space-y-6" x-data="{ showDiscountForm: false, showAdvanceForm: false, showLostForm: false }">

    {{-- Flash messages --}}
    @if(session('success'))
    <div class="flex items-start gap-3 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">
        <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Discount Approval Warning Banner --}}
    @if($opportunity->discount_percent > 0 && !$opportunity->discount_approved)
    <div class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg">
        <svg class="w-5 h-5 text-amber-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <div>
            <p class="text-sm font-semibold text-amber-800">Menunggu Persetujuan Diskon</p>
            <p class="text-xs text-amber-700 mt-0.5">
                Diskon <strong>{{ $opportunity->discount_percent }}%</strong> sedang menunggu persetujuan.
                Opportunity tidak dapat di-close sampai diskon disetujui.
            </p>
        </div>
    </div>
    @endif

    {{-- Header --}}
    <div class="cc-card rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2.5 py-1 rounded-md">
                        {{ $opportunity->opp_number }}
                    </span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $stageBadge[$opportunity->stage] }}">
                        {{ $stageLabels[$opportunity->stage] }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 mt-2">{{ $opportunity->title }}</h1>
                <p class="text-sm text-slate-500 mt-1">
                    <span class="font-medium text-slate-700">{{ $opportunity->client->company_name ?? '-' }}</span>
                    @if($opportunity->sales)
                     &mdash; Sales: {{ $opportunity->sales->name }}
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2 flex-shrink-0">
                {{-- Advance Stage --}}
                @if((!in_array($opportunity->stage, ['won', 'lost']) || ($opportunity->stage === 'lost' && (auth()->user()->isManager() || auth()->user()->isGM()))) && count($nextStages) > 0)
                <button
                    @click="showAdvanceForm = !showAdvanceForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                    </svg>
                    Advance Stage
                </button>
                @endif

                {{-- Mark Lost --}}
                @if(!in_array($opportunity->stage, ['won', 'lost']))
                <button
                    @click="showLostForm = !showLostForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 border border-red-200 text-sm font-semibold rounded-lg hover:bg-red-100 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Mark Lost
                </button>
                @endif

                <a href="{{ route('pipeline.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 border border-slate-200 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors duration-200 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        {{-- Stage Progress Bar --}}
        <div class="mt-6">
            <div class="flex items-center">
                @foreach(['prospecting','proposal','negotiation','won'] as $i => $s)
                @php $idx = array_search($s, $stages); @endphp
                <div class="flex-1 flex flex-col items-center relative">
                    <div @class([
                        'w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold border-2 z-10 transition-all duration-300',
                        'bg-emerald-500 border-emerald-500 text-white' => $stageIndex > $idx,
                        'bg-blue-600 border-blue-600 text-white ring-4 ring-blue-100' => $stageIndex === $idx && $opportunity->stage !== 'lost',
                        'bg-red-500 border-red-500 text-white' => $opportunity->stage === 'lost',
                        'cc-card border-slate-200 text-slate-400' => $stageIndex < $idx && $opportunity->stage !== 'lost',
                    ])>
                        @if($stageIndex > $idx)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        @else
                            {{ $idx + 1 }}
                        @endif
                    </div>
                    <span class="mt-1.5 text-xs font-medium {{ $stageIndex === $idx ? 'text-blue-700' : ($stageIndex > $idx ? 'text-emerald-600' : 'text-slate-400') }}">
                        {{ $stageLabels[$s] }}
                    </span>
                    @if($i < 3)
                    <div @class([
                        'absolute top-4.5 left-1/2 w-full h-0.5 -z-0',
                        'bg-emerald-400' => $stageIndex > $idx,
                        'bg-slate-200' => $stageIndex <= $idx,
                    ]) style="transform: translateX(50%); width: calc(100% - 36px); left: calc(50% + 18px);"></div>
                    @endif
                </div>
                @endforeach
            </div>
            @if($opportunity->stage === 'lost')
            <p class="text-center text-xs text-red-500 mt-2 font-medium">
                Deal ini ditandai sebagai Kalah
                @if($opportunity->lost_reason) — {{ $opportunity->lost_reason }} @endif
            </p>
            @endif
        </div>
    </div>

    {{-- Advance Stage Form --}}
    <div x-show="showAdvanceForm" x-transition class="cc-card rounded-2xl border border-blue-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-slate-800 mb-4">Advance Stage</h3>
        <form action="{{ route('opportunities.advance-stage', $opportunity->id) }}" method="POST">
            @csrf
            <div class="flex flex-wrap gap-3 mb-4">
                @foreach($nextStages as $ns)
                @if($ns !== 'lost')
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="stage" value="{{ $ns }}" class="text-blue-600" required>
                    <span class="text-sm font-medium text-slate-700">{{ $stageLabels[$ns] ?? $ns }}</span>
                </label>
                @endif
                @endforeach
            </div>
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan (opsional)</label>
                <textarea name="notes" rows="2" class="w-full text-sm border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Catatan perubahan stage..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 cursor-pointer transition-colors">Konfirmasi</button>
                <button type="button" @click="showAdvanceForm = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
            </div>
        </form>
    </div>

    {{-- Mark Lost Form --}}
    <div x-show="showLostForm" x-transition class="cc-card rounded-2xl border border-red-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-red-700 mb-4">Tandai sebagai Kalah</h3>
        <form action="{{ route('opportunities.advance-stage', $opportunity->id) }}" method="POST">
            @csrf
            <input type="hidden" name="stage" value="lost">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan Kalah <span class="text-red-500">*</span></label>
                <textarea name="lost_reason" rows="3" required class="w-full text-sm border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-red-400" placeholder="Jelaskan alasan deal tidak berhasil..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 cursor-pointer transition-colors">Konfirmasi Kalah</button>
                <button type="button" @click="showLostForm = false" class="px-4 py-2 border border-slate-200 text-slate-600 text-sm rounded-lg hover:bg-slate-50 cursor-pointer transition-colors">Batal</button>
            </div>
        </form>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left: Opportunity Info --}}
        <div class="lg:col-span-1 space-y-5">

            <div class="cc-card rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Informasi Deal</h2>
                <dl class="space-y-3">
                    @if($opportunity->product)
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Produk</dt>
                        <dd class="text-sm text-slate-800 font-semibold">{{ $opportunity->product->name }}</dd>
                        <dd class="text-xs text-slate-400 font-mono">{{ $opportunity->product->sku }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Estimasi Nilai</dt>
                        <dd class="text-sm font-bold text-slate-900">
                            {{ $opportunity->estimated_value ? 'Rp '.number_format((float)$opportunity->estimated_value,0,',','.') : '-' }}
                        </dd>
                    </div>
                    @if($opportunity->final_value)
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Nilai Final</dt>
                        <dd class="text-sm font-bold text-emerald-700">
                            Rp {{ number_format((float)$opportunity->final_value,0,',','.') }}
                        </dd>
                    </div>
                    @endif
                    @if($opportunity->pax)
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Pax</dt>
                        <dd class="text-sm text-slate-800">{{ $opportunity->pax }} pax</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Diskon</dt>
                        <dd class="text-sm">
                            @if($opportunity->discount_percent > 0)
                            <span class="font-semibold text-amber-700">{{ $opportunity->discount_percent }}%</span>
                            @if($opportunity->discount_approved)
                                <span class="ml-1.5 text-xs bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full font-medium">Disetujui</span>
                            @else
                                <span class="ml-1.5 text-xs bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded-full font-medium">Pending</span>
                            @endif
                            @else
                            <span class="text-slate-400">Tidak ada</span>
                            @endif
                        </dd>
                    </div>
                    @if($opportunity->expected_close_date)
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Target Close</dt>
                        <dd class="text-sm text-slate-800">{{ $opportunity->expected_close_date->format('d M Y') }}</dd>
                    </div>
                    @endif
                    @if($opportunity->actual_close_date)
                    <div>
                        <dt class="text-xs text-slate-400 font-medium mb-0.5">Close Aktual</dt>
                        <dd class="text-sm text-slate-800">{{ $opportunity->actual_close_date->format('d M Y') }}</dd>
                    </div>
                    @endif
                </dl>

                {{-- Discount Form Toggle --}}
                @if(!in_array($opportunity->stage, ['won','lost']))
                <div class="mt-4 pt-4 border-t border-slate-100">
                    <button @click="showDiscountForm = !showDiscountForm"
                            class="text-xs text-blue-600 hover:text-blue-800 font-semibold cursor-pointer flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M17 17h.01M7 17h.01M17 7h.01M3 3l18 18"/>
                        </svg>
                        Ajukan Diskon
                    </button>

                    <div x-show="showDiscountForm" x-transition class="mt-3">
                        <form action="{{ route('opportunities.discount', $opportunity->id) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Persen Diskon</label>
                                <div class="relative">
                                    <input type="number" name="discount_percent" min="0" max="100" step="0.01"
                                           value="{{ $opportunity->discount_percent ?? 0 }}"
                                           class="w-full text-sm border border-slate-200 rounded-lg p-2.5 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan</label>
                                <textarea name="notes" rows="2" class="w-full text-sm border border-slate-200 rounded-lg p-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Alasan diskon..."></textarea>
                            </div>
                            <button type="submit" class="w-full py-2 bg-amber-500 text-white text-sm font-semibold rounded-lg hover:bg-amber-600 cursor-pointer transition-colors">
                                Ajukan Diskon
                            </button>
                        </form>
                    </div>
                </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($opportunity->notes)
            <div class="cc-card rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-3">Catatan</h2>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $opportunity->notes }}</p>
            </div>
            @endif

        </div>

        {{-- Right: Activity Logs + Approval Requests --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Approval Requests --}}
            @if($approvalRequests->isNotEmpty())
            <div class="cc-card rounded-2xl border border-slate-100 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-700 mb-4">Approval Diskon</h2>
                <div class="space-y-3">
                    @foreach($approvalRequests as $ar)
                    <div class="flex items-start justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <div>
                            <div class="flex items-center gap-2">
                                <span @class([
                                    'text-xs font-semibold px-2 py-0.5 rounded-full',
                                    'bg-amber-100 text-amber-700' => $ar->status === 'pending',
                                    'bg-emerald-100 text-emerald-700' => $ar->status === 'approved',
                                    'bg-red-100 text-red-700' => $ar->status === 'rejected',
                                    'bg-blue-100 text-blue-700' => $ar->status === 'escalated',
                                ])>
                                    {{ ucfirst($ar->status) }}
                                </span>
                                <span class="text-xs text-slate-500">Level {{ $ar->level }}</span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1">
                                Diskon <strong>{{ $ar->discount_percent }}%</strong>
                                &mdash; Diminta: Rp {{ number_format((float)$ar->original_price,0,',','.') }}
                                &rarr; Rp {{ number_format((float)$ar->final_price,0,',','.') }}
                            </p>
                            @if($ar->currentApprover)
                            <p class="text-xs text-slate-400 mt-0.5">Approver: {{ $ar->currentApprover->name }}</p>
                            @endif
                            @if($ar->rejection_reason)
                            <p class="text-xs text-red-600 mt-0.5">Alasan: {{ $ar->rejection_reason }}</p>
                            @endif
                        </div>
                        <span class="text-xs text-slate-400 flex-shrink-0">
                            {{ $ar->created_at->format('d M') }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Activity Logs --}}
            <div class="cc-card rounded-2xl border border-slate-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-slate-700">Log Aktivitas</h2>
                    <a href="{{ route('activities.create', ['opportunity_id' => $opportunity->id]) }}"
                       class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600 hover:text-blue-800 cursor-pointer transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Aktivitas
                    </a>
                </div>

                @if($activityLogs->isEmpty())
                <div class="text-center py-8 text-sm text-slate-400">
                    <svg class="w-10 h-10 mx-auto text-slate-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Belum ada aktivitas tercatat
                </div>
                @else
                <div class="relative">
                    <div class="absolute left-4 top-0 bottom-0 w-px bg-slate-100"></div>
                    <div class="space-y-4">
                        @foreach($activityLogs as $log)
                        @php
                        $typeIcons = [
                            'meeting'   => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'color' => 'bg-blue-500'],
                            'call'      => ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'color' => 'bg-green-500'],
                            'visit'     => ['icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z', 'color' => 'bg-purple-500'],
                            'follow_up' => ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'color' => 'bg-amber-500'],
                            'email'     => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'bg-slate-500'],
                            'demo'      => ['icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'bg-indigo-500'],
                        ];
                        $typeConf = $typeIcons[$log->type] ?? $typeIcons['follow_up'];
                        @endphp
                        <div class="flex gap-4 pl-2">
                            <div class="w-6 h-6 rounded-full {{ $typeConf['color'] }} flex items-center justify-center flex-shrink-0 z-10">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeConf['icon'] }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 pb-4">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="text-sm font-semibold text-slate-800">{{ $log->subject }}</p>
                                    <span class="text-xs text-slate-400 flex-shrink-0">
                                        {{ $log->activity_date->format('d M H:i') }}
                                    </span>
                                </div>
                                @if($log->notes)
                                <p class="text-xs text-slate-500 mt-1">{{ $log->notes }}</p>
                                @endif
                                @if($log->outcome)
                                <p class="text-xs text-emerald-600 mt-1 font-medium">Hasil: {{ $log->outcome }}</p>
                                @endif
                                @if($log->next_action && $log->next_action_date)
                                <div class="flex items-center gap-1 mt-1.5 text-xs text-amber-600 bg-amber-50 px-2 py-1 rounded-md w-fit">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $log->next_action }} — {{ \Carbon\Carbon::parse($log->next_action_date)->format('d M Y') }}
                                </div>
                                @endif
                                <p class="text-xs text-slate-400 mt-1">{{ $log->sales->name ?? '-' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

        </div>
    </div>

</div>
@endsection
