@extends('layouts.app')

@section('header_title', $invoice->invoice_number)

@section('content')
<x-breadcrumb :items="[
    ['url' => route('dashboard'), 'label' => 'Dashboard'],
    ['url' => route('finance.index'), 'label' => 'Finance'],
    ['url' => '#', 'label' => $invoice->invoice_number],
]" />

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Invoice Card --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Header --}}
        <div class="cc-card rounded-lg shadow p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-[var(--cc-text)] font-mono">{{ $invoice->invoice_number }}</h2>
                    <p class="text-[var(--cc-text-muted)] text-sm mt-1">Created: {{ $invoice->created_at->format('d M Y') }}</p>
                </div>
                <x-status-badge :status="$invoice->status" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-[var(--cc-border)]/50 pt-6">
                <div>
                    <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide mb-2">Invoice To</p>
                    <a href="{{ route('clients.show', $invoice->client_id) }}"
                       class="text-blue-500 hover:underline font-semibold block">
                        {{ $invoice->client->company_name }}
                    </a>
                    <p class="text-sm text-[var(--cc-text-muted)] mt-1">{{ $invoice->client->pic_name }}</p>
                    <p class="text-sm text-[var(--cc-text-muted)]">{{ $invoice->client->phone }}</p>
                    <p class="text-sm text-[var(--cc-text-muted)]">{{ $invoice->client->email }}</p>
                </div>
                <div>
                    <p class="text-xs text-[var(--cc-text-muted)] uppercase tracking-wide mb-2">Related Booking</p>
                    @if($invoice->booking)
                        <a href="{{ route('bookings.show', $invoice->booking->id) }}"
                           class="text-blue-500 hover:underline font-semibold block font-mono">
                            {{ $invoice->booking->booking_number }}
                        </a>
                        @if($invoice->booking->vehicle)
                            <a href="{{ route('fleet.show', $invoice->booking->vehicle_id) }}" class="text-sm text-blue-500 hover:underline">
                                🚌 {{ $invoice->booking->vehicle->plate_number }}
                            </a>
                        @endif
                        @if($invoice->booking->sales)
                            <p class="text-sm text-[var(--cc-text-muted)] mt-1">
                                Sales:
                                @if(auth()->user()->isGM())
                                    <a href="{{ route('sales.performance', $invoice->booking->sales_id) }}" class="text-blue-500 hover:underline">
                                        {{ $invoice->booking->sales->name }}
                                    </a>
                                @else
                                    <span class="text-[var(--cc-text)] font-semibold">{{ $invoice->booking->sales->name }}</span>
                                @endif
                            </p>
                        @endif
                    @else
                        <p class="text-[var(--cc-text-muted)] text-sm">No linked booking</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Line Items --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Invoice Details</h3>
            <table class="w-full text-sm">
                <thead class="border-b border-[var(--cc-border)]/50">
                    <tr class="text-[var(--cc-text-muted)]">
                        <th class="text-left py-2">Description</th>
                        <th class="text-right py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-b border-[var(--cc-border)]/50">
                        <td class="py-3 text-[var(--cc-text)]">Transportation / Rental Service
                            @if($invoice->notes)
                                <p class="text-xs text-[var(--cc-text-muted)] mt-1">{{ $invoice->notes }}</p>
                            @endif
                        </td>
                        <td class="py-3 text-right font-semibold text-[var(--cc-text)]">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                    </tr>
                </tbody>
                <tfoot class="border-t border-[var(--cc-border)]/50">
                    <tr>
                        <td class="py-3 font-bold text-[var(--cc-text)]">Total Due</td>
                        <td class="py-3 text-right font-bold text-xl text-indigo-400">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Payment History --}}
        @if($invoice->payments->count())
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Payment Records</h3>
            <table class="w-full text-sm">
                <thead class="border-b border-[var(--cc-border)]/50">
                    <tr class="text-[var(--cc-text-muted)]">
                        <th class="text-left py-2">Date</th>
                        <th class="text-left py-2">Method</th>
                        <th class="text-left py-2">Reference</th>
                        <th class="text-right py-2">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments as $payment)
                    <tr class="border-b border-[var(--cc-border)]/50">
                        <td class="py-2 text-[var(--cc-text)]">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                        <td class="py-2 capitalize text-[var(--cc-text)]">{{ str_replace('_', ' ', $payment->method) }}</td>
                        <td class="py-2 font-mono text-[var(--cc-text-muted)]">{{ $payment->payment_number }}</td>
                        <td class="py-2 text-right font-semibold text-emerald-400">{{ \App\Helpers\FormatHelper::formatIDR($payment->amount) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Payment Status --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Payment Status</h3>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-[var(--cc-text-muted)] text-sm">Invoice Amount</span>
                    <span class="font-semibold text-[var(--cc-text)]">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount) }}</span>
                </div>
                @php $paidTotal = $invoice->payments->sum('amount'); @endphp
                <div class="flex justify-between">
                    <span class="text-[var(--cc-text-muted)] text-sm">Amount Paid</span>
                    <span class="font-semibold text-emerald-400">{{ \App\Helpers\FormatHelper::formatIDR($paidTotal) }}</span>
                </div>
                <div class="flex justify-between border-t border-[var(--cc-border)]/50 pt-3">
                    <span class="text-[var(--cc-text)] font-semibold text-sm">Outstanding</span>
                    <span class="font-bold text-rose-400">{{ \App\Helpers\FormatHelper::formatIDR($invoice->amount - $paidTotal) }}</span>
                </div>
            </div>
        </div>

        {{-- Important Dates --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Important Dates</h3>
            <div class="space-y-3 text-sm">
                <div>
                    <p class="text-[var(--cc-text-muted)]">Invoice Date</p>
                    <p class="font-semibold text-[var(--cc-text)]">{{ $invoice->created_at->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-[var(--cc-text-muted)]">Due Date</p>
                    <p class="font-semibold {{ \Carbon\Carbon::parse($invoice->due_date)->isPast() && $invoice->status !== 'paid' ? 'text-rose-400' : 'text-[var(--cc-text)]' }}">
                        {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}
                    </p>
                </div>
                @if($invoice->paid_at)
                <div>
                    <p class="text-[var(--cc-text-muted)]">Paid On</p>
                    <p class="font-semibold text-emerald-400">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="cc-card rounded-lg shadow p-6">
            <h3 class="font-semibold text-[var(--cc-text)] mb-4">Actions</h3>
            <div class="space-y-2">
                <button onclick="window.print()" class="w-full bg-indigo-600 text-gray-900 py-2.5 rounded-xl hover:bg-indigo-500 font-semibold text-sm transition-all shadow-lg shadow-indigo-600/20">
                    🖨 Print Invoice
                </button>
                <a href="{{ route('finance.index') }}" class="block w-full bg-[var(--cc-bg-muted)] border border-[var(--cc-border)] text-[var(--cc-text)] py-2.5 rounded-xl hover:bg-[var(--cc-surface)] text-center font-semibold text-sm transition-all">
                    ← Back to Finance
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
