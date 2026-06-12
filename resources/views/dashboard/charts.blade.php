{{--
    DASHBOARD CHARTS PARTIAL — included in all dashboard views
    Charts: 7-day timeline, pipeline donut, sales leaderboard bar, KPI sparklines
    Requires Chart.js (loaded via app.js)
--}}

{{-- ════ SECTION: 7-Day Revenue + Deals Timeline ════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

    {{-- Main 7-day area chart (2/3 width) --}}
    <div class="chart-card lg:col-span-2">
        <div class="flex items-center justify-between mb-1">
            <div>
                <span class="chart-title">📈 Revenue & Deals
                    <span class="chart-sub">7 hari terakhir</span>
                </span>
            </div>
            <div class="flex items-center gap-3 text-[11px]" style="color:var(--cc-text-muted)">
                <span><span style="color:var(--color-primary)">●</span> Revenue</span>
                <span><span style="color:#a78bfa">●</span> Deals closed</span>
            </div>
        </div>
        <div style="height:180px;position:relative">
            <canvas id="chart-7day"></canvas>
        </div>
        {{-- Day labels with deal counts --}}
        <div class="flex justify-between mt-2 px-1">
            @php
            $days7 = $days7 ?? [
                ['day'=>'Sen','date'=>'1 Jun','deals'=>3,'won'=>1],
                ['day'=>'Sel','date'=>'2 Jun','deals'=>5,'won'=>2],
                ['day'=>'Rab','date'=>'3 Jun','deals'=>2,'won'=>0],
                ['day'=>'Kam','date'=>'4 Jun','deals'=>8,'won'=>3],
                ['day'=>'Jum','date'=>'5 Jun','deals'=>6,'won'=>2],
                ['day'=>'Sab','date'=>'6 Jun','deals'=>4,'won'=>1],
                ['day'=>'Ming','date'=>'7 Jun','deals'=>1,'won'=>0,'today'=>true],
            ];
            @endphp
            @foreach($days7 as $d)
            <div class="timeline-day {{ isset($d['today']) ? 'font-black' : '' }}"
                 style="{{ isset($d['today']) ? 'color:var(--cc-accent)' : '' }}">
                <span>{{ $d['day'] }}</span>
                @if($d['deals'] > 0)
                <span class="text-[9px] px-1.5 py-0.5 rounded-full"
                      style="background:var(--cc-accent-dim);color:var(--cc-accent)">
                    {{ $d['deals'] }}
                </span>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- Pipeline Stage Donut (1/3) --}}
    <div class="chart-card">
        <div class="chart-title">🗂️ Pipeline Distribution</div>
        <div style="height:150px;position:relative">
            <canvas id="chart-donut"></canvas>
        </div>
        {{-- Legend --}}
        <div class="mt-3 space-y-1.5">
            @php
            $stages = $pipelineDistribution ?? [
                ['label'=>'Prospecting','pct'=>35,'color'=>'#6366f1','count'=>14],
                ['label'=>'Proposal',   'pct'=>25,'color'=>'#f59e0b','count'=>10],
                ['label'=>'Negotiation','pct'=>20,'color'=>'#f97316','count'=>8],
                ['label'=>'Won',        'pct'=>15,'color'=>'#10b981','count'=>6],
                ['label'=>'Lost',       'pct'=>5, 'color'=>'#ef4444','count'=>2],
            ];
            @endphp
            @foreach($stages as $s)
            <div class="flex items-center gap-2 text-[11px]">
                <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:{{ $s['color'] }}"></span>
                <span style="color:var(--cc-text);flex:1">{{ $s['label'] }}</span>
                <span style="color:var(--cc-text-muted)">{{ $s['count'] }}</span>
                <span class="font-bold" style="color:var(--cc-text-muted);width:30px;text-align:right">{{ $s['pct'] }}%</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ════ SECTION: Sales Leaderboard + Activity Heatmap ════ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">

    {{-- Sales Leaderboard Bar Chart --}}
    <div class="chart-card">
        <div class="flex items-center justify-between mb-3">
            <span class="chart-title">🏅 Sales Leaderboard<span class="chart-sub">bulan ini</span></span>
            <a href="{{ route('kpi.index') }}" class="text-[11px] font-semibold" style="color:var(--cc-accent)">Full KPI →</a>
        </div>
        <div style="height:160px;position:relative">
            <canvas id="chart-leaders"></canvas>
        </div>
    </div>

    {{-- Activity Types Doughnut --}}
    <div class="chart-card">
        <div class="chart-title">📋 Activity Breakdown<span class="chart-sub">7 hari terakhir</span></div>
        <div class="flex items-center gap-4">
            <div style="height:160px;width:160px;flex-shrink:0;position:relative">
                <canvas id="chart-activity"></canvas>
            </div>
            <div class="space-y-2 flex-1">
                @php
                $actTypes = $actTypes ?? [
                    ['label'=>'📞 Call',    'count'=>24,'color'=>'#3b82f6'],
                    ['label'=>'📧 Email',   'count'=>18,'color'=>'#8b5cf6'],
                    ['label'=>'🤝 Meeting', 'count'=>12,'color'=>'#10b981'],
                    ['label'=>'📄 Proposal','count'=>8, 'color'=>'#f59e0b'],
                    ['label'=>'🔄 Follow-up','count'=>15,'color'=>'#ec4899'],
                ];
                @endphp
                @foreach($actTypes as $a)
                <div>
                    <div class="flex items-center justify-between text-[11px] mb-0.5">
                        <span style="color:var(--cc-text)">{{ $a['label'] }}</span>
                        <span class="font-bold" style="color:{{ $a['color'] }}">{{ $a['count'] }}</span>
                    </div>
                    <div class="progress-bar-bg">
                        <div class="progress-bar-fill"
                             style="width:{{ round($a['count'] > 0 ? ($a['count']/max(array_column($actTypes, 'count'))*100) : 0) }}%;background:{{ $a['color'] }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {

    /* ── Chart.js Global Defaults (dark-mode-aware) ── */
    const isDark = () => !document.documentElement.classList.contains('light');
    const textColor  = () => isDark() ? '#64748b' : '#7070a0';
    const gridColor  = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tooltipBg  = () => isDark() ? 'rgba(15,15,28,0.96)' : 'rgba(255,255,255,0.96)';
    const tooltipTxt = () => isDark() ? '#e2e8f0' : '#1e1e3a';

    const sharedTooltip = () => ({
        backgroundColor: tooltipBg(),
        borderColor: 'rgba(0,229,255,0.2)',
        borderWidth: 1,
        titleColor: textColor(),
        bodyColor: tooltipTxt(),
        bodyFont: { size: 13, weight: '700' },
        padding: 10,
        cornerRadius: 8,
    });

    /* ── 1. 7-Day Revenue + Deals Timeline ── */
    const ctx7 = document.getElementById('chart-7day');
    if (ctx7) {
        new Chart(ctx7, {
            type: 'bar',
            data: {
                labels: {!! json_encode($days7Labels ?? ['Sen 1/6','Sel 2/6','Rab 3/6','Kam 4/6','Jum 5/6','Sab 6/6','Min 7/6']) !!},
                datasets: [
                    {
                        type: 'line',
                        label: 'Revenue (Jt Rp)',
                        data: {!! json_encode($days7Revenue ?? [320, 415, 285, 524, 478, 245, 190]) !!},
                        borderColor: 'var(--color-primary)',
                        backgroundColor: 'rgba(0,229,255,0.07)',
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 4,
                        pointBackgroundColor: 'var(--color-primary)',
                        pointBorderColor: 'rgba(0,229,255,0.3)',
                        pointBorderWidth: 2,
                        pointHoverRadius: 6,
                        yAxisID: 'y',
                        order: 1,
                    },
                    {
                        type: 'bar',
                        label: 'Deals Closed',
                        data: {!! json_encode($days7Deals ?? [3, 5, 2, 8, 6, 4, 1]) !!},
                        backgroundColor: 'rgba(167,139,250,0.35)',
                        borderColor: 'rgba(167,139,250,0.7)',
                        borderWidth: 1,
                        borderRadius: 5,
                        borderSkipped: false,
                        yAxisID: 'y2',
                        order: 2,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                resizeDelay: 100,
                interaction: { mode: 'index', intersect: false },
                animation: { duration: 400, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...sharedTooltip(),
                        callbacks: {
                            label: ctx => ctx.dataset.label === 'Revenue (Jt Rp)'
                                ? `💰 Rp ${ctx.raw} Jt`
                                : `🏆 ${ctx.raw} deals`,
                        }
                    }
                },
                scales: {
                    x: { grid: { color: gridColor(), drawBorder: false }, ticks: { color: textColor(), font: { size: 11, weight: '600' } } },
                    y:  { position: 'left',  grid: { color: gridColor(), drawBorder: false }, ticks: { color: textColor(), font: { size: 10 }, callback: v => `${v}Jt` } },
                    y2: { position: 'right', grid: { display: false }, ticks: { color: '#a78bfa', font: { size: 10 }, stepSize: 1 } },
                }
            }
        });
    }

    /* ── 2. Pipeline Donut ── */
    const ctxDonut = document.getElementById('chart-donut');
    if (ctxDonut) {
        new Chart(ctxDonut, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($pipelineLabels ?? ['Prospecting','Proposal','Negotiation','Won','Lost']) !!},
                datasets: [{
                    data: {!! json_encode($pipelinePct ?? [35, 25, 20, 15, 5]) !!},
                    backgroundColor: {!! json_encode($pipelineColors ?? ['#6366f1','#f59e0b','#f97316','#10b981','#ef4444']) !!},
                    borderColor: isDark() ? '#09090f' : '#f0f0fa',
                    borderWidth: 3,
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                resizeDelay: 100,
                animation: { duration: 400, easing: 'easeOutQuart' },
                cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...sharedTooltip(),
                        callbacks: { label: ctx => `${ctx.label}: ${ctx.raw}%` }
                    }
                }
            }
        });
    }

    /* ── 3. Sales Leaderboard Horizontal Bar ── */
    const ctxLeaders = document.getElementById('chart-leaders');
    if (ctxLeaders) {
        new Chart(ctxLeaders, {
            type: 'bar',
            data: {
                labels: {!! json_encode($salesLeaderboardLabels ?? ['Andi P.','Sari D.','Reza F.','Hendra W.','Budi H.']) !!},
                datasets: [
                    {
                        label: 'Revenue (Jt)',
                        data: {!! json_encode($salesLeaderboardData ?? [740, 615, 480, 355, 290]) !!},
                        backgroundColor: {!! json_encode($salesLeaderboardColors ?? ['rgba(245,158,11,0.7)','rgba(148,163,184,0.6)','rgba(180,83,9,0.6)','rgba(99,102,241,0.55)','rgba(99,102,241,0.4)']) !!},
                        borderColor: {!! json_encode($salesLeaderboardColors ?? ['rgba(245,158,11,0.9)','rgba(148,163,184,0.8)','rgba(180,83,9,0.8)','rgba(99,102,241,0.75)','rgba(99,102,241,0.6)']) !!},
                        borderWidth: 1,
                        borderRadius: 5,
                        borderSkipped: false,
                    },
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true, maintainAspectRatio: false,
                resizeDelay: 100,
                animation: { duration: 400, easing: 'easeOutQuart' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        ...sharedTooltip(),
                        callbacks: { label: ctx => `💰 Rp ${ctx.raw} Jt` }
                    }
                },
                scales: {
                    x: { grid: { color: gridColor(), drawBorder: false }, ticks: { color: textColor(), font: { size: 10 }, callback: v => `${v}Jt` } },
                    y: { grid: { display: false }, ticks: { color: textColor(), font: { size: 12, weight: '700' } } }
                }
            }
        });
    }

    /* ── 4. Activity Types Doughnut ── */
    const ctxAct = document.getElementById('chart-activity');
    if (ctxAct) {
        new Chart(ctxAct, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($activityChartLabels ?? ['Call','Email','Meeting','Proposal','Follow-up']) !!},
                datasets: [{
                    data: {!! json_encode($activityChartData ?? [24, 18, 12, 8, 15]) !!},
                    backgroundColor: ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ec4899'],
                    borderColor: isDark() ? '#09090f' : '#f0f0fa',
                    borderWidth: 3,
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                resizeDelay: 100,
                animation: { duration: 400, easing: 'easeOutQuart' },
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: { ...sharedTooltip() }
                }
            }
        });
    }

    /* ── Re-render on theme change ── */
    const observer = new MutationObserver(() => {
        Chart.instances && Object.values(Chart.instances).forEach(c => c.update());
    });
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
