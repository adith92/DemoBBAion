@extends('layouts.app')

@section('header_title', 'Finance Dashboard')

@section('content')
<div class="space-y-6">

    <div id="widget-kpi-row" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('finance.index', ['filter' => 'today']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-blue-500 hover:shadow-md hover:bg-blue-50 transition-all">
            <p class="text-gray-500 text-sm">Today Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($todayRevenue) }}</p>
            <p class="text-xs text-blue-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <a href="{{ route('finance.index', ['filter' => 'month']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-green-500 hover:shadow-md hover:bg-green-50 transition-all">
            <p class="text-gray-500 text-sm">Month Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
            <p class="text-xs text-green-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View invoices →</p>
        </a>

        <a href="{{ route('finance.index', ['status' => 'sent']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-yellow-500 hover:shadow-md hover:bg-yellow-50 transition-all">
            <p class="text-gray-500 text-sm">Pending Invoice</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $pendingInvoice }}</p>
            <p class="text-xs text-yellow-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">View pending →</p>
        </a>

        <a href="{{ route('finance.index', ['status' => 'overdue']) }}" class="group block cc-card rounded-lg shadow p-6 border-l-4 border-red-500 hover:shadow-md hover:bg-red-50 transition-all">
            <p class="text-gray-500 text-sm">Outstanding</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ \App\Helpers\FormatHelper::formatIDR($outstanding) }}</p>
            <p class="text-xs text-red-600 mt-2 opacity-0 group-hover:opacity-100 transition-opacity">{{ $overdueCount }} overdue invoices →</p>
        </a>
    </div>

    <div id="widget-finance-summary" class="cc-card rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900">Financial Summary</h3>
        <p class="text-gray-500 mt-2 text-sm">Total Paid This Month: <strong class="text-green-600">{{ \App\Helpers\FormatHelper::formatIDR($paidThisMonth) }}</strong></p>
    </div>

    @if($overdueInvoices->count())
    <div id="widget-finance-overdue" class="cc-card rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">⚠️ Overdue Invoices</h3>
            <a href="{{ route('finance.index', ['status' => 'overdue']) }}" class="text-red-600 hover:text-red-800 text-sm font-medium">View all →</a>
        </div>
        <div class="space-y-2">
            @foreach($overdueInvoices as $inv)
            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                <div>
                    <a href="{{ route('invoices.show', $inv->id) }}" class="text-red-700 hover:text-red-900 font-medium">
                        {{ $inv->invoice_number }}
                    </a>
                    <span class="text-gray-600 text-sm ml-2">—</span>
                    <a href="{{ route('clients.show', $inv->client_id) }}" class="text-blue-600 hover:underline text-sm ml-1">
                        {{ $inv->client->company_name }}
                    </a>
                </div>
                <span class="font-semibold text-red-700">{{ \App\Helpers\FormatHelper::formatIDR($inv->amount) }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
