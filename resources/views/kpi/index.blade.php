@extends('layouts.app')

@section('header_title', 'Performa & Target')

@section('content')
@php
    use Carbon\Carbon;
    $monthNames = [
        1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
        7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
    ];
    $isManager = auth()->user()->isManager() || auth()->user()->isGM() || auth()->user()->isDirector();
    function kpiBar(int|float $actual, int|float $target, string $color = 'blue'): array {
        $pct = $target > 0 ? min(100, round(($actual / $target) * 100, 1)) : ($actual > 0 ? 100 : 0);
        return ['pct' => $pct, 'color' => $color];
    }
    function rupiah(float|string $val): string {
        return 'Rp ' . number_format((float)$val, 0, ',', '.');
    }
@endphp

<div x-data="{
    showSetTargetModal: false,
    targetUserId: '',
    targetYear: {{ $year }},
    targetMonth: {{ $month }},
}">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Performa & Target KPI</h1>
            <p class="text-sm text-gray-500 mt-0.5">{{ $monthNames[$month] }} {{ $year }}</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Month/Year selector --}}
            <form method="GET" class="flex items-center gap-2">
                <select name="month" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @foreach($monthNames as $m => $mn)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $mn }}</option>
                    @endforeach
                </select>
                <select name="year" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="bg-blue-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">Tampilkan</button>
            </form>

            @if($isManager)
            <button @click="showSetTargetModal = true"
                    class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Set Target
            </button>
            @endif
        </div>
    </div>

    {{-- Overall Score Card --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 text-white rounded-2xl p-5 col-span-1">
            <div class="text-blue-200 text-sm font-medium mb-1">Skor Keseluruhan</div>
            <div class="text-4xl font-bold">{{ $overallScore }}%</div>
            <div class="mt-3 bg-blue-500/50 rounded-full h-2">
                <div class="bg-white rounded-full h-2 transition-all" style="width: {{ min(100, $overallScore) }}%"></div>
            </div>
            <div class="text-blue-200 text-xs mt-2">Rata-rata pencapaian semua KPI</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="text-gray-500 text-sm mb-1">Total Aktivitas</div>
            <div class="text-3xl font-bold text-gray-900">
                {{ $ownTarget->actual_meetings + $ownTarget->actual_calls + $ownTarget->actual_visits }}
            </div>
            <div class="text-xs text-gray-400 mt-1">Meeting + Panggilan + Kunjungan</div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="text-gray-500 text-sm mb-1">Deals Won</div>
            <div class="text-3xl font-bold text-green-600">{{ $ownTarget->actual_won }}</div>
            <div class="text-xs text-gray-400 mt-1">dari target {{ $ownTarget->target_won }}</div>
        </div>
    </div>

    {{-- KPI Progress Bars --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-5">Pencapaian KPI Bulan Ini</h3>

        <div class="space-y-5">
            @php
                $kpis = [
                    ['icon' => '🤝', 'label' => 'Meeting', 'actual' => $ownTarget->actual_meetings, 'target' => $ownTarget->target_meetings, 'color' => 'blue', 'unit' => 'meeting'],
                    ['icon' => '📞', 'label' => 'Panggilan / Follow-up', 'actual' => $ownTarget->actual_calls, 'target' => $ownTarget->target_calls, 'color' => 'green', 'unit' => 'panggilan'],
                    ['icon' => '🚗', 'label' => 'Kunjungan', 'actual' => $ownTarget->actual_visits, 'target' => $ownTarget->target_visits, 'color' => 'purple', 'unit' => 'kunjungan'],
                    ['icon' => '🎯', 'label' => 'Opportunity Dibuat', 'actual' => $ownTarget->actual_opportunities, 'target' => $ownTarget->target_opportunities, 'color' => 'orange', 'unit' => 'opportunity'],
                    ['icon' => '🏆', 'label' => 'Deals Won', 'actual' => $ownTarget->actual_won, 'target' => $ownTarget->target_won, 'color' => 'yellow', 'unit' => 'deal'],
                ];
                $colorMap = [
                    'blue' => ['bar' => 'bg-blue-500', 'text' => 'text-blue-700', 'bg' => 'bg-blue-50'],
                    'green' => ['bar' => 'bg-green-500', 'text' => 'text-green-700', 'bg' => 'bg-green-50'],
                    'purple' => ['bar' => 'bg-purple-500', 'text' => 'text-purple-700', 'bg' => 'bg-purple-50'],
                    'orange' => ['bar' => 'bg-orange-500', 'text' => 'text-orange-700', 'bg' => 'bg-orange-50'],
                    'yellow' => ['bar' => 'bg-yellow-500', 'text' => 'text-yellow-700', 'bg' => 'bg-yellow-50'],
                    'red' => ['bar' => 'bg-red-500', 'text' => 'text-red-700', 'bg' => 'bg-red-50'],
                ];
            @endphp

            @foreach($kpis as $kpi)
            @php
                $bar = kpiBar($kpi['actual'], $kpi['target']);
                $c = $colorMap[$kpi['color']];
                $pct = $bar['pct'];
                $barColor = $pct >= 100 ? 'bg-green-500' : ($pct >= 70 ? $c['bar'] : ($pct >= 40 ? 'bg-orange-400' : 'bg-red-400'));
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $kpi['icon'] }}</span>
                        <span class="font-medium text-gray-800 text-sm">{{ $kpi['label'] }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">
                            <span class="font-bold text-gray-900">{{ $kpi['actual'] }}</span>
                            / {{ $kpi['target'] }} {{ $kpi['unit'] }}
                        </span>
                        <span class="text-sm font-bold {{ $pct >= 100 ? 'text-green-600' : ($pct >= 70 ? $c['text'] : 'text-orange-600') }}">
                            {{ $pct }}%
                        </span>
                    </div>
                </div>
                <div class="bg-gray-100 rounded-full h-2.5">
                    <div class="{{ $barColor }} rounded-full h-2.5 transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach

            {{-- Revenue --}}
            @php
                $revActual = (float) $ownTarget->actual_revenue;
                $revTarget = (float) $ownTarget->target_revenue;
                $revPct = $revTarget > 0 ? min(100, round(($revActual / $revTarget) * 100, 1)) : ($revActual > 0 ? 100 : 0);
                $revBarColor = $revPct >= 100 ? 'bg-green-500' : ($revPct >= 70 ? 'bg-blue-500' : ($revPct >= 40 ? 'bg-orange-400' : 'bg-red-400'));
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">💰</span>
                        <span class="font-medium text-gray-800 text-sm">Revenue</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-600">
                            <span class="font-bold text-gray-900">{{ rupiah($revActual) }}</span>
                            / {{ rupiah($revTarget) }}
                        </span>
                        <span class="text-sm font-bold {{ $revPct >= 100 ? 'text-green-600' : ($revPct >= 70 ? 'text-blue-700' : 'text-orange-600') }}">
                            {{ $revPct }}%
                        </span>
                    </div>
                </div>
                <div class="bg-gray-100 rounded-full h-2.5">
                    <div class="{{ $revBarColor }} rounded-full h-2.5 transition-all duration-500" style="width: {{ $revPct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Last 6 Months Chart --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tren 6 Bulan Terakhir</h3>
        <div class="h-64">
            <canvas id="kpiTrendChart"></canvas>
        </div>
    </div>

    {{-- Team KPI Table (Manager/GM/Director) --}}
    @if($isManager && $teamUsers->isNotEmpty())
    <div class="cc-card rounded-2xl overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between flex-wrap gap-3">
            <h3 class="text-[15px] font-bold text-slate-200">KPI Tim Sales — {{ $monthNames[$month] }} {{ $year }}</h3>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-500">{{ $teamUsers->count() }} anggota</span>
                <form method="GET" action="{{ route('kpi.index') }}" class="flex items-center gap-1">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <input type="hidden" name="month" value="{{ $month }}">
                    <label class="text-xs text-slate-500">Urutkan:</label>
                    <select name="sort_team" onchange="this.form.submit()"
                            class="dark-input text-[12px] py-1 px-2 rounded-lg ml-1">
                        <option value="revenue" {{ ($sortTeam ?? 'revenue') === 'revenue' ? 'selected' : '' }}>Revenue ↓</option>
                        <option value="kpi_pct" {{ ($sortTeam ?? '') === 'kpi_pct'  ? 'selected' : '' }}>Skor KPI ↓</option>
                        <option value="name"    {{ ($sortTeam ?? '') === 'name'     ? 'selected' : '' }}>Nama A-Z</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] uppercase text-slate-500 font-semibold">
                        <th class="text-left px-4 py-3">Sales</th>
                        <th class="text-center px-3 py-3">Meeting</th>
                        <th class="text-center px-3 py-3">Panggilan</th>
                        <th class="text-center px-3 py-3">Kunjungan</th>
                        <th class="text-center px-3 py-3">Oppty</th>
                        <th class="text-center px-3 py-3">Won</th>
                        <th class="text-right px-4 py-3">Revenue</th>
                        <th class="text-center px-4 py-3">Skor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($teamTargets as $tt)
                    @php
                        if (!$tt->user) continue;
                        $tu = $tt->user;
                        $scores = [];
                        if ($tt->target_meetings > 0) $scores[] = min(100, ($tt->actual_meetings / $tt->target_meetings) * 100);
                        if ($tt->target_calls > 0) $scores[] = min(100, ($tt->actual_calls / $tt->target_calls) * 100);
                        if ($tt->target_visits > 0) $scores[] = min(100, ($tt->actual_visits / $tt->target_visits) * 100);
                        if ($tt->target_opportunities > 0) $scores[] = min(100, ($tt->actual_opportunities / $tt->target_opportunities) * 100);
                        if ($tt->target_won > 0) $scores[] = min(100, ($tt->actual_won / $tt->target_won) * 100);
                        if ((float)$tt->target_revenue > 0) $scores[] = min(100, ((float)$tt->actual_revenue / (float)$tt->target_revenue) * 100);
                        $teamScore = count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
                        $scoreColor = $teamScore >= 80 ? 'text-green-400 bg-green-900/40' : ($teamScore >= 50 ? 'text-yellow-400 bg-yellow-900/40' : 'text-red-400 bg-red-900/40');
                    @endphp
                    <tr class="hover:bg-white/3 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-slate-200">{{ $tu->name }}</div>
                            <div class="text-xs text-slate-500 uppercase">{{ $tu->role }}</div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-semibold text-slate-200">{{ $tt->actual_meetings }}</span>
                            <span class="text-slate-600">/{{ $tt->target_meetings }}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-semibold text-slate-200">{{ $tt->actual_calls }}</span>
                            <span class="text-slate-600">/{{ $tt->target_calls }}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-semibold text-slate-200">{{ $tt->actual_visits }}</span>
                            <span class="text-slate-600">/{{ $tt->target_visits }}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-semibold text-slate-200">{{ $tt->actual_opportunities }}</span>
                            <span class="text-slate-600">/{{ $tt->target_opportunities }}</span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-semibold text-green-400">{{ $tt->actual_won }}</span>
                            <span class="text-slate-600">/{{ $tt->target_won }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="text-xs font-semibold text-slate-200">{{ rupiah((float)$tt->actual_revenue) }}</div>
                            <div class="text-xs text-slate-600">/ {{ rupiah((float)$tt->target_revenue) }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-block px-2 py-1 rounded-full text-xs font-bold {{ $scoreColor }}">
                                {{ $teamScore }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Set Target Modal --}}
    @if($isManager)
    <div x-show="showSetTargetModal"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
         @click.self="showSetTargetModal = false">

        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden"
             @click.stop>
            <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-5 flex items-center justify-between">
                <h3 class="text-lg font-bold text-white">Set Target KPI</h3>
                <button @click="showSetTargetModal = false" class="text-green-200 hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form method="POST" action="{{ route('kpi.store') }}" class="p-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                    <div class="sm:col-span-1">
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Sales / Manager</label>
                        <select name="user_id" x-model="targetUserId" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            <option value="">-- Pilih --</option>
                            @foreach($salesUsers as $su)
                            <option value="{{ $su->id }}">{{ $su->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Tahun</label>
                        <select name="period_year" x-model="targetYear" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Bulan</label>
                        <select name="period_month" x-model="targetMonth" required
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                            @foreach($monthNames as $m => $mn)
                            <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>{{ $mn }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @php
                        $targetFields = [
                            ['name' => 'target_meetings', 'label' => 'Target Meeting', 'icon' => '🤝', 'type' => 'number'],
                            ['name' => 'target_calls', 'label' => 'Target Panggilan', 'icon' => '📞', 'type' => 'number'],
                            ['name' => 'target_visits', 'label' => 'Target Kunjungan', 'icon' => '🚗', 'type' => 'number'],
                            ['name' => 'target_opportunities', 'label' => 'Target Opportunity', 'icon' => '🎯', 'type' => 'number'],
                            ['name' => 'target_won', 'label' => 'Target Deals Won', 'icon' => '🏆', 'type' => 'number'],
                            ['name' => 'target_revenue', 'label' => 'Target Revenue (IDR)', 'icon' => '💰', 'type' => 'number'],
                        ];
                    @endphp
                    @foreach($targetFields as $tf)
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">{{ $tf['icon'] }} {{ $tf['label'] }}</label>
                        <input type="{{ $tf['type'] }}" name="{{ $tf['name'] }}" min="0"
                               placeholder="0"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500">
                    </div>
                    @endforeach
                </div>

                <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                    <button type="button" @click="showSetTargetModal = false"
                            class="px-5 py-2 text-sm font-medium text-gray-600 border border-gray-300 rounded-xl hover:bg-gray-50 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-xl transition-colors text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Simpan Target
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

</div>

{{-- Chart.js --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('kpiTrendChart');
    if (!ctx) return;

    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Meeting',
                    data: chartData.meetings,
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderRadius: 4,
                },
                {
                    label: 'Panggilan',
                    data: chartData.calls,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderRadius: 4,
                },
                {
                    label: 'Revenue (juta)',
                    data: chartData.revenue.map(r => +(r / 1000000).toFixed(1)),
                    type: 'line',
                    borderColor: 'rgba(234, 179, 8, 1)',
                    backgroundColor: 'rgba(234, 179, 8, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(234, 179, 8, 1)',
                    tension: 0.4,
                    yAxisID: 'y2',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.label === 'Revenue (juta)') {
                                return 'Revenue: Rp ' + ctx.parsed.y.toFixed(1) + 'jt';
                            }
                            return ctx.dataset.label + ': ' + ctx.parsed.y;
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    title: { display: true, text: 'Jumlah' }
                },
                y2: {
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    title: { display: true, text: 'Revenue (juta IDR)' }
                }
            }
        }
    });
});
</script>
@endsection
