@extends('layouts.app')

@section('header_title', 'Opportunities')

@section('content')
<div class="space-y-6 font-sans">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-[#003887] text-[28px]">handshake</span>
            <div>
                <h2 class="text-xl font-bold text-slate-900">Sales Opportunities</h2>
                <p class="text-xs text-slate-400">Daftar peluang penjualan B2B</p>
            </div>
        </div>
        <a href="{{ route('opportunities.create') }}" class="flex items-center gap-2 bg-[#003887] hover:bg-secondary text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition-colors">
            <span class="material-symbols-outlined text-[18px]">add</span> Opportunity Baru
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="cc-card rounded-2xl shadow-sm border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Stage</label>
            <select name="stage" class="rounded-xl border-slate-300 text-sm focus:border-[#003887] focus:ring-[#003887]">
                <option value="">Semua Stage</option>
                @foreach(['prospecting','proposal','negotiation','won','lost'] as $st)
                <option value="{{ $st }}" @selected(request('stage')===$st)>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        @isset($salesUsers)
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sales</label>
            <select name="sales_id" class="rounded-xl border-slate-300 text-sm focus:border-[#003887] focus:ring-[#003887]">
                <option value="">Semua Sales</option>
                @foreach($salesUsers as $s)
                <option value="{{ $s->id }}" @selected((string)request('sales_id')===(string)$s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endisset
        <button class="flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold px-4 py-2 rounded-xl transition-colors">
            <span class="material-symbols-outlined text-[18px]">filter_alt</span> Filter
        </button>
    </form>

    {{-- Table --}}
    <div class="cc-card rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm resizable-table" data-table-id="opportunities-table">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="px-5 py-3">Opportunity</th>
                        <th class="px-5 py-3">Client</th>
                        <th class="px-5 py-3">Sales</th>
                        <th class="px-5 py-3">Stage</th>
                        <th class="px-5 py-3 text-right">Estimasi</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opportunities as $op)
                    @php
                        $stageColors = [
                            'prospecting' => 'bg-slate-100 text-slate-700',
                            'proposal'    => 'bg-blue-100 text-blue-700',
                            'negotiation' => 'bg-amber-100 text-amber-700',
                            'won'         => 'bg-emerald-100 text-emerald-700',
                            'lost'        => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <a href="{{ route('opportunities.show', $op->id) }}" class="font-semibold text-slate-800 hover:text-[#003887]">{{ $op->title ?? $op->product->name ?? 'Opportunity #'.$op->id }}</a>
                        </td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $op->client->company_name ?? '—' }}</td>
                        <td class="px-5 py-3.5 text-slate-600">{{ $op->sales->name ?? '—' }}</td>
                        <td class="px-5 py-3.5">
                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[11px] font-bold {{ $stageColors[$op->stage] ?? 'bg-slate-100 text-slate-700' }}">{{ ucfirst($op->stage) }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">{{ \App\Helpers\FormatHelper::formatIDR($op->estimated_value ?? 0) }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <a href="{{ route('opportunities.show', $op->id) }}" class="text-[#003887] hover:underline text-xs font-semibold">Detail →</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl block mb-2 opacity-40">inbox</span>
                        Belum ada opportunity
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($opportunities->hasPages())
        <div class="px-5 py-3 border-t border-slate-100">{{ $opportunities->links() }}</div>
        @endif
    </div>
</div>
@endsection
