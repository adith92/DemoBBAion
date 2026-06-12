@extends('layouts.app')

@section('header_title', 'Manager Dashboard')

@section('content')
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- Row 1: Team Overview Cards (3 cards) --}}
    <div class="grid-stack-item" gs-id="w-pipeline-tim" gs-x="0" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Pipeline Tim</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">
                    Rp {{ number_format($teamPipelineValue, 0, ',', '.') }}
                </p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">Total value aktif</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-won-alltime" gs-x="4" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Won (All Time)</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $teamWon }}</p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $teamLost }} lost</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-anggota-tim" gs-x="8" gs-y="0" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] p-5 shadow-sm h-full">
                <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide font-semibold">Anggota Tim</p>
                <p class="text-2xl font-bold mt-1" style="color:var(--cc-text)">{{ $teamMembers->count() }}</p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-1">Sales aktif</p>
            </div>
        </div>
    </div>

    {{-- Revenue Trend Chart --}}
    <div class="grid-stack-item" gs-id="w-revenue-chart" gs-x="0" gs-y="2" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm p-5 h-full">
                <h3 class="font-semibold mb-4" style="color:var(--cc-text)">Revenue Trend (6 Months) - Seluruh Tim</h3>
                <div id="revenueChart" style="min-height:280px"></div>
            </div>
        </div>
    </div>

    {{-- Pipeline per Sales --}}
    <div class="grid-stack-item" gs-id="w-pipeline-breakdown" gs-x="0" gs-y="7" gs-w="8" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">Pipeline per Sales (Stage Breakdown)</h3>
                </div>
                <div class="p-5 space-y-4 overflow-y-auto" style="max-height:calc(100% - 56px)">
                    @php
                        $stageColors = [
                            'prospecting' => 'bg-gray-400 dark:bg-gray-600',
                            'proposal'    => 'bg-blue-400 dark:bg-blue-500',
                            'negotiation' => 'bg-yellow-400 dark:bg-yellow-500',
                        ];
                        $stageLabelsMap = [
                            'prospecting' => 'Prospecting',
                            'proposal'    => 'Proposal',
                            'negotiation' => 'Negosiasi',
                        ];
                    @endphp

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-3 text-xs text-[var(--cc-text)]">
                        @foreach($stages as $s)
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded {{ $stageColors[$s] ?? 'bg-gray-300 dark:bg-gray-600' }} inline-block"></span>
                            {{ $stageLabelsMap[$s] ?? $s }}
                        </span>
                        @endforeach
                    </div>

                    @forelse($stageBreakdown as $row)
                    @php $rowTotal = array_sum($row['totals']); @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-[var(--cc-text)]">{{ $row['name'] }}</span>
                            <span class="text-xs text-[var(--cc-text-muted)]">{{ $rowTotal }} total</span>
                        </div>
                        <div class="flex h-5 rounded-full overflow-hidden bg-gray-100 dark:bg-gray-800">
                            @foreach($stages as $s)
                            @if(isset($row['totals'][$s]) && $row['totals'][$s] > 0 && $rowTotal > 0)
                            <div class="{{ $stageColors[$s] ?? 'bg-gray-300 dark:bg-gray-600' }} flex items-center justify-center text-gray-900 text-xs"
                                 style="width: {{ round(($row['totals'][$s] / $rowTotal) * 100) }}%"
                                 title="{{ $stageLabelsMap[$s] ?? $s }}: {{ $row['totals'][$s] }}">
                                {{ $row['totals'][$s] }}
                            </div>
                            @endif
                            @endforeach
                            @if($rowTotal == 0)
                            <div class="flex-1 flex items-center justify-center text-xs text-[var(--cc-text-muted)]">Belum ada</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-[var(--cc-text-muted)]">Belum ada anggota tim.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar: Recent Activities --}}
    <div class="grid-stack-item" gs-id="w-recent-activities" gs-x="8" gs-y="7" gs-w="4" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">Aktivitas Terbaru Tim</h3>
                </div>
                <div class="divide-y divide-[var(--cc-border)] overflow-y-auto" style="max-height:calc(100% - 100px)">
                    @php
                        $activityIcons = [
                            'meeting'    => '🤝',
                            'call'       => '📞',
                            'visit'      => '🚗',
                            'follow_up'  => '📋',
                            'email'      => '📧',
                            'demo'       => '🎯',
                        ];
                    @endphp
                    @forelse($recentActivities as $activity)
                    <div class="px-5 py-3 hover:bg-black/5 dark:hover:bg-gray-100/5">
                        <div class="flex items-start gap-2">
                            <span class="text-lg leading-none mt-0.5">
                                {{ $activityIcons[$activity->type] ?? '📌' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-[var(--cc-text)] truncate">{{ $activity->subject }}</p>
                                <p class="text-xs text-[var(--cc-text-muted)]">
                                    {{ optional($activity->sales)->name ?? '-' }}
                                    @if($activity->client) &bull; {{ optional($activity->client)->company_name }} @endif
                                </p>
                                <p class="text-xs text-[var(--cc-text-muted)]">{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-6 text-center text-[var(--cc-text-muted)] text-sm">Belum ada aktivitas.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-[var(--cc-border)]">
                    <a href="{{ route('activities.index') }}" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Semua aktivitas</a>
                </div>
            </div>
        </div>
    </div>

    {{-- KPI Achievement Table --}}
    <div class="grid-stack-item" gs-id="w-kpi-achievement" gs-x="0" gs-y="13" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl border border-[var(--cc-border)] shadow-sm overflow-hidden h-full">
                <div class="px-5 py-4 border-b border-[var(--cc-border)]">
                    <h3 class="font-semibold text-[var(--cc-text)]">KPI Tim - {{ now()->format('F Y') }}</h3>
                </div>
                <div class="overflow-x-auto" style="max-height:calc(100% - 56px)">
                    <table class="w-full text-sm">
                        <thead class="bg-black/5 dark:bg-gray-100/5 text-xs text-[var(--cc-text-muted)] uppercase sticky top-0">
                            <tr>
                                <th class="px-4 py-3 text-left">Sales</th>
                                <th class="px-4 py-3 text-center">Won</th>
                                <th class="px-4 py-3 text-center">Win Rate</th>
                                <th class="px-4 py-3 text-right">Revenue</th>
                                <th class="px-4 py-3 text-center">KPI%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[var(--cc-border)]">
                            @forelse($teamMembers as $member)
                            <tr class="hover:bg-black/5 dark:hover:bg-gray-100/5">
                                <td class="px-4 py-3 font-medium text-[var(--cc-text)]">{{ $member->name }}</td>
                                <td class="px-4 py-3 text-center text-green-600 dark:text-green-400 font-semibold">{{ $member->won_count }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded font-medium
                                        {{ $member->win_rate >= 50 ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400' }}">
                                        {{ $member->win_rate }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-[var(--cc-text)]">Rp {{ number_format($member->won_revenue ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center gap-1 justify-center">
                                        <div class="w-14 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $member->kpi_pct >= 100 ? 'bg-green-500' : ($member->kpi_pct >= 60 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                                style="width: {{ min($member->kpi_pct, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-[var(--cc-text)]">{{ $member->kpi_pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-[var(--cc-text-muted)]">Belum ada anggota tim.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</x-dashboard-grid>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.classList.contains('dark');
    var textColor = isDark ? '#94a3b8' : '#64748b';

    // Revenue Trend
    var revData = {!! json_encode($revenueTrend ?? ['labels'=>[],'data'=>[]]) !!};
    var revenueOptions = {
        series: [{ name: "Revenue Tim", data: revData.data }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
        colors: ['#3b82f6'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [50, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        xaxis: { categories: revData.labels, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor }, formatter: function (val) { return "Rp " + (val/1000000).toFixed(0) + "M"; } } },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (val) { return "Rp " + new Intl.NumberFormat('id-ID').format(val); } } }
    };
    new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();
});
</script>
@endpush
@endsection
