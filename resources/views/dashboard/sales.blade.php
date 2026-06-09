@extends('layouts.app')

@section('header_title', 'Sales Dashboard')

@section('content')
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- Row 1: Revenue KPIs (4 cards, each gs-w=3) --}}
    <div class="grid-stack-item" gs-id="w-revenue-today" gs-x="0" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Today</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-revenue-week" gs-x="3" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-indigo-500 h-full">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Week</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($weekRevenue) }}</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-revenue-month" gs-x="6" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-purple-500 h-full">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Month</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-revenue-year" gs-x="9" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-green-500 h-full">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Year</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($yearRevenue) }}</p>
            </div>
        </div>
    </div>

    {{-- Row 2: Operational KPIs --}}
    <div class="grid-stack-item" gs-id="w-active-bookings" gs-x="0" gs-y="2" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-yellow-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Active Bookings</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $activeBookings }}</p>
                <p class="text-xs text-yellow-600 dark:text-yellow-500 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-my-clients" gs-x="4" gs-y="2" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <a href="{{ route('clients.index') }}" class="group block cc-card rounded-xl shadow p-5 border-l-4 border-orange-500 h-full hover:shadow-md transition-all">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Clients</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ $myClients }}</p>
                <p class="text-xs text-orange-600 dark:text-orange-500 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View my clients →</p>
            </a>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-new-booking" gs-x="8" gs-y="2" gs-w="4" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 flex items-center justify-center h-full">
                <a href="{{ route('bookings.create') }}" class="block w-full bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-semibold hover:bg-blue-700 transition-colors">
                    ➕ New Booking
                </a>
            </div>
        </div>
    </div>

    {{-- Row 3: Charts --}}
    <div class="grid-stack-item" gs-id="w-funnel-chart" gs-x="0" gs-y="4" gs-w="6" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full">
                <h3 class="text-base font-semibold mb-3" style="color:var(--cc-text)">Sales Pipeline</h3>
                <div id="funnelChart" style="min-height:280px"></div>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-revenue-chart" gs-x="6" gs-y="4" gs-w="6" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full">
                <h3 class="text-base font-semibold mb-3" style="color:var(--cc-text)">Revenue Trend (6 Months)</h3>
                <div id="revenueChart" style="min-height:280px"></div>
            </div>
        </div>
    </div>

    {{-- Row 4: Recent Bookings --}}
    <div class="grid-stack-item" gs-id="w-recent-bookings" gs-x="0" gs-y="9" gs-w="12" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full overflow-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-base font-semibold" style="color:var(--cc-text)">Recent Bookings</h3>
                    <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">View all →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b" style="border-color:var(--cc-border)">
                            <tr style="color:var(--cc-text-muted)">
                                <th class="text-left py-2">Booking #</th>
                                <th class="text-left py-2">Client</th>
                                <th class="text-left py-2">Vehicle</th>
                                <th class="text-left py-2">Status</th>
                                <th class="text-right py-2">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings as $booking)
                            <tr class="border-b transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="border-color:var(--cc-border)">
                                <td class="py-3">
                                    <a href="{{ route('bookings.show', $booking->id) }}" class="text-blue-500 hover:underline font-mono">
                                        {{ $booking->booking_number }}
                                    </a>
                                </td>
                                <td class="py-3">
                                    <a href="{{ route('clients.show', $booking->client_id) }}" class="hover:text-blue-500" style="color:var(--cc-text)">
                                        {{ $booking->client->company_name }}
                                    </a>
                                </td>
                                <td class="py-3" style="color:var(--cc-text-muted)">{{ $booking->vehicle->plate_number }}</td>
                                <td class="py-3"><x-status-badge :status="$booking->status" /></td>
                                <td class="py-3 text-right font-semibold" style="color:var(--cc-text)">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="py-4 text-center" style="color:var(--cc-text-muted)">No recent bookings</td></tr>
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

    // Pipeline Funnel
    var funnelOptions = {
        series: [{ name: "Deals", data: {!! json_encode($salesFunnel ?? []) !!} }],
        chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
        plotOptions: { bar: { borderRadius: 4, horizontal: true, distributed: true, dataLabels: { position: 'bottom' } } },
        colors: ['#6366f1', '#3b82f6', '#f59e0b', '#10b981'],
        dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'] }, offsetX: 0 },
        xaxis: { categories: ['Prospecting', 'Proposal', 'Negotiation', 'Won'], labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor } } },
        tooltip: { theme: isDark ? 'dark' : 'light' }
    };
    new ApexCharts(document.querySelector("#funnelChart"), funnelOptions).render();

    // Revenue Trend
    var revData = {!! json_encode($revenueTrend ?? ['labels'=>[],'data'=>[]]) !!};
    var revenueOptions = {
        series: [{ name: "Revenue", data: revData.data }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
        colors: ['#10b981'],
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
