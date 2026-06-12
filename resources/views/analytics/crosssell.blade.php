@extends('layouts.app')

@section('header_title', 'Cross-sell Analysis')

@section('content')
<div class="space-y-6">

    {{-- Sub-nav --}}
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('analytics.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium cc-card border border-white/8 text-slate-500 hover:cc-card">Overview</a>
        <a href="{{ route('analytics.pipeline') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium cc-card border border-white/8 text-slate-500 hover:cc-card">Pipeline Funnel</a>
        <a href="{{ route('analytics.crosssell') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium bg-blue-700 text-gray-900">Cross-sell</a>
        <a href="{{ route('analytics.sales') }}"
           class="px-4 py-2 rounded-lg text-sm font-medium cc-card border border-white/8 text-slate-500 hover:cc-card">Sales Performance</a>
    </div>

    @php
        $totalClients = $shortTermOnly->count() + $longTermOnly->count() + $evoucherOnly->count()
            + $shortAndLong->count() + $shortAndEv->count() + $longAndEv->count()
            + $allThree->count() + $none->count();
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Venn Diagram (SVG) --}}
        <div class="cc-card rounded-xl border border-white/8 shadow-sm p-6">
            <h3 class="font-semibold text-slate-100 mb-2">Distribusi Produk per Klien</h3>
            <p class="text-xs text-slate-500 mb-6">Berdasarkan opportunity won. Total klien: {{ $totalClients }}</p>

            <div class="flex justify-center">
                <svg viewBox="0 0 400 340" class="w-full max-w-sm" xmlns="http://www.w3.org/2000/svg">
                    <!-- Short Term circle (top-left) -->
                    <circle cx="155" cy="140" r="110" fill="#3b82f6" fill-opacity="0.25" stroke="#3b82f6" stroke-width="2"/>
                    <!-- Long Term circle (top-right) -->
                    <circle cx="245" cy="140" r="110" fill="#10b981" fill-opacity="0.25" stroke="#10b981" stroke-width="2"/>
                    <!-- E-Voucher circle (bottom) -->
                    <circle cx="200" cy="215" r="110" fill="#f59e0b" fill-opacity="0.25" stroke="#f59e0b" stroke-width="2"/>

                    <!-- Labels for circles -->
                    <text x="80" y="80" fill="#2563eb" font-size="13" font-weight="600" text-anchor="middle">Short Term</text>
                    <text x="80" y="96" fill="#2563eb" font-size="11" text-anchor="middle">({{ $shortTermOnly->count() + $shortAndLong->count() + $shortAndEv->count() + $allThree->count() }})</text>

                    <text x="320" y="80" fill="#059669" font-size="13" font-weight="600" text-anchor="middle">Long Term</text>
                    <text x="320" y="96" fill="#059669" font-size="11" text-anchor="middle">({{ $longTermOnly->count() + $shortAndLong->count() + $longAndEv->count() + $allThree->count() }})</text>

                    <text x="200" y="325" fill="#b45309" font-size="13" font-weight="600" text-anchor="middle">E-Voucher</text>
                    <text x="200" y="341" fill="#b45309" font-size="11" text-anchor="middle">({{ $evoucherOnly->count() + $shortAndEv->count() + $longAndEv->count() + $allThree->count() }})</text>

                    <!-- Count in each region -->
                    <!-- Short Term only -->
                    <text x="108" y="125" fill="#1e40af" font-size="22" font-weight="700" text-anchor="middle">{{ $shortTermOnly->count() }}</text>
                    <text x="108" y="143" fill="#1e40af" font-size="10" text-anchor="middle">only</text>

                    <!-- Long Term only -->
                    <text x="292" y="125" fill="#065f46" font-size="22" font-weight="700" text-anchor="middle">{{ $longTermOnly->count() }}</text>
                    <text x="292" y="143" fill="#065f46" font-size="10" text-anchor="middle">only</text>

                    <!-- E-Voucher only -->
                    <text x="200" y="268" fill="#92400e" font-size="22" font-weight="700" text-anchor="middle">{{ $evoucherOnly->count() }}</text>
                    <text x="200" y="284" fill="#92400e" font-size="10" text-anchor="middle">only</text>

                    <!-- Short + Long -->
                    <text x="200" y="115" fill="#374151" font-size="16" font-weight="600" text-anchor="middle">{{ $shortAndLong->count() }}</text>

                    <!-- Short + EV -->
                    <text x="148" y="205" fill="#374151" font-size="16" font-weight="600" text-anchor="middle">{{ $shortAndEv->count() }}</text>

                    <!-- Long + EV -->
                    <text x="252" y="205" fill="#374151" font-size="16" font-weight="600" text-anchor="middle">{{ $longAndEv->count() }}</text>

                    <!-- All Three (center) -->
                    <text x="200" y="165" fill="#111827" font-size="20" font-weight="700" text-anchor="middle">{{ $allThree->count() }}</text>
                    <text x="200" y="181" fill="#6b7280" font-size="10" text-anchor="middle">semua</text>
                </svg>
            </div>

            {{-- Legend --}}
            <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2 p-2 bg-blue-900/30 rounded">
                    <span class="w-3 h-3 rounded-full bg-blue-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Short Term Only: <b>{{ $shortTermOnly->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-green-900/30 rounded">
                    <span class="w-3 h-3 rounded-full bg-green-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Long Term Only: <b>{{ $longTermOnly->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-yellow-900/20 rounded">
                    <span class="w-3 h-3 rounded-full bg-yellow-500 flex-shrink-0"></span>
                    <span class="text-slate-200">E-Voucher Only: <b>{{ $evoucherOnly->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-indigo-900/30 rounded">
                    <span class="w-3 h-3 rounded-full bg-indigo-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Short + Long: <b>{{ $shortAndLong->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-purple-900/30 rounded">
                    <span class="w-3 h-3 rounded-full bg-purple-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Short + EV: <b>{{ $shortAndEv->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-teal-900/30 rounded">
                    <span class="w-3 h-3 rounded-full bg-teal-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Long + EV: <b>{{ $longAndEv->count() }}</b></span>
                </div>
                <div class="flex items-center gap-2 p-2 bg-orange-900/20 rounded col-span-2">
                    <span class="w-3 h-3 rounded-full bg-orange-500 flex-shrink-0"></span>
                    <span class="text-slate-200">Semua Kategori: <b>{{ $allThree->count() }}</b> &bull;
                        Belum Ada Produk: <b>{{ $none->count() }}</b></span>
                </div>
            </div>
        </div>

        {{-- Cross-sell Opportunity Insights --}}
        <div class="cc-card rounded-xl border border-white/8 shadow-sm p-6">
            <h3 class="font-semibold text-slate-100 mb-4">Peluang Cross-sell</h3>

            @php
                $crossSellOpportunity = $shortTermOnly->count() + $longTermOnly->count() + $evoucherOnly->count();
            @endphp

            <div class="space-y-4">
                <div class="p-4 bg-blue-500/10 rounded-xl border border-blue-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-blue-400">Short Term Only</p>
                            <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">Potensial upgrade ke Long Term / E-Voucher</p>
                        </div>
                        <span class="text-3xl font-bold text-blue-400">{{ $shortTermOnly->count() }}</span>
                    </div>
                </div>

                <div class="p-4 bg-emerald-500/10 rounded-xl border border-emerald-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-emerald-400">Long Term Only</p>
                            <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">Potensial tambah Short Term / E-Voucher</p>
                        </div>
                        <span class="text-3xl font-bold text-emerald-400">{{ $longTermOnly->count() }}</span>
                    </div>
                </div>

                <div class="p-4 bg-amber-500/10 rounded-xl border border-amber-500/20">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-amber-400">E-Voucher Only</p>
                            <p class="text-xs text-[var(--cc-text-muted)] mt-0.5">Potensial upgrade ke layanan reguler</p>
                        </div>
                        <span class="text-3xl font-bold text-amber-400">{{ $evoucherOnly->count() }}</span>
                    </div>
                </div>

                <div class="p-4 cc-card rounded-lg border border-white/8">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-slate-100">Total Peluang Cross-sell</p>
                            <p class="text-xs text-slate-500 mt-0.5">Klien dengan hanya 1 kategori produk</p>
                        </div>
                        <span class="text-3xl font-bold text-slate-100">{{ $crossSellOpportunity }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Client Table --}}
    <div class="cc-card rounded-xl border border-white/8 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-white/5 flex items-center justify-between flex-wrap gap-3">
            <h3 class="font-semibold text-slate-100">Daftar Klien per Kategori Produk</h3>
            <button onclick="exportTable()" class="flex items-center gap-2 px-3 py-1.5 text-sm bg-green-600 text-gray-900 rounded-lg hover:bg-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </button>
        </div>

        <div class="overflow-x-auto">
            <table id="crosssellTable" class="w-full text-sm">
                <thead class="cc-card text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Perusahaan</th>
                        <th class="px-4 py-3 text-center">Short Term</th>
                        <th class="px-4 py-3 text-center">Long Term</th>
                        <th class="px-4 py-3 text-center">E-Voucher</th>
                        <th class="px-4 py-3 text-center">Tier</th>
                        <th class="px-4 py-3 text-left">Sales</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($clientTable as $row)
                    @php
                        $client   = $row['client'];
                        $hasShort = $row['short_term'];
                        $hasLong  = $row['long_term'];
                        $hasEv    = $row['evoucher'];
                        $count    = (int)$hasShort + (int)$hasLong + (int)$hasEv;
                    @endphp
                    <tr class="hover:cc-card">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-100">{{ $client->company_name }}</div>
                            <div class="text-xs text-slate-500">{{ $client->pic_name ?? '' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($hasShort)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-900/40 text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="text-gray-300 text-lg">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($hasLong)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-green-900/40 text-green-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="text-gray-300 text-lg">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($hasEv)
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-yellow-900/30 text-yellow-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            @else
                            <span class="text-gray-300 text-lg">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $tierColors = [
                                    'platinum' => 'bg-purple-900/40 text-purple-700',
                                    'gold'     => 'bg-yellow-900/30 text-yellow-700',
                                    'silver'   => 'bg-gray-200 text-slate-500',
                                    'bronze'   => 'bg-orange-900/30 text-orange-700',
                                ];
                                $tier = $client->tier ?? 'bronze';
                            @endphp
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $tierColors[$tier] ?? 'bg-gray-100/10 text-slate-500' }}">
                                {{ ucfirst($tier) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-200">
                            {{ optional($client->assignedSales)->name ?? '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">Belum ada data klien.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function exportTable() {
    const table = document.getElementById('crosssellTable');
    const rows  = [...table.querySelectorAll('tr')];
    const csv   = rows.map(row => {
        const cells = [...row.querySelectorAll('th, td')];
        return cells.map(c => '"' + c.innerText.replace(/"/g, '""').trim() + '"').join(',');
    }).join('\n');

    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = 'crosssell_analysis_{{ now()->format("Ymd") }}.csv';
    a.click();
    URL.revokeObjectURL(url);
}
</script>
@endpush
