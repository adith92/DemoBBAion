@extends('layouts.app')

@section('header_title', 'Command Center')

@push('styles')
<style>
.exec-summary-card {
    background: #ffffff;
    border: 1px solid rgba(16,40,72,0.10);
    border-radius: 12px;
    box-shadow: 0 10px 28px rgba(16,40,72,0.08);
    position: relative;
    overflow: hidden;
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
    border: 1px solid rgba(16,40,72,0.10);
    border-radius: 10px;
    padding: 10px;
    background: linear-gradient(180deg, #ffffff 0%, #f7fafc 100%);
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
<div class="space-y-5">

    {{-- ===== COMMAND CENTER HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-black tracking-tight" style="color:#101828;">{{ __('ui.bluebird_crm') }} <span style="color:#1468a8;">{{ __('ui.command_center') }}</span></h1>
            </div>
            <p class="text-xs" style="color:#475569;">{{ __('ui.corporate_intel') }}</p>
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
            <a href="{{ route('approvals.index') }}" class="btn-primary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">fact_check</span>
                {{ __('ui.approve_queue') }}
            </a>
            <a href="{{ route('analytics.index') }}" class="btn-secondary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">query_stats</span>
                {{ __('ui.reports') }}
            </a>
        </div>
    </div>

    {{-- ===== KPI CARDS ROW ===== --}}
    <div id="widget-kpi-row" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

        {{-- KPI 1: Revenue --}}
        <a href="{{ route('analytics.index') }}" class="kpi-card kpi-cyan col-span-2 md:col-span-1 lg:col-span-1 block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(20,104,168,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(20,104,168,0.10);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#1468a8;">payments</span>
                </div>
                <span class="signal-up">▲ 18.4%</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">Rp 2,84 M</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.monthly_revenue') }}</div>
            <canvas id="spark-revenue" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </a>

        {{-- KPI 2: Bookings --}}
        <a href="{{ route('bookings.index') }}" class="kpi-card kpi-blue block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(59,130,246,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#60a5fa;">route</span>
                </div>
                <span class="signal-up">▲ 32</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $pendingDispatch ?? 248 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.active_bookings') }}</div>
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
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $availableVehicles ?? 72 }}%</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.fleet_utilization') }}</div>
            <canvas id="spark-fleet" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </a>

        {{-- KPI 4: Clients --}}
        <a href="{{ route('clients.index') }}" class="kpi-card kpi-purple block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(139,92,246,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(139,92,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#a78bfa;">corporate_fare</span>
                </div>
                <span class="signal-up">▲ 12</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">128</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.corp_clients') }}</div>
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
            <div class="text-lg font-black leading-tight" style="color:#101828;">Rp 420 Jt</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.outstanding_inv') }}</div>
            <canvas id="spark-invoice" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </a>

        {{-- KPI 6: Approval --}}
        <a href="{{ route('approvals.index') }}" class="kpi-card kpi-red block group" style="position:relative;overflow:hidden;transition:transform 0.12s,box-shadow 0.12s;" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(239,68,68,0.18)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
            <div class="flex items-start justify-between mb-2">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#f87171;">pending_actions</span>
                </div>
                <span class="signal-down">{{ __('ui.urgent') }}</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $pendingPO ?? 14 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">{{ __('ui.pending_approval') }}</div>
            <canvas id="spark-approvals" style="position:absolute;bottom:6px;right:6px;width:64px;height:22px;opacity:0.65;"></canvas>
        </a>

    </div>

    {{-- ===== QUICK SHORTCUTS ===== --}}
    <div id="widget-quick-shortcuts">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xs font-bold uppercase tracking-widest" style="color:#475569;">
                <span class="material-symbols-outlined text-[13px] align-middle mr-1" style="color:#0066ff;">bolt</span>
                {{ __('ui.quick_shortcuts') }}
            </h2>
            <span class="text-[10px]" style="color:#334155;">{{ __('ui.quick_shortcuts_desc') }}</span>
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
                ['icon'=>'redeem',         'label'=>__('ui.vouchers'),      'route'=>'vouchers.index',      'color'=>'#fb7185', 'bg'=>'rgba(251,113,133,0.1)'],
                ['icon'=>'query_stats',    'label'=>__('ui.analytics'),     'route'=>'analytics.index',     'color'=>'#1468a8', 'bg'=>'rgba(20,104,168,0.08)'],
                ['icon'=>'fact_check',     'label'=>__('ui.approvals'),     'route'=>'approvals.index',     'color'=>'#4ade80', 'bg'=>'rgba(74,222,128,0.1)'],
                ['icon'=>'inventory_2',    'label'=>__('ui.products'),      'route'=>'products.index',      'color'=>'#e879f9', 'bg'=>'rgba(232,121,249,0.1)'],
                ['icon'=>'bar_chart',      'label'=>__('ui.kpi'),           'route'=>'kpi.index',           'color'=>'#fde047', 'bg'=>'rgba(253,224,71,0.1)'],
                ['icon'=>'event_note',     'label'=>__('ui.activities'),    'route'=>'activities.index',    'color'=>'#94a3b8', 'bg'=>'rgba(148,163,184,0.08)'],
                ['icon'=>'description',    'label'=>__('ui.opportunities'), 'route'=>'opportunities.index', 'color'=>'#7dd3fc', 'bg'=>'rgba(125,211,252,0.1)'],
                ['icon'=>'contract',       'label'=>__('ui.vehicle_contracts'),  'route'=>'vehicle-contracts.index', 'color'=>'#86efac', 'bg'=>'rgba(134,239,172,0.1)'],
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

    {{-- ===== MAIN GRID: Executive + Fleet League ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Executive Summary (2/3) --}}
        <div id="widget-exec-summary" class="lg:col-span-2 exec-summary-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#1468a8;">auto_awesome</span>
                    <a href="{{ route('analytics.index') }}" class="text-xs font-bold uppercase tracking-widest dashboard-link" style="color:#1468a8;">{{ __('ui.executive_intelligence') }}</a>
                </div>
                <a href="{{ route('analytics.index') }}" style="background:rgba(20,104,168,0.08);color:#0f5f9f;border:1px solid rgba(20,104,168,0.18);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">{{ __('ui.ai_summary') }}</a>
            </div>
            <h3 class="text-base font-bold mb-3 leading-snug" style="color:#101828;">
                <a href="{{ route('analytics.index') }}" class="dashboard-link">{!! __('ui.performance_headline', ['value' => '<span style="color:#21785f;">18.4%</span>']) !!}</a>
            </h3>
            <p class="text-sm leading-relaxed mb-4" style="color:#64748b;">
                {{ __('ui.summary_text') }}
            </p>
            <div class="ai-metric-grid">
                <a href="{{ route('analytics.index') }}" class="ai-metric dashboard-link">
                    <div class="text-[10px] font-bold uppercase tracking-wide" style="color:#667085;">Revenue Lift</div>
                    <div class="text-xl font-black mt-1" style="color:#101828;">18.4%</div>
                    <div class="ai-meter"><span style="width:82%;background:#2f9d7e;"></span></div>
                </a>
                <a href="{{ route('fleet.index') }}" class="ai-metric dashboard-link">
                    <div class="text-[10px] font-bold uppercase tracking-wide" style="color:#667085;">Fleet Health</div>
                    <div class="text-xl font-black mt-1" style="color:#101828;">72%</div>
                    <div class="ai-meter"><span style="width:72%;background:#1468a8;"></span></div>
                </a>
                <a href="{{ route('finance.index') }}" class="ai-metric dashboard-link">
                    <div class="text-[10px] font-bold uppercase tracking-wide" style="color:#667085;">Invoice Risk</div>
                    <div class="text-xl font-black mt-1" style="color:#101828;">14</div>
                    <div class="ai-meter"><span style="width:58%;background:#d7a72f;"></span></div>
                </a>
            </div>
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#334155;">{{ __('ui.strategic_recommendations') }}</div>
                @php
                $recs = [
                    ['icon'=>'group','color'=>'#1468a8','route'=>'clients.index','text'=>__('ui.rec_clients')],
                    ['icon'=>'receipt_long','color'=>'#a17412','route'=>'finance.index','text'=>__('ui.rec_invoice')],
                    ['icon'=>'local_shipping','color'=>'#21785f','route'=>'fleet.index','text'=>__('ui.rec_fleet')],
                    ['icon'=>'build','color'=>'#72529a','route'=>'approvals.index','text'=>__('ui.rec_approval')],
                    ['icon'=>'leaderboard','color'=>'#1468a8','route'=>'kpi.index','text'=>__('ui.rec_sales')],
                ];
                @endphp
                @foreach($recs as $r)
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[14px] mt-0.5 flex-shrink-0" style="color:{{ $r['color'] }};">{{ $r['icon'] }}</span>
                    <a href="{{ route($r['route']) }}" class="text-xs dashboard-link" style="color:#475467;">{{ $r['text'] }}</a>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Fleet League (1/3) --}}
        <div id="widget-fleet-league" class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f59e0b;">emoji_events</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.fleet_league') }}</span>
                </div>
                <a href="{{ route('fleet.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">{{ __('ui.view_all') }}</a>
            </div>
            @php
            $fleets = [
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
                            <div class="rank-num" style="background:rgba(16,40,72,0.06);color:#667085;">{{ $i+1 }}</div>
                            <a href="{{ route('fleet.index') }}" class="text-xs font-semibold dashboard-link" style="color:#101828;">{{ $f['name'] }}</a>
                        </div>
                        <div class="flex items-center gap-2">
                            <span style="background:{{ $f['badgeColor'] }};color:{{ $f['badgeText'] }};font-size:9px;font-weight:700;padding:1px 6px;border-radius:4px;text-transform:uppercase;letter-spacing:0.04em;">{{ $f['badge'] }}</span>
                            <span class="text-xs font-bold" style="color:#101828;">{{ $f['pct'] }}%</span>
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

    {{-- ===== BOTTOM GRID: Revenue Chart + Sales Ranking + Bookings + Approvals ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Revenue Chart (2/3) --}}
        <div id="widget-revenue-chart" class="lg:col-span-2 cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#3b82f6;">bar_chart</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.weekly_revenue_movement') }}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-[10px] font-semibold" style="color:#475569;">{{ __('ui.peak_note') }}</span>
                    <a href="{{ route('analytics.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">Detail →</a>
                </div>
            </div>
            <canvas id="revenueChart" height="180"></canvas>
        </div>

        {{-- Sales Ranking (1/3) --}}
        <div id="widget-sales-ranking" class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#a78bfa;">military_tech</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.sales_ranking') }}</span>
                </div>
                <a href="{{ route('kpi.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">KPI →</a>
            </div>
            @php
            $sellers = [
                ['name'=>'Andi Pratama','rev'=>'Rp 740 Jt','closing'=>'38%','medal'=>'🥇','color'=>'#f59e0b'],
                ['name'=>'Sari Dewi','rev'=>'Rp 615 Jt','closing'=>'34%','medal'=>'🥈','color'=>'#94a3b8'],
                ['name'=>'Reza Firmansyah','rev'=>'Rp 480 Jt','closing'=>'29%','medal'=>'🥉','color'=>'#cd7c2f'],
                ['name'=>'Maya Corp.','rev'=>'Rp 355 Jt','closing'=>'24%','medal'=>'4','color'=>'#475569'],
            ];
            @endphp
            <div class="space-y-1">
                @foreach($sellers as $s)
                <div class="rank-row">
                    <span class="text-base flex-shrink-0">{{ $s['medal'] }}</span>
                    <div class="flex-grow min-w-0">
                        <a href="{{ route('kpi.index') }}" class="text-xs font-semibold dashboard-link truncate" style="color:#101828;">{{ $s['name'] }}</a>
                        <div class="text-[10px]" style="color:#475569;">Closing {{ $s['closing'] }}</div>
                    </div>
                    <div class="text-xs font-bold flex-shrink-0" style="color:{{ $s['color'] }};">{{ $s['rev'] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== BOTTOM ROW: Recent Bookings + Approval Queue ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Recent Bookings --}}
        <div id="widget-recent-books" class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#10b981;">route</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.recent_bookings') }}</span>
                </div>
                <a href="{{ route('bookings.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">{{ __('ui.view_all') }}</a>
            </div>
            @php
            $bookings = [
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
                        <div class="text-[10px]" style="color:#475569;"><a href="{{ route('clients.index') }}" class="dashboard-link">{{ $b['client'] }}</a> - <a href="{{ route('fleet.index') }}" class="dashboard-link">{{ $b['fleet'] }}</a></div>
                    </div>
                    <span class="status-badge {{ $b['statusClass'] }} flex-shrink-0">{{ $b['status'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Approval Queue --}}
        <div id="widget-approval-q" class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f87171;">pending_actions</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">{{ __('ui.approval_queue') }}</span>
                </div>
                <a href="{{ route('approvals.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">Approve →</a>
            </div>
            @php
            $approvals = [
                ['title'=>'Fleet Maintenance PO','dept'=>'Operational','priority'=>'High','icon'=>'build','iconColor'=>'#f87171'],
                ['title'=>'Corp. Contract Renewal','dept'=>'Sales','priority'=>'High','icon'=>'handshake','iconColor'=>'#f59e0b'],
                ['title'=>'Invoice Adjustment','dept'=>'Finance','priority'=>'Medium','icon'=>'receipt_long','iconColor'=>'#fbbf24'],
                ['title'=>'Enterprise Onboarding','dept'=>'Sales','priority'=>'Medium','icon'=>'person_add','iconColor'=>'#60a5fa'],
            ];
            @endphp
            <div class="space-y-1">
                @foreach($approvals as $a)
                <div class="approval-item">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.04);">
                        <span class="material-symbols-outlined text-[16px]" style="color:{{ $a['iconColor'] }};">{{ $a['icon'] }}</span>
                    </div>
                    <div class="flex-grow min-w-0">
                        <a href="{{ route('approvals.index') }}" class="text-xs font-semibold dashboard-link truncate" style="color:#101828;">{{ $a['title'] }}</a>
                        <div class="text-[10px]" style="color:#475569;">{{ $a['dept'] }}</div>
                    </div>
                    <span class="{{ $a['priority'] === 'High' ? 'priority-high' : 'priority-med' }} flex-shrink-0 uppercase tracking-wide">{{ $a['priority'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

</div>
    {{-- ════ CHARTS SECTION ════ --}}
    <div id="widget-charts-section">
        @include('dashboard.charts')
    </div>

</div>{{-- close outer space-y-5 --}}
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Revenue (Jt)',
                data: [320, 410, 285, 520, 475, 240, 190],
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
        { id: 'spark-revenue',   data: [210,240,285,310,295,340,380,395,420,440,460,484], color: '#1468a8' },
        { id: 'spark-bookings',  data: [180,195,210,230,225,248,260,255,270,248,265,280], color: '#60a5fa' },
        { id: 'spark-fleet',     data: [65,68,70,72,69,71,74,72,75,73,72,74],            color: '#34d399' },
        { id: 'spark-clients',   data: [100,104,108,110,112,115,116,118,120,122,125,128], color: '#a78bfa' },
        { id: 'spark-invoice',   data: [280,310,340,360,395,420,410,430,440,420,415,420], color: '#fbbf24' },
        { id: 'spark-approvals', data: [8,10,12,9,11,14,12,15,13,14,16,14],              color: '#f87171' },
    ];
    sparks.forEach(s => {
        if (window.CRM_Sparkline) CRM_Sparkline.render(s.id, s.data, s.color);
    });
});
</script>
@endpush
