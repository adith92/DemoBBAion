@extends('layouts.app')

@section('header_title', 'Manager Dashboard')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between mb-2">
        <h2 class="text-xl font-bold" style="color:var(--cc-text)">Ringkasan Tim</h2>
        <button onclick="CRM_Widget && CRM_Widget.open()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-white/10 hover:bg-white/5 transition-colors text-sm font-semibold" style="color:var(--cc-text)">
            <span class="material-symbols-outlined text-[18px]">tune</span>
            Kustomisasi Dashboard
        </button>
    </div>

    {{-- Team Overview Cards --}}
    <div id="widget-team-overview" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="cc-card rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Pipeline Tim</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">
                Rp {{ number_format($teamPipelineValue, 0, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Total value aktif</p>
        </div>

        <div class="cc-card rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Won (All Time)</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ $teamWon }}</p>
            <p class="text-xs text-gray-400 mt-1">{{ $teamLost }} lost</p>
        </div>

        <div class="cc-card rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Approval Level-1</p>
            <p class="text-2xl font-bold {{ $pendingApprovals > 0 ? 'text-red-600' : 'text-gray-700' }} mt-1">
                {{ $pendingApprovals }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Menunggu keputusan Anda</p>
        </div>

        <div class="cc-card rounded-xl border border-gray-200 p-5 shadow-sm">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-semibold">Anggota Tim</p>
            <p class="text-2xl font-bold text-gray-700 mt-1" style="color:var(--cc-text)">{{ $teamMembers->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">Sales aktif</p>
        </div>
    </div>

    {{-- Charts Section --}}
    <div id="widget-revenue-chart" class="cc-card rounded-xl border border-gray-200 shadow-sm p-5 mb-6">
        <h3 class="font-semibold text-gray-800 mb-4" style="color:var(--cc-text)">Revenue Trend (6 Months) - Seluruh Tim</h3>
        <div id="revenueChart" class="min-h-[300px]"></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Pipeline Stage Bars per Sales --}}
        <div class="lg:col-span-2 space-y-6">

            <div id="widget-pipeline-breakdown" class="cc-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Pipeline per Sales (Stage Breakdown)</h3>
                </div>
                <div class="p-5 space-y-4">
                    @php
                        $stageColors = [
                            'prospecting' => 'bg-gray-400',
                            'proposal'    => 'bg-blue-400',
                            'negotiation' => 'bg-yellow-400',
                        ];
                        $stageLabels = [
                            'prospecting' => 'Prospecting',
                            'proposal'    => 'Proposal',
                            'negotiation' => 'Negosiasi',
                        ];
                    @endphp

                    {{-- Legend --}}
                    <div class="flex flex-wrap gap-3 text-xs">
                        @foreach($stages as $s)
                        <span class="flex items-center gap-1">
                            <span class="w-3 h-3 rounded {{ $stageColors[$s] ?? 'bg-gray-300' }} inline-block"></span>
                            {{ $stageLabels[$s] ?? $s }}
                        </span>
                        @endforeach
                    </div>

                    @forelse($stageBreakdown as $row)
                    @php
                        $rowTotal = array_sum($row['totals']);
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700">{{ $row['name'] }}</span>
                            <span class="text-xs text-gray-500">{{ $rowTotal }} total</span>
                        </div>
                        <div class="flex h-5 rounded-full overflow-hidden bg-gray-100">
                            @foreach($stages as $s)
                            @if(isset($row['totals'][$s]) && $row['totals'][$s] > 0 && $rowTotal > 0)
                            <div class="{{ $stageColors[$s] ?? 'bg-gray-300' }} flex items-center justify-center text-white text-xs"
                                 style="width: {{ round(($row['totals'][$s] / $rowTotal) * 100) }}%"
                                 title="{{ $stageLabels[$s] ?? $s }}: {{ $row['totals'][$s] }}">
                                {{ $row['totals'][$s] }}
                            </div>
                            @endif
                            @endforeach
                            @if($rowTotal == 0)
                            <div class="flex-1 flex items-center justify-center text-xs text-gray-400">Belum ada</div>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-sm text-gray-400">Belum ada anggota tim.</p>
                    @endforelse
                </div>
            </div>

            {{-- KPI Achievement Table --}}
            <div id="widget-kpi-achievement" class="cc-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">KPI Tim - {{ now()->format('F Y') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Sales</th>
                                <th class="px-4 py-3 text-center">Won</th>
                                <th class="px-4 py-3 text-center">Win Rate</th>
                                <th class="px-4 py-3 text-right">Revenue</th>
                                <th class="px-4 py-3 text-center">KPI%</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($teamMembers as $member)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $member->name }}</td>
                                <td class="px-4 py-3 text-center text-green-600 font-semibold">{{ $member->won_count }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-xs px-2 py-0.5 rounded font-medium
                                        {{ $member->win_rate >= 50 ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ $member->win_rate }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format($member->won_revenue ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center gap-1 justify-center">
                                        <div class="w-14 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full {{ $member->kpi_pct >= 100 ? 'bg-green-500' : ($member->kpi_pct >= 60 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                                style="width: {{ min($member->kpi_pct, 100) }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ $member->kpi_pct }}%</span>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada anggota tim.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Sidebar: Approvals + Recent Activities --}}
        <div class="space-y-6">

            {{-- Pending Approvals (level-1) --}}
            <div id="widget-approval-q" class="cc-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-semibold text-gray-800">Approval Level-1</h3>
                    @if($pendingApprovals > 0)
                    <span class="bg-red-100 text-red-600 text-xs font-semibold px-2 py-0.5 rounded-full">
                        {{ $pendingApprovals }}
                    </span>
                    @endif
                </div>
                <div class="divide-y divide-gray-100">
                    @forelse($approvalQueue as $approval)
                    <div class="px-5 py-3">
                        <p class="text-sm font-medium text-gray-800">
                            {{ optional(optional($approval->opportunity)->client)->company_name ?? 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500">{{ $approval->discount_percent }}% diskon</p>
                        <a href="{{ route('approvals.show', $approval) }}"
                           class="text-xs text-blue-600 hover:underline">Review &rarr;</a>
                    </div>
                    @empty
                    <div class="px-5 py-6 text-center text-gray-400 text-sm">Tidak ada pending.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <a href="{{ route('approvals.index') }}" class="text-xs text-blue-600 hover:underline">Semua approval</a>
                </div>
            </div>

            {{-- Recent Team Activities --}}
            <div id="widget-recent-activities" class="cc-card rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-semibold text-gray-800">Aktivitas Terbaru Tim</h3>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
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
                    <div class="px-5 py-3">
                        <div class="flex items-start gap-2">
                            <span class="text-lg leading-none mt-0.5">
                                {{ $activityIcons[$activity->type] ?? '📌' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $activity->subject }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ optional($activity->sales)->name ?? '-' }}
                                    @if($activity->client) &bull; {{ optional($activity->client)->company_name }} @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($activity->activity_date)->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-6 text-center text-gray-400 text-sm">Belum ada aktivitas.</div>
                    @endforelse
                </div>
                <div class="px-5 py-3 border-t border-gray-100">
                    <a href="{{ route('activities.index') }}" class="text-xs text-blue-600 hover:underline">Semua aktivitas</a>
                </div>
            </div>

        </div>
    </div>



</div>

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
        chart: { type: 'area', height: 320, toolbar: { show: false }, background: 'transparent' },
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
