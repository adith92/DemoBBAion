@extends('layouts.app')

@section('header_title', 'Analytics Overview')

@section('content')
<div class="space-y-6">

    @include('components.analytics-nav')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Pipeline Funnel Chart --}}
        <div class="lg:col-span-2 cc-card rounded-xl border border-cc shadow-sm p-5">
            <h3 class="font-semibold text-cc mb-4">Pipeline Funnel (Opportunity Aktif)</h3>
            @php
                $stageOrder  = ['prospecting', 'proposal', 'negotiation'];
                $stageLabels = ['Prospecting', 'Proposal', 'Negosiasi'];
                $stageCounts = [];
                $stageValues = [];
                $maxCount = 1;
                foreach ($stageOrder as $s) {
                    $stageCounts[] = $pipelineByStage[$s]->count ?? 0;
                    $stageValues[] = $pipelineByStage[$s]->total_value ?? 0;
                    $maxCount = max($maxCount, $pipelineByStage[$s]->count ?? 0);
                }
            @endphp
            <div class="space-y-3">
                @foreach($stageOrder as $i => $stage)
                @php
                    $cnt = $stageCounts[$i];
                    $val = $stageValues[$i];
                    $pct = $maxCount > 0 ? round(($cnt / $maxCount) * 100) : 0;
                    $colors = ['bg-blue-600', 'bg-indigo-500', 'bg-purple-500'];
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1 text-sm">
                        <span class="font-medium text-cc">{{ $stageLabels[$i] }}</span>
                        <span class="text-cc-muted">{{ $cnt }} deal &bull; Rp {{ number_format($val, 0, ',', '.') }}</span>
                    </div>
                    <div class="h-8 bg-cc-card rounded-lg overflow-hidden">
                        <div class="{{ $colors[$i] }} h-full rounded-lg flex items-center px-3 text-gray-900 text-xs font-semibold transition-all"
                             style="width: {{ max($pct, 4) }}%">
                            {{ $pct }}%
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-cc flex gap-6 text-sm text-cc-muted">
                @php
                    $wonData  = $pipelineByStage['won'] ?? null;
                    $lostData = $pipelineByStage['lost'] ?? null;
                    $wonCnt  = $wonData->count ?? 0;
                    $lostCnt = $lostData->count ?? 0;
                @endphp
                <div>
                    <span class="font-semibold text-green-600">{{ $wonCnt }}</span> Won
                </div>
                <div>
                    <span class="font-semibold text-red-500">{{ $lostCnt }}</span> Lost
                </div>
                <div>
                    Win rate: <span class="font-semibold">
                        {{ ($wonCnt + $lostCnt) > 0 ? round($wonCnt / ($wonCnt + $lostCnt) * 100, 1) : 0 }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- Cross-sell Widget --}}
        <div class="cc-card rounded-xl border border-cc shadow-sm p-5">
            <h3 class="font-semibold text-cc mb-4">Cross-sell Segments</h3>
            <div class="space-y-3">
                @php
                    $cs = $crossSellCount;
                    $segments = [
                        ['label' => 'Short Term Only',     'key' => 'short_term_only',  'color' => 'bg-blue-900/40 text-blue-700'],
                        ['label' => 'Long Term Only',      'key' => 'long_term_only',   'color' => 'bg-green-900/40 text-green-700'],
                        ['label' => 'E-Voucher Only',      'key' => 'evoucher_only',    'color' => 'bg-yellow-900/30 text-yellow-700'],
                        ['label' => 'Short + Long Term',   'key' => 'short_and_long',   'color' => 'bg-indigo-900/40 text-indigo-700'],
                        ['label' => 'Short + E-Voucher',   'key' => 'short_and_ev',     'color' => 'bg-purple-900/40 text-purple-700'],
                        ['label' => 'Long + E-Voucher',    'key' => 'long_and_ev',      'color' => 'bg-teal-900/40 text-teal-700'],
                        ['label' => 'Semua Kategori',      'key' => 'all_three',         'color' => 'bg-orange-900/30 text-orange-700'],
                        ['label' => 'Belum Ada Produk',    'key' => 'none',              'color' => 'bg-cc-card text-cc-muted'],
                    ];
                @endphp
                @foreach($segments as $seg)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-cc">{{ $seg['label'] }}</span>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $seg['color'] }}">
                        {{ $cs[$seg['key']] ?? 0 }}
                    </span>
                </div>
                @endforeach
            </div>
            <div class="mt-4">
                <a href="{{ route('analytics.crosssell') }}"
                   class="text-sm text-blue-600 hover:underline">Analisis lengkap &rarr;</a>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Revenue by Product Category Donut --}}
        <div class="cc-card rounded-xl border border-cc shadow-sm p-5">
            <h3 class="font-semibold text-cc mb-4">Revenue per Kategori Produk</h3>
            <div class="flex items-center gap-6">
                <div class="flex-shrink-0">
                    <canvas id="categoryDonut" width="180" height="180"></canvas>
                </div>
                <div id="categoryLegend" class="flex-1 space-y-2 text-sm"></div>
            </div>
        </div>

        {{-- Sales Performance Leaderboard --}}
        <div class="cc-card rounded-xl border border-cc shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-cc flex items-center justify-between">
                <h3 class="font-semibold text-cc">Sales Leaderboard</h3>
                <a href="{{ route('analytics.sales') }}" class="text-xs text-blue-600 hover:underline">Detail</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="cc-card text-xs text-cc-muted uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">#</th>
                            <th class="px-4 py-2 text-left">Sales</th>
                            <th class="px-4 py-2 text-center">Won</th>
                            <th class="px-4 py-2 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cc">
                        @forelse($topSales->take(8) as $idx => $s)
                        <tr class="hover:cc-card">
                            <td class="px-4 py-2 text-cc-muted font-mono">{{ $idx + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-cc">{{ $s->name }}</td>
                            <td class="px-4 py-2 text-center text-green-600 font-semibold">{{ $s->won_count }}</td>
                            <td class="px-4 py-2 text-right text-cc">Rp {{ number_format($s->won_revenue ?? 0, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-4 py-6 text-center text-cc-muted">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Activity Summary --}}
    <div class="cc-card rounded-xl border border-cc shadow-sm p-5">
        <h3 class="font-semibold text-cc mb-4">Ringkasan Aktivitas (30 hari terakhir)</h3>
        @php
            $activityLabels = [
                'meeting'   => 'Meeting',
                'call'      => 'Call',
                'visit'     => 'Visit',
                'follow_up' => 'Follow Up',
                'email'     => 'Email',
                'demo'      => 'Demo',
            ];
            $activityColors = [
                'meeting'   => '#3b82f6',
                'call'      => '#10b981',
                'visit'     => '#f59e0b',
                'follow_up' => '#8b5cf6',
                'email'     => '#6366f1',
                'demo'      => '#ec4899',
            ];
            $totalActivities = $activitySummary->sum('count');
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($activityLabels as $type => $label)
            @php $count = $activitySummary[$type]->count ?? 0; @endphp
            <div class="text-center p-3 cc-card rounded-lg">
                <p class="text-2xl font-bold text-cc">{{ $count }}</p>
                <p class="text-xs text-cc-muted mt-1">{{ $label }}</p>
            </div>
            @endforeach
        </div>
        <div class="mt-4 text-sm text-cc-muted">Total: <span class="font-semibold text-cc">{{ $totalActivities }}</span> aktivitas</div>
    </div>

    @include('analytics.charts')
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Revenue by category (via API - approximate with invoice data)
    // Placeholder donut with dummy breakdown if no API available
    @php $catJson = ['labels' => ['Short Term', 'Long Term', 'E-Voucher', 'Lainnya'], 'values' => [40, 35, 15, 10], 'colors' => ['#3b82f6', '#10b981', '#f59e0b', '#6b7280']]; @endphp
    const catData = @json($catJson);

    const ctx = document.getElementById('categoryDonut').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: catData.labels,
            datasets: [{
                data: catData.values,
                backgroundColor: catData.colors,
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: false,
            cutout: '65%',
            plugins: { legend: { display: false } }
        }
    });

    // Render legend
    const legend = document.getElementById('categoryLegend');
    catData.labels.forEach((label, i) => {
        legend.innerHTML += `
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:${catData.colors[i]}"></span>
                <span class="text-cc">${label}</span>
                <span class="ml-auto font-semibold text-cc">${catData.values[i]}%</span>
            </div>`;
    });
});
</script>
@endpush
