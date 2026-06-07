@extends('layouts.app')

@section('header_title', 'Director HQ')

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
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- ===== COMMAND CENTER HEADER ===== --}}
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <h1 class="text-xl font-black tracking-tight" style="color:#101828;">Bluebird CRM <span style="color:#1468a8;">Command Center</span></h1>
            </div>
            <p class="text-xs" style="color:#475569;">Corporate Fleet · Sales Pipeline · Dispatch · Revenue Intelligence</p>
            <div class="flex flex-wrap items-center gap-2 mt-3">
                <a href="{{ route('dashboard') }}" class="badge-demo">Live Demo</a>
                <a href="{{ route('analytics.index') }}" class="badge-demo">June 2026</a>
                <a href="{{ route('dashboard') }}" class="badge-live flex items-center gap-1.5">
                    <span class="pulse-dot" style="width:5px;height:5px;"></span>
                    Director HQ
                </a>
                <a href="{{ route('analytics.index') }}" style="background:rgba(141,107,184,0.12);color:#72529a;border:1px solid rgba(141,107,184,0.2);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">API Ready</a>
                <a href="{{ route('bookings.index') }}" style="background:rgba(215,167,47,0.14);color:#8c6814;border:1px solid rgba(215,167,47,0.26);font-size:10px;font-weight:700;padding:3px 8px;border-radius:6px;text-transform:uppercase;letter-spacing:0.04em;">Railway Deploy</a>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <a href="{{ route('approvals.index') }}" class="btn-primary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">fact_check</span>
                Approve Queue
            </a>
            <a href="{{ route('analytics.index') }}" class="btn-secondary text-xs py-2 px-4">
                <span class="material-symbols-outlined text-[14px]">query_stats</span>
                Reports
            </a>
        </div>
    </div>

    {{-- ===== KPI CARDS ROW ===== --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

        {{-- KPI 1: Revenue --}}
        <div class="kpi-card kpi-cyan col-span-2 md:col-span-1 lg:col-span-1">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(20,104,168,0.10);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#1468a8;">payments</span>
                </div>
                <span class="signal-up">▲ 18.4%</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">Rp 2,84 M</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Monthly Revenue</div>
        </div>

        {{-- KPI 2: Bookings --}}
        <div class="kpi-card kpi-blue">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(59,130,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#60a5fa;">route</span>
                </div>
                <span class="signal-up">▲ 32</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $pendingDispatch ?? 248 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Active Bookings</div>
        </div>

        {{-- KPI 3: Fleet --}}
        <div class="kpi-card kpi-emerald">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(16,185,129,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#34d399;">local_shipping</span>
                </div>
                <span class="signal-up">Healthy</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $availableVehicles ?? 72 }}%</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Fleet Utilization</div>
        </div>

        {{-- KPI 4: Clients --}}
        <div class="kpi-card kpi-purple">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(139,92,246,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#a78bfa;">corporate_fare</span>
                </div>
                <span class="signal-up">▲ 12</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">128</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Corp. Clients</div>
        </div>

        {{-- KPI 5: Outstanding --}}
        <div class="kpi-card kpi-gold">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(245,158,11,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#fbbf24;">receipt_long</span>
                </div>
                <span class="signal-warn">Attention</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">Rp 420 Jt</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Outstanding Inv.</div>
        </div>

        {{-- KPI 6: Approval --}}
        <div class="kpi-card kpi-red">
            <div class="flex items-start justify-between mb-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(239,68,68,0.1);">
                    <span class="material-symbols-outlined text-[17px]" style="color:#f87171;">pending_actions</span>
                </div>
                <span class="signal-down">Urgent</span>
            </div>
            <div class="text-lg font-black leading-tight" style="color:#101828;">{{ $pendingPO ?? 14 }}</div>
            <div class="text-[10px] font-semibold uppercase tracking-wide mt-1" style="color:#475569;">Pending Approval</div>
        </div>

    </div>

    {{-- ===== MAIN GRID: Executive + Fleet League ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Executive Summary (2/3) --}}
        <div class="lg:col-span-2 exec-summary-card p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#1468a8;">auto_awesome</span>
                    <a href="{{ route('analytics.index') }}" class="text-xs font-bold uppercase tracking-widest dashboard-link" style="color:#1468a8;">Executive Intelligence</a>
                </div>
                <a href="{{ route('analytics.index') }}" style="background:rgba(20,104,168,0.08);color:#0f5f9f;border:1px solid rgba(20,104,168,0.18);font-size:9px;font-weight:700;padding:2px 8px;border-radius:5px;text-transform:uppercase;letter-spacing:0.06em;">AI Summary</a>
            </div>
            <h3 class="text-base font-bold mb-3 leading-snug" style="color:#101828;">
                <a href="{{ route('analytics.index') }}" class="dashboard-link">Corporate fleet performance naik <span style="color:#21785f;">18.4%</span> bulan ini.</a>
            </h3>
            <p class="text-sm leading-relaxed mb-4" style="color:#64748b;">
                <a href="{{ route('fleet.index') }}" class="dashboard-link">Golden Bird</a> menjadi kontributor revenue terbesar, didorong kontrak corporate dan airport executive transfer. <a href="{{ route('bookings.index') }}" class="dashboard-link">Big Bird</a> stabil dari charter perusahaan, sementara <a href="{{ route('pipeline.index') }}" class="dashboard-link">Cititrans</a> membutuhkan peningkatan pipeline untuk rute bisnis. <a href="{{ route('finance.index') }}" class="dashboard-link">Finance</a> perlu mempercepat follow-up outstanding invoice di atas 14 hari.
            </p>
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-widest mb-2" style="color:#334155;">Strategic Recommendations</div>
                @php
                $recs = [
                    ['icon'=>'group','color'=>'#1468a8','route'=>'clients.index','text'=>'Prioritaskan 12 client corporate dengan potensi renewal'],
                    ['icon'=>'receipt_long','color'=>'#a17412','route'=>'finance.index','text'=>'Follow-up invoice overdue di atas 14 hari - Rp 420 Jt exposed'],
                    ['icon'=>'local_shipping','color'=>'#21785f','route'=>'fleet.index','text'=>'Tambahkan fleet allocation untuk area Jakarta HQ'],
                    ['icon'=>'build','color'=>'#72529a','route'=>'approvals.index','text'=>'Percepat approval PO maintenance untuk unit high-demand'],
                    ['icon'=>'leaderboard','color'=>'#1468a8','route'=>'kpi.index','text'=>'Dorong sales terbaik untuk handle enterprise account'],
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
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f59e0b;">emoji_events</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Fleet League</span>
                </div>
                <a href="{{ route('fleet.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">View All →</a>
            </div>
            @php
            $fleets = [
                ['name'=>'Golden Bird','pct'=>92,'color'=>'#f59e0b','badge'=>'High Performer','badgeColor'=>'rgba(245,158,11,0.12)','badgeText'=>'#fbbf24'],
                ['name'=>'Big Bird','pct'=>84,'color'=>'#10b981','badge'=>'Stable','badgeColor'=>'rgba(16,185,129,0.12)','badgeText'=>'#34d399'],
                ['name'=>'Cititrans','pct'=>78,'color'=>'#3b82f6','badge'=>'Needs Growth','badgeColor'=>'rgba(59,130,246,0.12)','badgeText'=>'#60a5fa'],
                ['name'=>'Exec. Transport','pct'=>73,'color'=>'#8b5cf6','badge'=>'Under Review','badgeColor'=>'rgba(139,92,246,0.12)','badgeText'=>'#a78bfa'],
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
        <div class="lg:col-span-2 cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#3b82f6;">bar_chart</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Weekly Revenue Movement</span>
                </div>
                <span class="text-[10px] font-semibold" style="color:#475569;">Peak: Kamis - Corporate Airport Transfer</span>
            </div>
            <canvas id="revenueChart" height="180"></canvas>
        </div>

        {{-- Sales Ranking (1/3) --}}
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#a78bfa;">military_tech</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Sales Ranking</span>
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
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#10b981;">route</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Recent Bookings</span>
                </div>
                <a href="{{ route('bookings.index') }}" class="text-[10px] font-semibold" style="color:#3b82f6;">View All →</a>
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
        <div class="cc-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]" style="color:#f87171;">pending_actions</span>
                    <span class="text-xs font-bold uppercase tracking-widest" style="color:#94a3b8;">Approval Queue</span>
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
});
</script>
@endpush
