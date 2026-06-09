@extends('layouts.app')

@section('header_title', 'Sales Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Revenue KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <div class="group block cc-card rounded-lg shadow p-6 border-l-4 border-blue-500 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Today</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
        </div>

        <div class="group block cc-card rounded-lg shadow p-6 border-l-4 border-indigo-500 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Week</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($weekRevenue) }}</p>
        </div>

        <div class="group block cc-card rounded-lg shadow p-6 border-l-4 border-purple-500 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Month</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
        </div>

        <div class="group block cc-card rounded-lg shadow p-6 border-l-4 border-green-500 transition-all">
            <p class="text-gray-500 text-sm">My Revenue Year</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($yearRevenue) }}</p>
        </div>
    </div>

    {{-- Operational KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <a href="{{ route('bookings.index', ['status' => 'active']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-yellow-500 hover:shadow-md hover:bg-yellow-50 transition-all">
            <p class="text-gray-500 text-sm">Active Bookings</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $activeBookings }}</p>
            <p class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
        </a>

        <a href="{{ route('clients.index') }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-orange-500 hover:shadow-md hover:bg-orange-50 transition-all">
            <p class="text-gray-500 text-sm">My Clients</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $myClients }}</p>
            <p class="text-xs text-orange-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View my clients →</p>
        </a>

        <div class="cc-card rounded-lg shadow p-6 flex items-center">
            <a href="{{ route('bookings.create') }}" class="block w-full bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-semibold hover:bg-blue-700 transition-colors">
                ➕ New Booking
            </a>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4" style="color:var(--cc-text)">Pipeline Funnel</h3>
            <div id="funnelChart" class="min-h-[300px]"></div>
        </div>
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4" style="color:var(--cc-text)">Revenue Trend (6 Months)</h3>
            <div id="revenueChart" class="min-h-[300px]"></div>
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="cc-card rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold" style="color:var(--cc-text)">Recent Bookings</h3>
            <a href="{{ route('bookings.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all →</a>
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
                    <tr class="border-b transition-colors" style="border-color:var(--cc-border); hover:background:var(--cc-row-hover)">
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.classList.contains('dark');
    var textColor = isDark ? '#94a3b8' : '#64748b';

    // Pipeline Funnel
    var funnelOptions = {
        series: [{ name: "Deals", data: {!! json_encode($salesFunnel ?? []) !!} }],
        chart: { type: 'bar', height: 320, toolbar: { show: false }, background: 'transparent' },
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
        chart: { type: 'area', height: 320, toolbar: { show: false }, background: 'transparent' },
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
