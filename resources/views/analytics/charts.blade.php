{{-- Analytics Charts Partial --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

    {{-- Multi-metric trend --}}
    <div class="chart-card lg:col-span-2">
        <div class="flex items-center justify-between mb-3">
            <span class="chart-title">📈 Multi-Metric Trend <span class="chart-sub">6 bulan terakhir</span></span>
            <div class="flex gap-3 text-[11px]" style="color:var(--cc-text-muted)">
                <span><span style="color:var(--color-primary)">●</span> Revenue</span>
                <span><span style="color:#10b981">●</span> Deals Won</span>
                <span><span style="color:#a78bfa">●</span> New Clients</span>
            </div>
        </div>
        <div style="height:200px;position:relative">
            <canvas id="chart-analytics-trend"></canvas>
        </div>
    </div>

    {{-- Win/Loss ratio --}}
    <div class="chart-card">
        <span class="chart-title">🏆 Win / Loss Ratio <span class="chart-sub">pipeline</span></span>
        <div style="height:160px;position:relative;margin-top:12px;">
            <canvas id="chart-analytics-winloss"></canvas>
        </div>
    </div>

    {{-- Revenue by segment --}}
    <div class="chart-card">
        <span class="chart-title">💼 Revenue by Segment <span class="chart-sub">bulan ini</span></span>
        <div style="height:160px;position:relative;margin-top:12px;">
            <canvas id="chart-analytics-segment"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';
    const tt = () => ({ backgroundColor: isDark() ? 'rgba(15,15,28,0.96)' : 'rgba(255,255,255,0.96)', borderColor: 'rgba(0,229,255,0.2)', borderWidth: 1, titleColor: tc(), bodyColor: isDark() ? '#e2e8f0' : '#1e1e3a', padding: 10, cornerRadius: 8 });

    // Trend line
    const ctxT = document.getElementById('chart-analytics-trend');
    if (ctxT) new Chart(ctxT, {
        type: 'line',
        data: {
            labels: ['Jan','Feb','Mar','Apr','May','Jun'],
            datasets: [
                { label: 'Revenue (Jt)', data: [1820,2100,1950,2400,2650,2840], borderColor: 'var(--color-primary)', backgroundColor: 'rgba(0,229,255,0.06)', fill: true, tension: 0.4, borderWidth: 2.5, pointRadius: 4, pointBackgroundColor: 'var(--color-primary)', yAxisID: 'y' },
                { label: 'Deals Won',    data: [18,22,17,28,31,34],             borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.4, borderWidth: 2, borderDash: [4,3], pointRadius: 3, pointBackgroundColor: '#10b981', yAxisID: 'y2' },
                { label: 'New Clients', data: [8,11,9,14,12,16],               borderColor: '#a78bfa', backgroundColor: 'transparent', tension: 0.4, borderWidth: 2, borderDash: [4,3], pointRadius: 3, pointBackgroundColor: '#a78bfa', yAxisID: 'y2' },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false }, tooltip: tt() },
            scales: {
                x:  { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11 } } },
                y:  { position: 'left',  grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'Jt' } },
                y2: { position: 'right', grid: { display: false }, ticks: { color: tc(), stepSize: 5 } },
            }
        }
    });

    // Win/Loss
    const ctxW = document.getElementById('chart-analytics-winloss');
    if (ctxW) new Chart(ctxW, {
        type: 'doughnut',
        data: {
            labels: ['Won', 'Lost', 'In Progress'],
            datasets: [{ data: [42, 15, 43], backgroundColor: ['#10b981','#ef4444','#3b82f6'], borderColor: isDark() ? '#09090f' : '#f0f0fa', borderWidth: 3, hoverOffset: 5 }]
        },
        options: { responsive: true, maintainAspectRatio: false, resizeDelay: 100, cutout: '62%', animation: { duration: 400 },
            plugins: { legend: { position: 'right', labels: { color: tc(), font: { size: 11 }, boxWidth: 12 } } } }
    });

    // Segment bar
    const ctxS = document.getElementById('chart-analytics-segment');
    if (ctxS) new Chart(ctxS, {
        type: 'bar',
        data: {
            labels: ['Corporate', 'Government', 'SME', 'Retail'],
            datasets: [{ data: [1240, 680, 420, 280], backgroundColor: ['rgba(0,229,255,0.55)','rgba(139,92,246,0.55)','rgba(245,158,11,0.55)','rgba(59,130,246,0.55)'], borderRadius: 5, borderSkipped: false }]
        },
        options: { responsive: true, maintainAspectRatio: false, resizeDelay: 100, animation: { duration: 400 },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `Rp ${c.raw} Jt` } } },
            scales: { x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11 } } }, y: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'Jt' } } }
        }
    });

    new MutationObserver(() => Object.values(Chart.instances || {}).forEach(c => c.update()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
