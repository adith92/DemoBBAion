@extends('layouts.app')

@section('header_title', 'GM Dashboard')

@section('content')
<div class="space-y-6 font-sans">

    {{-- SECTION 1: QUICK SHORTCUTS --}}
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-[#003887] text-[28px]">flash_on</span>
            Quick Shortcuts
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">

            {{-- Shortcut 1: Approve PO --}}
            <a href="{{ route('approvals.index') }}"
               class="group p-6 bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all cursor-pointer">
                <div class="text-center">
                    <div class="text-4xl mb-3">📋</div>
                    <p class="text-sm font-bold text-gray-900">Approve PO</p>
                    <p class="text-xs text-orange-600 font-semibold mt-2 group-hover:text-orange-700">
                        {{ $pendingPO ?? 0 }} Pending
                    </p>
                </div>
            </a>

            {{-- Shortcut 2: Fleet Availability --}}
            <a href="{{ route('fleet.index') }}"
               class="group p-6 bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all cursor-pointer">
                <div class="text-center">
                    <div class="text-4xl mb-3">🚗</div>
                    <p class="text-sm font-bold text-gray-900">Fleet Status</p>
                    <p class="text-xs text-green-600 font-semibold mt-2 group-hover:text-green-700">
                        {{ $availableVehicles ?? 0 }} Available
                    </p>
                </div>
            </a>

            {{-- Shortcut 3: Revenue Report --}}
            <a href="{{ route('finance.index') }}"
               class="group p-6 bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all cursor-pointer">
                <div class="text-center">
                    <div class="text-4xl mb-3">💰</div>
                    <p class="text-sm font-bold text-gray-900">Revenue</p>
                    <p class="text-xs text-blue-600 font-semibold mt-2 group-hover:text-blue-700">
                        {{ \App\Helpers\FormatHelper::formatIDR($todayRevenue ?? 0) }}
                    </p>
                </div>
            </a>

            {{-- Shortcut 4: Dispatch Center --}}
            <a href="{{ route('bookings.index') }}"
               class="group p-6 bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all cursor-pointer">
                <div class="text-center">
                    <div class="text-4xl mb-3">📍</div>
                    <p class="text-sm font-bold text-gray-900">Dispatch</p>
                    <p class="text-xs text-blue-600 font-semibold mt-2 group-hover:text-blue-700">
                        {{ $pendingDispatch ?? 0 }} Pending
                    </p>
                </div>
            </a>

            {{-- Shortcut 5: Activities --}}
            <a href="{{ route('activities.index') }}"
               class="group p-6 bg-white rounded-2xl shadow-sm border border-slate-200 hover:border-blue-400 hover:shadow-lg transition-all cursor-pointer">
                <div class="text-center">
                    <div class="text-4xl mb-3">📝</div>
                    <p class="text-sm font-bold text-gray-900">Activities</p>
                    <p class="text-xs text-purple-600 font-semibold mt-2 group-hover:text-purple-700">
                        View Log
                    </p>
                </div>
            </a>

        </div>
    </section>

    {{-- SECTION 2: KPI CARDS --}}
    <section>
        <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
            <span class="material-symbols-outlined text-[#003887] text-[28px]">trending_up</span>
            Key Performance Indicators
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

            {{-- KPI 1: Total Revenue This Month --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow cursor-pointer border-l-4 border-l-blue-500">
                <p class="text-gray-600 text-xs uppercase font-semibold tracking-wider">Revenue (This Month)</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-3">
                    {{ \App\Helpers\FormatHelper::formatIDR($totalMonthlyRevenue ?? 0) }}
                </p>
                <p class="text-xs text-green-600 mt-2">📈 Target tracking</p>
            </div>

            {{-- KPI 2: Completed Bookings This Month --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow cursor-pointer border-l-4 border-l-green-500">
                <p class="text-gray-600 text-xs uppercase font-semibold tracking-wider">Completed Bookings</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-3">{{ $completedBookings ?? 0 }}</p>
                <p class="text-xs text-green-600 mt-2">✅ This month</p>
            </div>

            {{-- KPI 3: Avg Revenue per Booking --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow cursor-pointer border-l-4 border-l-purple-500">
                <p class="text-gray-600 text-xs uppercase font-semibold tracking-wider">Avg per Booking</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-3">
                    {{ \App\Helpers\FormatHelper::formatIDR($avgRevenuePerBooking ?? 0) }}
                </p>
                <p class="text-xs text-purple-600 mt-2">💡 Average value</p>
            </div>

            {{-- KPI 4: Active Clients --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 hover:shadow-md transition-shadow cursor-pointer border-l-4 border-l-orange-500">
                <p class="text-gray-600 text-xs uppercase font-semibold tracking-wider">Active Clients</p>
                <p class="text-3xl font-extrabold text-gray-900 mt-3">{{ $activeClients ?? 0 }}</p>
                <p class="text-xs text-orange-600 mt-2">🏢 B2B partners</p>
            </div>

        </div>
    </section>

    {{-- SECTION 3: MAIN CHART (Tabbed Carousel) --}}
    <section>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#003887] text-[24px]">show_chart</span>
                    Revenue Trend
                </h2>
                <div class="flex gap-2">
                    <button data-period="daily"
                            class="tab-btn active px-4 py-2 rounded-lg border-2 border-[#003887] bg-blue-50 text-[#003887] font-semibold text-sm">
                        Daily
                    </button>
                    <button data-period="weekly"
                            class="tab-btn px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700 font-semibold text-sm hover:border-gray-300">
                        Weekly
                    </button>
                    <button data-period="monthly"
                            class="tab-btn px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700 font-semibold text-sm hover:border-gray-300">
                        Monthly
                    </button>
                    <button data-period="yearly"
                            class="tab-btn px-4 py-2 rounded-lg border-2 border-gray-200 text-gray-700 font-semibold text-sm hover:border-gray-300">
                        Yearly
                    </button>
                </div>
            </div>

            <div class="relative h-80 transition-opacity duration-300" id="chartContainer">
                <canvas id="revenueChart" style="position: relative; height: 320px !important;"></canvas>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200 flex justify-between items-center">
                <p class="text-xs text-gray-600">
                    <span class="inline-block w-3 h-3 bg-[#003887] rounded-full mr-2"></span>
                    Revenue (Rp)
                </p>
                <a href="{{ route('finance.index') }}" class="px-3 py-1 text-xs bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                    📊 View Full Report
                </a>
            </div>
        </div>
    </section>

    {{-- SECTION 4: SECONDARY CHARTS --}}
    <section>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Chart 1: Top Sales --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-green-600">person_fill</span>
                    Top Sales This Month
                </h3>
                <div class="relative h-80">
                    <canvas id="topSalesChart" style="position: relative; height: 300px !important;"></canvas>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('activities.index') }}" class="text-[#003887] hover:underline text-sm font-semibold">
                        View all sales performance →
                    </a>
                </div>
            </div>

            {{-- Chart 2: Client Distribution --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-purple-600">pie_chart</span>
                    Client Tier Distribution
                </h3>
                <div class="relative h-80">
                    <canvas id="clientTierChart" style="position: relative; height: 300px !important;"></canvas>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <a href="{{ route('clients.index') }}" class="text-[#003887] hover:underline text-sm font-semibold">
                        View all clients →
                    </a>
                </div>
            </div>

        </div>
    </section>

</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<script>
let revenueChart = null;
let topSalesChart = null;
let clientTierChart = null;

const chartData = {
    daily: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        revenue: [12000000, 15000000, 11000000, 18000000, 14000000, 16000000, 13000000],
    },
    weekly: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
        revenue: [85000000, 92000000, 78000000, 95000000],
    },
    monthly: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        revenue: [380000000, 420000000, 350000000, 450000000, 400000000, 380000000],
    },
    yearly: {
        labels: ['2021', '2022', '2023', '2024', '2025', '2026'],
        revenue: [3200000000, 3800000000, 4200000000, 4600000000, 5100000000, 4900000000],
    }
};

const topSalesData = {
    labels: ['Andi (Sales 1)', 'Sari (Sales 2)', 'Reza (Sales 3)', 'Budi', 'Dewi'],
    data: [450000000, 380000000, 320000000, 280000000, 210000000],
};

const clientTierData = {
    labels: ['Platinum', 'Gold', 'Silver', 'Bronze'],
    data: [12, 28, 35, 25],
    colors: ['#003887', '#1e4fa8', '#2563EB', '#60A5FA'],
};

function initRevenueChart(period = 'daily') {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const data = chartData[period];

    if (revenueChart) {
        revenueChart.destroy();
    }

    revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Revenue (Rp)',
                data: data.revenue,
                borderColor: '#003887',
                backgroundColor: 'rgba(0, 56, 135, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#003887',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                        }
                    }
                }
            }
        }
    });
}

function initTopSalesChart() {
    const ctx = document.getElementById('topSalesChart').getContext('2d');

    topSalesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: topSalesData.labels,
            datasets: [{
                label: 'Revenue (Rp)',
                data: topSalesData.data,
                backgroundColor: ['#003887', '#1e4fa8', '#2563EB', '#60A5FA', '#93C5FD'],
                borderRadius: 8,
                borderSkipped: false,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.parsed.x.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                        }
                    }
                }
            }
        }
    });
}

function initClientTierChart() {
    const ctx = document.getElementById('clientTierChart').getContext('2d');

    clientTierChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: clientTierData.labels,
            datasets: [{
                data: clientTierData.data,
                backgroundColor: clientTierData.colors,
                borderColor: '#fff',
                borderWidth: 3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.parsed / total) * 100).toFixed(1);
                            return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
}

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const period = this.dataset.period;

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.classList.remove('border-[#003887]', 'bg-blue-50', 'text-[#003887]');
            b.classList.add('border-gray-200', 'text-gray-700');
        });
        this.classList.add('border-[#003887]', 'bg-blue-50', 'text-[#003887]');
        this.classList.remove('border-gray-200', 'text-gray-700');

        const container = document.getElementById('chartContainer');
        container.style.opacity = '0';

        setTimeout(() => {
            initRevenueChart(period);
            container.style.opacity = '1';
        }, 200);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    initRevenueChart('daily');
    initTopSalesChart();
    initClientTierChart();
});
</script>

<style>
.tab-btn {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.tab-btn.active {
    transform: translateY(-2px);
}

#chartContainer {
    transition: opacity 0.3s ease;
}
</style>
@endsection
