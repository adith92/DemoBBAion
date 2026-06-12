{{-- Finance Charts Partial --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">

    {{-- Cashflow Waterfall --}}
    <div class="chart-card">
        <span class="chart-title">💰 Cashflow Overview <span class="chart-sub">bulan ini</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-fin-cashflow"></canvas>
        </div>
    </div>

    {{-- Invoice Aging --}}
    <div class="chart-card">
        <span class="chart-title">📋 Invoice Aging <span class="chart-sub">outstanding</span></span>
        <div style="height:180px;position:relative;margin-top:12px;">
            <canvas id="chart-fin-aging"></canvas>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = () => !document.documentElement.classList.contains('light');
    const gc = () => isDark() ? 'rgba(255,255,255,0.04)' : 'rgba(80,80,180,0.06)';
    const tc = () => isDark() ? '#64748b' : '#7070a0';

    // Cashflow bar
    const ctxC = document.getElementById('chart-fin-cashflow');
    if (ctxC) new Chart(ctxC, {
        type: 'bar',
        data: {
            labels: ['Revenue', 'COGS', 'OpEx', 'Net Profit'],
            datasets: [{
                data: [2840, -980, -620, 1240],
                backgroundColor: [
                    'rgba(16,185,129,0.65)', 'rgba(239,68,68,0.65)',
                    'rgba(245,158,11,0.65)', 'rgba(0,229,255,0.65)'
                ],
                borderColor: ['#10b981','#ef4444','#f59e0b','var(--color-primary)'],
                borderWidth: 1, borderRadius: 6, borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400, easing: 'easeOutQuart' },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `Rp ${Math.abs(c.raw)} Jt` } } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), font: { size: 11, weight: '600' } } },
                y: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'Jt' } },
            }
        }
    });

    // Aging horizontal bar
    const ctxA = document.getElementById('chart-fin-aging');
    if (ctxA) new Chart(ctxA, {
        type: 'bar',
        data: {
            labels: ['0–14 days', '15–30 days', '31–60 days', '60+ days'],
            datasets: [{
                label: 'Outstanding (Jt)',
                data: [180, 140, 70, 30],
                backgroundColor: ['rgba(16,185,129,0.6)','rgba(245,158,11,0.6)','rgba(249,115,22,0.6)','rgba(239,68,68,0.6)'],
                borderColor:     ['#10b981','#f59e0b','#f97316','#ef4444'],
                borderWidth: 1, borderRadius: 5, borderSkipped: false,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false, resizeDelay: 100,
            animation: { duration: 400 },
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => `Rp ${c.raw} Jt` } } },
            scales: {
                x: { grid: { color: gc(), drawBorder: false }, ticks: { color: tc(), callback: v => v + 'Jt' } },
                y: { grid: { display: false }, ticks: { color: tc(), font: { size: 11, weight: '600' } } },
            }
        }
    });

    new MutationObserver(() => Object.values(Chart.instances || {}).forEach(c => c.update()))
        .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
});
</script>
@endpush
