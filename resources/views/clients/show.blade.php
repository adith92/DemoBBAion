@extends('layouts.app')

@section('header_title', $client->company_name)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('clients.index'), 'label' => 'Clients'],
    ['url' => '#', 'label' => $client->company_name],
]" />

{{-- Hero --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-[var(--cc-text)]">{{ $client->company_name }}</h2>
            <p class="text-[var(--cc-text-muted)] mt-1">{{ $client->industry }} · {{ $client->address }}</p>
        </div>
        <x-status-badge :status="$client->status" />
    </div>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-[var(--cc-border)]">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">PIC Name</p>
            <a href="mailto:{{ $client->email }}" class="font-semibold text-cc-cyan hover:underline">{{ $client->pic_name }}</a>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Phone</p>
            <p class="font-semibold text-[var(--cc-text)]">{{ $client->phone }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Email</p>
            <p class="font-semibold text-[var(--cc-text)] text-sm">{{ $client->email }}</p>
        </div>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--cc-text-muted)]">Assigned Sales</p>
            @if($client->assignedSales)
                <a href="{{ route('sales.performance', $client->assignedSales->id) }}"
                   class="font-semibold text-cc-cyan hover:underline">
                    {{ $client->assignedSales->name }}
                </a>
            @else
                <p class="text-[var(--cc-text-faint)]">Unassigned</p>
            @endif
        </div>
    </div>
</div>

{{-- KPI Cards --}}
<div class="mb-6">
    <h3 class="text-xs font-bold uppercase tracking-wider text-[var(--cc-text-muted)] mb-3">Rangkuman Deal & Transaksi</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
        {{-- Opportunity KPIs --}}
        <div class="kpi-card kpi-cyan">
            <p class="kpi-label">Jumlah Transaksi (Won)</p>
            <p class="kpi-value text-sky-500">{{ $stats['won_deals_count'] }} won</p>
        </div>

        <div class="kpi-card kpi-blue">
            <p class="kpi-label">Nilai Transaksi (Won)</p>
            <p class="kpi-value text-blue-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['won_deals_sum']) }}</p>
        </div>

        {{-- Invoice KPIs --}}
        <a href="{{ route('finance.index', ['status' => 'paid']) }}" class="kpi-card kpi-emerald block group">
            <p class="kpi-label">Total Paid (Invoice)</p>
            <p class="kpi-value text-emerald-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_spend']) }}</p>
            <p class="text-[9px] font-bold mt-1 text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <div class="kpi-card kpi-gold">
            <p class="kpi-label">Pending (Invoice)</p>
            <p class="kpi-value text-amber-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_pending']) }}</p>
        </div>

        <div class="kpi-card kpi-red">
            <p class="kpi-label">Overdue (Invoice)</p>
            <p class="kpi-value text-red-500">{{ \App\Helpers\FormatHelper::formatIDR($stats['total_overdue']) }}</p>
        </div>
    </div>
</div>

{{-- Opportunity History --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-[var(--cc-text)] mb-4">Opportunity & Deal History</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm dark-table resizable-table">
            <thead>
                <tr>
                    <th class="text-left">Opp # / Title</th>
                    <th class="text-left">Product</th>
                    <th class="text-left">Stage</th>
                    <th class="text-right">Value</th>
                    <th class="text-left pl-6">Close Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->opportunities->sortByDesc('created_at') as $opp)
                @php
                    $closeMonth = $opp->expected_close_date ? $opp->expected_close_date->format('Y-m') : '';
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('pipeline.index', ['highlight_id' => $opp->id, 'filter_month' => $closeMonth]) }}" 
                           class="text-cc-cyan hover:underline font-semibold block">
                            {{ $opp->opp_number }}
                        </a>
                        <span class="text-xs text-[var(--cc-text-muted)]">{{ $opp->title }}</span>
                    </td>
                    <td class="text-[var(--cc-text-muted)]">{{ $opp->product->name ?? '—' }}</td>
                    <td>
                        <span class="status-badge status-{{ $opp->stage_color }}">
                            {{ $opp->stage_label }}
                        </span>
                    </td>
                    <td class="text-right font-semibold text-[var(--cc-text)]">
                        {{ \App\Helpers\FormatHelper::formatIDR($opp->stage === 'won' ? $opp->final_value : $opp->estimated_value) }}
                    </td>
                    <td class="text-[var(--cc-text-muted)] pl-6">
                        @if($opp->stage === 'won' && $opp->actual_close_date)
                            {{ $opp->actual_close_date->format('d M Y') }}
                        @elseif($opp->expected_close_date)
                            {{ $opp->expected_close_date->format('d M Y') }} (Est)
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-4 text-center text-[var(--cc-text-muted)]">No opportunities yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Invoice Summary --}}
<div class="cc-card rounded-xl shadow p-6 mb-6">
    <h3 class="text-lg font-semibold text-[var(--cc-text)] mb-4">Invoice History</h3>
    <div class="overflow-x-auto">
        <table class="w-full text-sm dark-table resizable-table">
            <thead>
                <tr>
                    <th class="text-left">Invoice #</th>
                    <th class="text-left">Due Date</th>
                    <th class="text-left">Status</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($client->invoices->sortByDesc('created_at')->take(10) as $invoice)
                <tr>
                    <td>
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="text-cc-cyan hover:underline font-mono">
                            {{ $invoice->invoice_number }}
                        </a>
                    </td>
                    <td class="text-[var(--cc-text-muted)]">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                    <td><x-status-badge :status="$invoice->status" /></td>
                    <td class="text-right font-semibold text-[var(--cc-text)]">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-4 text-center text-[var(--cc-text-muted)]">No invoices yet</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Meeting Logs --}}
@if($client->meetingLogs->count())
<div class="cc-card rounded-xl shadow p-6">
    <h3 class="text-lg font-semibold text-[var(--cc-text)] mb-4">Meeting Log</h3>
    <div class="space-y-3">
        @foreach($client->meetingLogs->sortByDesc('meeting_date')->take(5) as $meeting)
        <div class="border-l-2 border-blue-400 pl-4 py-2 bg-[var(--cc-surface)] rounded-r-lg">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-[var(--cc-text)]">{{ \Carbon\Carbon::parse($meeting->meeting_date)->format('d M Y') }}</p>
                    <p class="text-[13px] text-[var(--cc-text-muted)] mt-1">{{ $meeting->outcome }}</p>
                    @if($meeting->notes)
                        <p class="text-xs text-[var(--cc-text-faint)] mt-1 italic">{{ $meeting->notes }}</p>
                    @endif
                </div>
                <x-status-badge :status="$meeting->status" />
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
