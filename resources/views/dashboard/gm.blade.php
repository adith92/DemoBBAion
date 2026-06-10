@extends('layouts.app')

@section('header_title', 'Command Center')

@push('styles')
<style>
.exec-summary-card {
    background: var(--cc-card);
    border: 1px solid var(--cc-border);
    border-radius: 12px;
    box-shadow: 0 10px 28px rgba(16,40,72,0.08);
    position: relative;
    overflow: hidden;
    transition: background var(--theme-speed) ease, border-color var(--theme-speed) ease;
}
.exec-summary-card::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(20,104,168,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.fleet-bar {
    height: 6px;
    border-radius: 3px;
    background: rgba(16,40,72,0.10);
    overflow: hidden;
}
.fleet-bar-fill {
    height: 100%;
    border-radius: 3px;
}
.rank-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(16,40,72,0.08);
}
.rank-row:last-child { border-bottom: none; }
.rank-num {
    width: 24px;
    height: 24px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 800;
    flex-shrink: 0;
}
.booking-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    border-radius: 10px;
    transition: background 0.15s;
}
.booking-row:hover { background: rgba(20,104,168,0.06); }
.approval-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 0;
    border-bottom: 1px solid rgba(16,40,72,0.08);
}
.approval-item:last-child { border-bottom: none; }
.priority-high { color: #b94a48; font-size: 10px; font-weight: 700; }
.priority-med { color: #a17412; font-size: 10px; font-weight: 700; }
.dashboard-link { color: inherit; text-decoration: none; }
.dashboard-link:hover { color: #1468a8 !important; text-decoration: underline; text-underline-offset: 3px; }
.ai-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 10px;
    margin: 14px 0 16px;
}
.ai-metric {
    border: 1px solid var(--cc-border);
    border-radius: 10px;
    padding: 10px;
    background: var(--cc-card);
    transition: background var(--theme-speed) ease, border-color var(--theme-speed) ease;
}
.ai-meter {
    height: 6px;
    border-radius: 999px;
    background: rgba(16,40,72,0.10);
    overflow: hidden;
    margin-top: 8px;
}
.ai-meter > span { display: block; height: 100%; border-radius: 999px; }
@media (max-width: 640px) { .ai-metric-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')

{{-- ===== COMMAND CENTER HEADER (outside grid) ===== --}}
<div class="flex flex-col md:flex-row md:items-start justify-between gap-4 mb-5">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-xl font-black tracking-tight" style="color: var(--cc-text);">{{ __('ui.bluebird_crm') }} <span style="color:#1468a8;">{{ __('ui.command_center') }}</span></h1>
        </div>
        <p class="text-xs" style="color: var(--cc-text-muted);">{{ __('ui.corporate_intel') }}</p>
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <a href="{{ route('dashboard') }}" class="badge-demo">{{ __('ui.live_demo') }}</a>
            <a href="{{ route('analytics.index') }}" class="badge-demo">{{ __('ui.june_2026') }}</a>
            <a href="{{ route('dashboard.gm') }}" class="badge-live flex items-center gap-1.5">
                <span class="pulse-dot" style="width:5px;height:5px;"></span>
                {{ __('ui.director_hq') }}
            </a>
            <a href="{{ route('analytics.index') }}" style="background:rgba(141,107,184,0.12);color:#72529a;border:1px solid rgba(141,107,184,0.2);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">{{ __('ui.api_ready') }}</a>
            <a href="{{ route('bookings.index') }}" style="background:rgba(215,167,47,0.14);color:#8c6814;border:1px solid rgba(215,167,47,0.26);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">{{ __('ui.render_deploy') }}</a>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route('analytics.index') }}" class="btn-secondary text-xs py-2 px-4">
            <span class="material-symbols-outlined text-[14px]">query_stats</span>
            {{ __('ui.reports') }}
        </a>
    </div>
</div>

<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- ===== KPI CARDS ROW ===== --}}
    <div class="grid-stack-item" gs-id="widget-kpi-row" gs-x="0" gs-y="0" gs-w="12" gs-h="3">
        <div class="grid-stack-item-content">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 h-full">

                {{-- KPI 1: Revenue --}}
                <a href="{{ route('analytics.index') }}" class="kpi-card kpi-cyan col-span-2 md:col-span-1 lg:col-span-1 block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(20,104,168,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="flex items-start justify-between mb-1.5">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(20,104,168,0.10);">
                            <span class="material-symbols-outlined text-[17px]" style="color:#1468a8;">payments</span>
                        </div>
                    </div>
                    <div class="text-xs text-cc-muted flex flex-col space-y-0.5">
                        <span>Booked: <strong class="text-cc font-extrabold">{{ \App\Helpers\FormatHelper::formatIDR($totalMonthlyBooked) }}</strong></span>
                        <span>Paid: <strong class="text-emerald-500 font-extrabold">{{ \App\Helpers\FormatHelper::formatIDR($totalMonthlyPaid) }}</strong></span>
                    </div>
                    <div class="text-[9px] font-bold uppercase tracking-wider mt-1 text-cc-muted">Target vs Realisasi</div>
                    <canvas id="spark-revenue" style="position:absolute;bottom:4px;right:4px;width:54px;height:18px;opacity:0.65;"></canvas>
                </a>

                {{-- KPI 2: Bookings --}}
                <a href="{{ route('bookings.index') }}" class="kpi-card kpi-blue block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(59,130,246,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.1);">
                            <span class="material-symbols-outlined text-[17px]" style="color:#60a5fa;">route</span>
                        </div>
                        <span class="signal-up">{{ $bookingsSignal ?? '▲ 32' }}</span>
                    </div>
                    <div class="text-lg font-black leading-tight text-cc">{{ $activeBookings ?? 248 }}</div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide mt-1 text-cc-muted">{{ __('ui.active_bookings') }}</div>
                    <canvas id="spark-bookings" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
                </a>

                {{-- KPI 3: Fleet --}}
                <a href="{{ route('fleet.index') }}" class="kpi-card kpi-emerald block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(16,185,129,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.1);">
                            <span class="material-symbols-outlined text-[17px]" style="color:#34d399;">local_shipping</span>
                        </div>
                        <span class="signal-up">{{ __('ui.healthy') }}</span>
                    </div>
                    <div class="text-lg font-black leading-tight text-cc">{{ $utilizationRate ?? 72 }}%</div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide mt-1 text-cc-muted">{{ __('ui.fleet_utilization') }}</div>
                    <canvas id="spark-fleet" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
                </a>

                {{-- KPI 4: Clients --}}
                <a href="{{ route('clients.index') }}" class="kpi-card kpi-purple block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(139,92,246,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(139,92,246,0.1);">
                            <span class="material-symbols-outlined text-[17px]" style="color:#a78bfa;">corporate_fare</span>
                        </div>
                        <span class="signal-up">{{ $clientsSignal ?? '▲ 12' }}</span>
                    </div>
                    <div class="text-lg font-black leading-tight text-cc">{{ $activeClients }}</div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide mt-1 text-cc-muted">{{ __('ui.corp_clients') }}</div>
                    <canvas id="spark-clients" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
                </a>

                {{-- KPI 5: Outstanding --}}
                <a href="{{ route('finance.index') }}" class="kpi-card kpi-gold block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(245,158,11,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <div class="flex items-start justify-between mb-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.1);">
                            <span class="material-symbols-outlined text-[17px]" style="color:#fbbf24;">receipt_long</span>
                        </div>
                        <span class="signal-warn">{{ __('ui.attention') }}</span>
                    </div>
                    <div class="text-lg font-black leading-tight text-cc">{{ \App\Helpers\FormatHelper::formatIDR($outstandingInvoices) }}</div>
                    <div class="text-[10px] font-semibold uppercase tracking-wide mt-1 text-cc-muted">{{ __('ui.outstanding_inv') }}</div>
                    <canvas id="spark-invoice" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
                </a>

            </div>
        </div>
    </div>

    {{-- ===== QUICK SHORTCUTS ===== --}}
    <div class="grid-stack-item" gs-id="widget-quick-shortcuts" gs-x="0" gs-y="3" gs-w="12" gs-h="3">
        <div class="grid-stack-item-content">
            <div class="cc-card p-4 h-full overflow-auto">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold uppercase tracking-widest text-cc-muted">
                        <span class="material-symbols-outlined text-[13px] align-middle mr-1" style="color:#0066ff;">bolt</span>
                        {{ __('ui.quick_shortcuts') }}
                    </h2>
                    <span class="text-[10px] text-cc-muted">{{ __('ui.quick_shortcuts_desc') }}</span>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-2">
                    @php
                    $shortcuts = [
                        ['icon'=>'groups',         'label'=>__('ui.clients'),       'route'=>'clients.index',       'color'=>'#3385ff', 'bg'=>'rgba(0,82,204,0.12)'],
                        ['icon'=>'local_shipping', 'label'=>__('ui.sales_pipeline'), 'route'=>'pipeline.index',      'color'=>'#60a5fa', 'bg'=>'rgba(96,165,250,0.1)'],
                        ['icon'=>'book_online',    'label'=>__('ui.bookings'),      'route'=>'bookings.index',      'color'=>'#34d399', 'bg'=>'rgba(52,211,153,0.1)'],
                        ['icon'=>'directions_bus', 'label'=>__('ui.fleet'),         'route'=>'fleet.index',         'color'=>'#fbbf24', 'bg'=>'rgba(251,191,36,0.1)'],
                        ['icon'=>'build',          'label'=>__('ui.maintenance'),   'route'=>'maintenance.index',   'color'=>'#f97316', 'bg'=>'rgba(249,115,22,0.1)'],
                        ['icon'=>'receipt_long',   'label'=>__('ui.finance'),       'route'=>'finance.index',       'color'=>'#a78bfa', 'bg'=>'rgba(167,139,250,0.1)'],
                        ['icon'=>'subscriptions',  'label'=>__('ui.subscriptions'), 'route'=>'subscriptions.index', 'color'=>'#38bdf8', 'bg'=>'rgba(56,189,248,0.1)'],
                        ['icon'=>'query_stats',    'label'=>__('ui.analytics'),     'route'=>'analytics.index',     'color'=>'#1468a8', 'bg'=>'rgba(20,104,168,0.08)'],
                        ['icon'=>'inventory_2',    'label'=>__('ui.products'),      'route'=>'products.index',      'color'=>'#e879f9', 'bg'=>'rgba(232,121,249,0.1)'],
                        ['icon'=>'bar_chart',      'label'=>__('ui.kpi'),           'route'=>'kpi.index',           'color'=>'#fde047', 'bg'=>'rgba(253,224,71,0.1)'],
                        ['icon'=>'event_note',     'label'=>__('ui.activities'),    'route'=>'activities.index',    'color'=>'#94a3b8', 'bg'=>'rgba(148,163,184,0.08)'],
                        ['icon'=>'description',    'label'=>__('ui.opportunities'), 'route'=>'opportunities.index', 'color'=>'#7dd3fc', 'bg'=>'rgba(125,211,252,0.1)'],
                        ['icon'=>'dashboard',      'label'=>__('ui.gm_view'),       'route'=>'dashboard.gm',        'color'=>'#c084fc', 'bg'=>'rgba(192,132,252,0.1)'],
                    ];
                    @endphp

                    @foreach($shortcuts as $s)
                    <a href="{{ route($s['route']) }}"
                       class="group flex flex-col items-center gap-2 p-3 rounded-xl transition-all duration-150 hover:scale-105 active:scale-95"
                       style="background:{{ $s['bg'] }}; border:1px solid {{ $s['color'] }}22;">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center transition-all group-hover:shadow-lg"
                             style="background:{{ $s['bg'] }}; border:1px solid {{ $s['color'] }}44;">
                            <span class="material-symbols-outlined text-[18px]" style="color:{{ $s['color'] }};">{{ $s['icon'] }}</span>
                        </div>
                        <span class="text-[10px] font-semibold text-center leading-tight" style="color:#94a3b8;">{{ $s['label'] }}</span>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Executive Summary (2/3 width) ===== --}}
    <div class="grid-stack-item" gs-id="widget-exec-summary" gs-x="0" gs-y="6" gs-w="8" gs-h="8">
        <div class="grid-stack-item-content">
            <div class="exec-summary-card p-6 h-full overflow-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" style="color:#1468a8;">auto_awesome</span>
                        <a href="{{ route('analytics.index') }}" class="text-xs font-bold uppercase tracking-widest dashboard-link" style="color:#1468a8;">{{ __('ui.executive_intelligence') }}</a>
                    </div>
                    <a href="{{ route('analytics.index') }}" style="background:rgba(20,104,168,0.08);color:#0f5f9f;border:1px solid rgba(20,104,168,0.18);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">{{ __('ui.ai_summary') }}</a>
                </div>
                <h3 class="text-base font-bold mb-3 leading-snug text-cc">
                    <a href="{{ route('analytics.index') }}" class="dashboard-link">{!! __('ui.performance_headline', ['value' => '<span style="color:#21785f;">18.4%</span>']) !!}</a>
                </h3>
                <p class="text-sm leading-relaxed mb-4 text-cc-muted">
                    {{ __('ui.summary_text') }}
                </p>
                <div class="ai-metric-grid">
                    <a href="{{ route('analytics.index') }}" class="ai-metric dashboard-link">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-cc-muted">Revenue Lift</div>
                        <div class="text-xl font-black mt-1 text-cc">18.4%</div>
                        <div class="ai-meter"><span style="width:82%;background:#2f9d7e;"></span></div>
                    </a>
                    <a href="{{ route('fleet.index') }}" class="ai-metric dashboard-link">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-cc-muted">Fleet Health</div>
                        <div class="text-xl font-black mt-1 text-cc">72%</div>
                        <div class="ai-meter"><span style="width:72%;background:#1468a8;"></span></div>
                    </a>
                    <a href="{{ route('finance.index') }}" class="ai-metric dashboard-link">
                        <div class="text-[10px] font-bold uppercase tracking-wide text-cc-muted">Invoice Risk</div>
                        <div class="text-xl font-black mt-1 text-cc">14</div>
                        <div class="ai-meter"><span style="width:58%;background:#d7a72f;"></span></div>
                    </a>
                </div>
                <div class="space-y-2">
                    <div class="text-[10px] font-bold uppercase tracking-widest mb-2 text-cc-muted">{{ __('ui.strategic_recommendations') }}</div>
                    @php
                    $recs = [
                        ['icon'=>'group','color'=>'#1468a8','route'=>'clients.index','text'=>__('ui.rec_clients')],
                        ['icon'=>'receipt_long','color'=>'#a17412','route'=>'finance.index','text'=>__('ui.rec_invoice')],
                        ['icon'=>'local_shipping','color'=>'#21785f','route'=>'fleet.index','text'=>__('ui.rec_fleet')],
                        ['icon'=>'leaderboard','color'=>'#1468a8','route'=>'kpi.index','text'=>__('ui.rec_sales')],
                    ];
                    @endphp
                    @foreach($recs as $r)
                    <div class="flex items-start gap-2.5">
                        <span class="material-symbols-outlined text-[14px] mt-0.5 flex-shrink-0" style="color:{{ $r['color'] }};">{{ $r['icon'] }}</span>
                        <a href="{{ route($r['route']) }}" class="text-xs dashboard-link text-cc-muted">{{ $r['text'] }}</a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Fleet League (1/3 width) ===== --}}
    <div class="grid-stack-item" gs-id="widget-fleet-league" gs-x="8" gs-y="6" gs-w="4" gs-h="8">
        <div class="grid-stack-item-content">
            <div class="cc-card p-5 h-full overflow-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" style="color:#f59e0b;">emoji_events</span>
                        <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.fleet_league') }}</span>
                    </div>
                    <a href="{{ route('fleet.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">{{ __('ui.view_all') }}</a>
                </div>
                @php
                $fleets = $fleetLeague ?? [
                    ['name'=>'Golden Bird','pct'=>92,'color'=>'#f59e0b','badge'=>__('ui.high_performer'),'badgeColor'=>'rgba(245,158,11,0.12)','badgeText'=>'#fbbf24'],
                    ['name'=>'Big Bird','pct'=>84,'color'=>'#10b981','badge'=>__('ui.stable'),'badgeColor'=>'rgba(16,185,129,0.12)','badgeText'=>'#34d399'],
                    ['name'=>'Cititrans','pct'=>78,'color'=>'#3b82f6','badge'=>__('ui.needs_growth'),'badgeColor'=>'rgba(59,130,246,0.12)','badgeText'=>'#60a5fa'],
                    ['name'=>'Exec. Transport','pct'=>73,'color'=>'#8b5cf6','badge'=>__('ui.under_review'),'badgeColor'=>'rgba(139,92,246,0.12)','badgeText'=>'#a78bfa'],
                ];
                @endphp
                <div class="space-y-4">
                    @foreach($fleets as $i => $f)
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="rank-num" style="background: var(--cc-th-bg); color: var(--cc-text-muted);">{{ $i+1 }}</div>
                                <a href="{{ route('fleet.index') }}" class="text-xs font-semibold dashboard-link text-cc">{{ $f['name'] }}</a>
                            </div>
                            <div class="flex items-center gap-2">
                                <span style="background:{{ $f['badgeColor'] }};color:{{ $f['badgeText'] }};font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;">{{ $f['badge'] }}</span>
                                <span class="text-xs font-bold text-cc">{{ $f['pct'] }}%</span>
                            </div>
                        </div>
                        <div class="fleet-bar">
                            <div class="fleet-bar-fill" style="width:{{ $f['pct'] }}%;background:{{ $f['color'] }};opacity:0.8;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Revenue Chart (2/3 width) ===== --}}
    <div class="grid-stack-item" gs-id="widget-revenue-chart" gs-x="0" gs-y="14" gs-w="8" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card p-5 h-full">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" style="color:#3b82f6;">bar_chart</span>
                        <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.weekly_revenue_movement') }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-semibold text-cc-muted">{{ __('ui.peak_note') }}</span>
                        <a href="{{ route('analytics.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">Detail →</a>
                    </div>
                </div>
                <canvas id="revenueChart" height="180"></canvas>
            </div>
        </div>
    </div>

    {{-- ===== Sales Ranking / Performance (1/3 width) ===== --}}
    <div class="grid-stack-item" gs-id="widget-sales-ranking" gs-x="8" gs-y="14" gs-w="4" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card p-5 h-full overflow-auto flex flex-col" x-data="gmPerformanceWidget()" x-init="init()">
                
                {{-- Header --}}
                <div class="flex items-center justify-between mb-4 shrink-0">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-500">military_tech</span>
                        <span class="text-xs font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Sales Performance</span>
                    </div>
                    <a href="{{ route('kpi.index') }}" class="text-[10px] font-semibold text-blue-500 hover:underline">KPI Detail →</a>
                </div>

                {{-- Widget Content (Scrollable) --}}
                <div class="space-y-3 flex-grow overflow-y-auto pr-1 custom-scrollbar">
                    <template x-for="(manager, mIdx) in managerLeaderboard" :key="manager.user.id">
                        <div class="rounded-xl border border-white/5 bg-white/5 p-3 space-y-2.5 transition duration-200">
                            
                            {{-- Manager Row --}}
                            <div class="flex items-center justify-between cursor-pointer" @click="selectedManagerId = (selectedManagerId === manager.user.id ? null : manager.user.id)">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-6 h-6 rounded-lg font-bold text-xs flex items-center justify-center border border-white/5" 
                                          :class="mIdx === 0 ? 'bg-amber-500/20 text-amber-300' : (mIdx === 1 ? 'bg-slate-400/20 text-slate-300' : 'bg-slate-800 text-slate-400')"
                                          x-text="mIdx + 1"></span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-[var(--cc-text)] truncate" x-text="manager.user.name"></p>
                                        <p class="text-[9px] text-[var(--cc-text-muted)] font-semibold uppercase tracking-wider mt-0.5" 
                                           x-text="manager.reps.length + ' Reps'"></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="text-right">
                                        <p class="text-xs font-bold text-emerald-400 font-mono" x-text="formatIDR(manager.revenue)"></p>
                                        <p class="text-[9px] text-indigo-400 font-bold" x-text="getPercentage(manager.revenue, manager.target) + '% Target'"></p>
                                    </div>
                                    <span class="material-symbols-outlined text-[16px] text-slate-400 transition-transform duration-200"
                                          :class="{'rotate-180': selectedManagerId === manager.user.id}">expand_more</span>
                                </div>
                            </div>

                            {{-- Subordinates (Reps) List --}}
                            <div x-show="selectedManagerId === manager.user.id" x-collapse class="pl-8 space-y-2 border-l border-white/10 mt-1">
                                <template x-for="(rep, rIdx) in manager.reps" :key="rep.user.id">
                                    <div class="flex items-center justify-between text-xs py-1 hover:bg-white/5 rounded-lg px-2 -mx-2 transition-colors">
                                        <div class="min-w-0 flex items-center gap-2">
                                            <span class="text-[10px] text-slate-400 font-bold" x-text="(rIdx+1) + '.'"></span>
                                            <span class="font-medium text-[var(--cc-text-muted)] truncate" x-text="rep.user.name"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-mono font-semibold text-[var(--cc-text)]" x-text="formatIDR(rep.revenue)"></span>
                                            
                                            {{-- Performance badge --}}
                                            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full border"
                                                  :class="getPercentage(rep.revenue, rep.target) >= 100 ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 
                                                          (getPercentage(rep.revenue, rep.target) >= 50 ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 
                                                                                                          'bg-rose-500/10 text-rose-400 border-rose-500/20')"
                                                  x-text="getPercentage(rep.revenue, rep.target) + '%'"></span>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="manager.reps.length === 0">
                                    <p class="text-[10px] text-slate-500 italic py-1">No sales reps under this manager</p>
                                </template>
                            </div>

                        </div>
                    </template>
                    <template x-if="managerLeaderboard.length === 0">
                        <div class="text-center text-xs text-slate-500 py-6">No performance data available</div>
                    </template>
                </div>

            </div>
        </div>
    </div>

    {{-- ===== Recent Bookings (Full width) ===== --}}
    <div class="grid-stack-item" gs-id="widget-recent-books" gs-x="0" gs-y="19" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card p-5 h-full overflow-auto">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]" style="color:#10b981;">route</span>
                        <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.recent_bookings') }}</span>
                    </div>
                    <a href="{{ route('bookings.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">{{ __('ui.view_all') }}</a>
                </div>
                @php
                $bookings = $recentBookings ?? [
                    ['id'=>'GB-2026-0612','client'=>'Astra International','fleet'=>'Golden Bird','status'=>'confirmed','statusClass'=>'status-confirmed'],
                    ['id'=>'BB-2026-0441','client'=>'Telkom Indonesia','fleet'=>'Big Bird','status'=>'On Trip','statusClass'=>'status-completed'],
                    ['id'=>'CT-2026-0192','client'=>'Bank Mandiri','fleet'=>'Cititrans','status'=>'pending','statusClass'=>'status-pending'],
                    ['id'=>'EX-2026-0088','client'=>'Pertamina','fleet'=>'Executive','status'=>'completed','statusClass'=>'status-completed'],
                ];
                @endphp
                <div class="space-y-1">
                    @foreach($bookings as $b)
                    <div class="booking-row">
                        <div class="flex-grow min-w-0">
                            <a href="{{ route('bookings.index') }}" class="text-xs font-bold font-mono dashboard-link">{{ $b['id'] }}</a>
                            <div class="text-[10px] text-cc-muted"><a href="{{ route('clients.index') }}" class="dashboard-link">{{ $b['client'] }}</a> - <a href="{{ route('fleet.index') }}" class="dashboard-link">{{ $b['fleet'] }}</a></div>
                        </div>
                        <span class="status-badge {{ $b['statusClass'] }} flex-shrink-0">{{ $b['status'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Charts Section ===== --}}
    <div class="grid-stack-item" gs-id="widget-charts-section" gs-x="0" gs-y="24" gs-w="12" gs-h="6">
        <div class="grid-stack-item-content">
            <div class="h-full overflow-auto">
                @include('dashboard.charts')
            </div>
        </div>
    </div>

</x-dashboard-grid>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: @json($weeklyLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']),
            datasets: [{
                label: 'Revenue (Jt)',
                data: @json($weeklyRevenue ?? [320, 410, 285, 520, 475, 240, 190]),
                backgroundColor: [
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                    'rgba(20,104,168,0.62)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.3)',
                    'rgba(59,130,246,0.3)',
                ],
                borderColor: [
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.8)',
                    'rgba(20,104,168,0.88)',
                    'rgba(59,130,246,0.8)',
                    'rgba(59,130,246,0.5)',
                    'rgba(59,130,246,0.5)',
                ],
                borderWidth: 1,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(15,15,28,0.95)',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    titleColor: '#94a3b8',
                    bodyColor: '#1468a8',
                    bodyFont: { weight: 'bold', size: 14 },
                    callbacks: {
                        label: ctx => `Rp ${ctx.raw} Jt`
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: { color: '#475569', font: { size: 11, weight: '600' } }
                },
                y: {
                    grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
                    ticks: {
                        color: '#475569', font: { size: 11 },
                        callback: v => `${v} Jt`
                    }
                }
            }
        }
    });

    // ── KPI Sparklines ──
    const sparks = [
        { id: 'spark-revenue',   data: @json($sparkRevenue ?? [210,240,285,310,295,340,380,395,420,440,460,484]), color: '#1468a8' },
        { id: 'spark-bookings',  data: @json($sparkBookings ?? [180,195,210,230,225,248,260,255,270,248,265,280]), color: '#60a5fa' },
        { id: 'spark-fleet',     data: @json($sparkFleet ?? [65,68,70,72,69,71,74,72,75,73,72,74]),            color: '#34d399' },
        { id: 'spark-clients',   data: @json($sparkClients ?? [100,104,108,110,112,115,116,118,120,122,125,128]), color: '#a78bfa' },
        { id: 'spark-invoice',   data: @json($sparkInvoice ?? [280,310,340,360,395,420,410,430,440,420,415,420]), color: '#fbbf24' },
    ];
    sparks.forEach(s => {
        if (window.CRM_Sparkline) CRM_Sparkline.render(s.id, s.data, s.color);
    });
});

function gmPerformanceWidget() {
    return {
        users: @json($users),
        deals: @json($deals),
        targets: @json($targets),
        
        managerLeaderboard: [],
        selectedManagerId: null,

        init() {
            this.calculatePerformance();
        },

        calculatePerformance() {
            // Managers
            let managers = {};
            this.users.filter(u => u.role === 'Manager').forEach(u => {
                managers[u.id] = { user: u, revenue: 0, target: 0, reps: [] };
            });

            // Sales reps
            let reps = {};
            this.users.filter(u => u.role === 'Sales').forEach(u => {
                reps[u.id] = { user: u, revenue: 0, target: 0, managerId: u.managerId };
            });

            // Calculate revenue from Won deals
            this.deals.forEach(d => {
                if (d.stage === 'Won' && reps[d.salesId]) {
                    reps[d.salesId].revenue += (d.actualValue || 0);
                }
            });

            // Calculate target
            this.targets.forEach(t => {
                if (reps[t.userId]) {
                    let targetSum = Object.values(t.productTargets).reduce((a, b) => a + b, 0);
                    reps[t.userId].target += targetSum;
                }
            });

            // Group reps under managers and aggregate manager revenue/target
            Object.values(reps).forEach(rep => {
                if (rep.managerId && managers[rep.managerId]) {
                    managers[rep.managerId].reps.push(rep);
                    managers[rep.managerId].revenue += rep.revenue;
                    managers[rep.managerId].target += rep.target;
                }
            });

            // Sort reps inside managers
            Object.values(managers).forEach(m => {
                m.reps.sort((a, b) => b.revenue - a.revenue);
            });

            // Sort managers by team revenue
            this.managerLeaderboard = Object.values(managers)
                .sort((a, b) => b.revenue - a.revenue);
        },

        formatIDR(val) {
            if (!val) return 'Rp 0';
            if (val >= 1000000000) {
                return 'Rp ' + (val / 1000000000).toFixed(1) + 'M';
            }
            if (val >= 1000000) {
                return 'Rp ' + (val / 1000000).toFixed(0) + 'Jt';
            }
            return 'Rp ' + parseInt(val).toLocaleString('id-ID');
        },

        getPercentage(actual, target) {
            if (!target) return 0;
            return Math.round((actual / target) * 100);
        }
    }
}
</script>
@endpush
