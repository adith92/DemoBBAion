@extends('layouts.app')

@section('header_title', $user->name . ' — Performance')

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => '#', 'label' => $user->name . ' Performance'],
]" />

{{-- Hero --}}
<div class="bg-gradient-to-br from-indigo-900 to-indigo-800 rounded-2xl shadow p-8 mb-6 text-gray-900 border border-indigo-700/50">
    <div class="flex flex-col md:flex-row justify-between items-start gap-4">
        <div>
            <p class="text-indigo-200 text-xs uppercase tracking-widest font-semibold mb-1">Sales Performance</p>
            <h2 class="text-3xl font-bold">{{ $user->name }}</h2>
            <p class="text-indigo-200 text-sm mt-2">{{ $user->email }}</p>
        </div>
        <div class="text-right">
            <p class="text-indigo-200 text-sm">Total Revenue</p>
            <p class="text-4xl font-bold mt-1">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_revenue']) }}</p>
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <a href="{{ route('bookings.index', ['sales_id' => $user->id, 'status' => 'completed']) }}"
       class="group block bg-emerald-500/10 rounded-xl shadow p-4 border-l-4 border-emerald-500 hover:shadow-md transition-all text-center border border-[var(--cc-border)]/30 hover:bg-emerald-500/20">
        <p class="text-2xl font-bold text-emerald-400">{{ $stats['completed'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1 font-medium">Completed</p>
        <p class="text-[10px] text-emerald-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View bookings →</p>
    </a>
    <a href="{{ route('bookings.index', ['sales_id' => $user->id, 'status' => 'active']) }}"
       class="group block bg-blue-500/10 rounded-xl shadow p-4 border-l-4 border-blue-500 hover:shadow-md transition-all text-center border border-[var(--cc-border)]/30 hover:bg-blue-500/20">
        <p class="text-2xl font-bold text-blue-400">{{ $stats['active'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1 font-medium">Active</p>
        <p class="text-[10px] text-blue-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity">View active →</p>
    </a>
    <div class="bg-purple-500/10 rounded-xl shadow p-4 border-l-4 border-purple-500 text-center border border-[var(--cc-border)]/30">
        <p class="text-2xl font-bold text-purple-400">{{ $stats['total_bookings'] }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1 font-medium">Total Bookings</p>
        <p class="text-[10px] text-purple-400/50 mt-1">&nbsp;</p>
    </div>
    <div class="bg-indigo-500/10 rounded-xl shadow p-4 border-l-4 border-indigo-500 text-center border border-[var(--cc-border)]/30">
        <p class="text-lg font-bold text-indigo-400">{{ \App\Helpers\FormatHelper::formatIDR($stats['avg_per_booking']) }}</p>
        <p class="text-sm text-[var(--cc-text-muted)] mt-1 font-medium">Avg / Booking</p>
        <p class="text-[10px] text-indigo-400/50 mt-1">&nbsp;</p>
    </div>
</div>

{{-- Revenue Chart --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-[var(--cc-text)]">Revenue Trend</h3>
        <div class="flex gap-2 text-sm">
            @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly'] as $p => $label)
                <a href="{{ route('sales.performance', ['user' => $user->id, 'period' => $p]) }}"
                   class="{{ $period === $p ? 'bg-indigo-600 text-gray-900 font-semibold shadow-md shadow-indigo-600/10' : 'bg-[var(--cc-bg-muted)] text-[var(--cc-text-muted)] hover:bg-[var(--cc-surface)] border border-[var(--cc-border)]/50' }} px-3 py-1.5 rounded-lg font-medium text-xs transition-all">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>
    <canvas id="perfChart" height="80"></canvas>
</div>

{{-- Assigned Clients + Recent Bookings --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    {{-- Assigned Clients --}}
    <div class="cc-card rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-[var(--cc-text)]">Assigned Clients</h3>
            <a href="{{ route('clients.index') }}" class="text-blue-500 hover:text-blue-400 text-sm font-semibold">View all →</a>
        </div>
        <div class="space-y-2">
            @forelse($clients->take(8) as $client)
            <div class="flex items-center justify-between py-2 border-b border-[var(--cc-border)]/50 last:border-0">
                <div>
                    <a href="{{ route('clients.show', $client->id) }}"
                       class="text-blue-500 hover:underline font-semibold text-sm">
                        {{ $client->company_name }}
                    </a>
                    <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">{{ $client->industry }}</p>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$client->status" />
                    <p class="text-xs text-[var(--cc-text-muted)] mt-1 font-mono font-medium">{{ $client->bookings_count }} bookings</p>
                </div>
            </div>
            @empty
            <p class="text-[var(--cc-text-muted)] text-sm text-center py-4">No assigned clients</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Bookings --}}
    <div class="cc-card rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-[var(--cc-text)]">Recent Bookings</h3>
            <a href="{{ route('bookings.index', ['sales_id' => $user->id]) }}" class="text-blue-500 hover:text-blue-400 text-sm font-semibold">View all →</a>
        </div>
        <div class="space-y-2">
            @forelse($bookings->take(8) as $booking)
            <div class="flex items-center justify-between py-2 border-b border-[var(--cc-border)]/50 last:border-0 text-sm">
                <div>
                    <a href="{{ route('bookings.show', $booking->id) }}"
                       class="text-blue-500 hover:underline font-mono font-medium">
                        {{ $booking->booking_number }}
                    </a>
                    <div class="text-xs text-[var(--cc-text-muted)] mt-0.5">
                        <a href="{{ route('clients.show', $booking->client_id) }}" class="text-blue-500 hover:underline">
                            {{ $booking->client->company_name }}
                        </a>
                        · {{ $booking->pickup_datetime->format('d M') }}
                    </div>
                </div>
                <div class="text-right">
                    <x-status-badge :status="$booking->status" />
                    <p class="text-xs font-semibold text-[var(--cc-text)] mt-1">{{ \App\Helpers\FormatHelper::formatIDR($booking->price) }}</p>
                </div>
            </div>
            @empty
            <p class="text-[var(--cc-text-muted)] text-sm text-center py-4">No bookings yet</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
const chartData = @json($chartData);
const labels = chartData.map(d => d.label);
const values = chartData.map(d => parseFloat(d.value));

const ctx = document.getElementById('perfChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Revenue',
            data: values,
            backgroundColor: 'rgba(99, 102, 241, 0.7)', // Indigo tint
            borderColor: '#6366f1',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: { callbacks: { label: c => 'Rp ' + c.parsed.y.toLocaleString('id-ID') } }
        },
        scales: { y: { ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
    }
});
</script>
@endpush
@endsection
