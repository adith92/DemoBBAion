@extends('layouts.app')

@section('header_title', 'Sales Dashboard')

@section('content')
<div x-data="salesDashboard()">
<x-dashboard-grid :saved-layout="auth()->user()->dashboard_settings">

    {{-- Row 1: Target & Revenue KPIs (4 cards, each gs-w=3) --}}
    
    {{-- Target vs Achievement --}}
    <div class="grid-stack-item" gs-id="w-revenue-target" gs-x="0" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-indigo-500 h-full relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">track_changes</span>
                </div>
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Monthly Target</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($targetRevenue) }}</p>
                
                @php
                    $achievementPct = $targetRevenue > 0 ? min(100, round(($monthRevenue / $targetRevenue) * 100)) : 0;
                @endphp
                <div class="mt-4">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-[var(--cc-text-muted)] font-medium">Achievement</span>
                        <span class="font-bold text-indigo-500">{{ $achievementPct }}%</span>
                    </div>
                    <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-1.5">
                        <div class="bg-indigo-500 h-1.5 rounded-full shadow-[0_0_8px_rgba(99,102,241,0.6)]" style="width: {{ $achievementPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pipeline Value --}}
    <div class="grid-stack-item" gs-id="w-pipeline-value" gs-x="3" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 border-l-4 border-fuchsia-500 h-full relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-symbols-outlined text-[100px]">all_inclusive</span>
                </div>
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Total Pipeline Value</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($pipelineValue) }}</p>
                <p class="text-xs text-[var(--cc-text-muted)] mt-4">Potensi deal yang sedang berjalan</p>
            </div>
        </div>
    </div>

    {{-- Revenue Month --}}
    <div class="grid-stack-item" gs-id="w-revenue-month" gs-x="6" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('revenue', 'month', 'My Revenue Month')" class="cc-card rounded-xl shadow p-5 border-l-4 border-emerald-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(16,185,129,0.2)] active:scale-[0.98] relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500 text-emerald-500">
                    <span class="material-symbols-outlined text-[100px]">payments</span>
                </div>
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Month</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($monthRevenue) }}</p>
                <p class="text-xs text-emerald-500 mt-4 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> Klik untuk rincian data</p>
            </div>
        </div>
    </div>

    {{-- Revenue Year --}}
    <div class="grid-stack-item" gs-id="w-revenue-year" gs-x="9" gs-y="0" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('revenue', 'year', 'My Revenue Year')" class="cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(59,130,246,0.2)] active:scale-[0.98] relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-500 text-blue-500">
                    <span class="material-symbols-outlined text-[100px]">account_balance</span>
                </div>
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Revenue Year</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2">{{ \App\Helpers\FormatHelper::formatIDR($yearRevenue) }}</p>
                <p class="text-xs text-blue-500 mt-4 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> Klik untuk rincian data</p>
            </div>
        </div>
    </div>

    {{-- Row 2: Operational KPIs (All Clickable to Modals) --}}
    <div class="grid-stack-item" gs-id="w-active-bookings" gs-x="0" gs-y="2" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('bookings', null, 'Active Bookings')" class="cc-card rounded-xl shadow p-5 border-l-4 border-yellow-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(234,179,8,0.2)] active:scale-[0.98]">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Active Bookings</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2 flex items-center gap-2">
                    {{ $activeBookings }}
                </p>
                <p class="text-xs text-yellow-600 dark:text-yellow-500 mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> View bookings details →</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-my-clients" gs-x="3" gs-y="2" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('clients', null, 'My Clients')" class="cc-card rounded-xl shadow p-5 border-l-4 border-orange-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(249,115,22,0.2)] active:scale-[0.98]">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">My Clients</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2 flex items-center gap-2">
                    {{ $myClients }}
                </p>
                <p class="text-xs text-orange-600 dark:text-orange-500 mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> View client details →</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-available-fleet" gs-x="6" gs-y="2" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('fleet', null, 'Fleet / Armada')" class="cc-card rounded-xl shadow p-5 border-l-4 border-emerald-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(16,185,129,0.2)] active:scale-[0.98]">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Fleet / Armada</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2 flex items-center gap-2">
                    <span class="material-symbols-outlined">local_shipping</span> Connected
                </p>
                <p class="text-xs text-emerald-600 dark:text-emerald-500 mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> View fleet status →</p>
            </div>
        </div>
    </div>

    <div class="grid-stack-item" gs-id="w-available-drivers" gs-x="9" gs-y="2" gs-w="3" gs-h="2">
        <div class="grid-stack-item-content">
            <div @click="openBreakdown('drivers', null, 'Drivers / Supir')" class="cc-card rounded-xl shadow p-5 border-l-4 border-blue-500 h-full cursor-pointer transition-all duration-200 hover:scale-[1.02] hover:shadow-[0_0_15px_rgba(59,130,246,0.2)] active:scale-[0.98]">
                <p class="text-[var(--cc-text-muted)] text-xs uppercase tracking-wider font-semibold">Drivers / Supir</p>
                <p class="text-2xl font-bold text-[var(--cc-text)] mt-2 flex items-center gap-2">
                    <span class="material-symbols-outlined">badge</span> Connected
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-500 mt-2 flex items-center gap-1"><span class="material-symbols-outlined text-[14px]">ads_click</span> View driver status →</p>
            </div>
        </div>
    </div>

    {{-- Row 3: Charts --}}
    <div class="grid-stack-item" gs-id="w-funnel-chart" gs-x="0" gs-y="4" gs-w="6" gs-h="5">
        <div class="grid-stack-item-content">
            <div class="cc-card rounded-xl shadow p-5 h-full relative group">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-semibold" style="color:var(--cc-text)">Opportunities Funnel</h3>
                    <span class="text-[10px] bg-slate-200 dark:bg-slate-800 px-2 py-1 rounded text-[var(--cc-text-muted)]">Click bar to view details</span>
                </div>
                <div id="funnelChart" style="min-height:280px" class="cursor-pointer"></div>
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

</x-dashboard-grid>

    <!-- Global Breakdown Modal -->
    <div x-show="showModal" 
         class="fixed inset-0 z-[100] overflow-y-auto" 
         x-cloak
         @keydown.escape.window="showModal = false">
        <!-- Backdrop -->
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/60 backdrop-blur-md transition-opacity"
             @click="showModal = false"></div>

        <!-- Modal Content -->
        <div class="flex min-h-screen items-center justify-center p-4">
            <div x-show="showModal"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-2xl bg-[var(--cc-surface)] border border-[var(--cc-border)] p-6 shadow-2xl transition-all w-full max-w-5xl max-h-[85vh] flex flex-col">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-[var(--cc-border)] pb-4 mb-4">
                    <h3 class="text-lg font-bold text-[var(--cc-text)] flex items-center gap-2">
                        <span class="material-symbols-outlined text-indigo-500" x-html="modalIcon"></span>
                        <span x-text="title"></span>
                    </h3>
                    <button @click="showModal = false" class="text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition-colors">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <!-- Content Area (Scrollable) -->
                <div class="flex-1 overflow-y-auto space-y-4 min-h-[200px] max-h-[60vh] pr-2">
                    <!-- Loading State -->
                    <div x-show="loading" class="flex flex-col items-center justify-center py-12 space-y-3">
                        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-500"></div>
                        <p class="text-[var(--cc-text-muted)] text-sm">Menghubungkan & Memuat data...</p>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && items.length === 0" class="text-center py-12">
                        <span class="material-symbols-outlined text-5xl text-[var(--cc-text-muted)] mb-2">assignment_late</span>
                        <p class="text-[var(--cc-text)] font-semibold">Tidak Ada Data</p>
                        <p class="text-[var(--cc-text-muted)] text-sm mt-1">Belum ada data yang tercatat untuk kategori ini.</p>
                    </div>

                    <!-- Dynamic Layout based on type -->
                    
                    <!-- REVENUE / OPPORTUNITIES CARDS -->
                    <div x-show="!loading && items.length > 0 && (breakdownType === 'revenue' || breakdownType === 'opportunities')" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="item in items" :key="item.id">
                            <a :href="'/pipeline?highlight_id=' + item.id" class="flex flex-col justify-between p-4 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface-secondary)] hover:border-indigo-500/50 hover:bg-indigo-500/5 transition-all duration-200 cursor-pointer group">
                                <div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-xs font-mono font-semibold px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-500" x-text="item.opp_number"></span>
                                        <span class="text-xs text-[var(--cc-text-muted)]" x-text="item.actual_close_date || item.stage"></span>
                                    </div>
                                    <h4 class="font-bold text-[var(--cc-text)] text-sm mb-1 line-clamp-1 group-hover:text-indigo-500 transition-colors" x-text="item.title"></h4>
                                    <p class="text-xs text-[var(--cc-text-muted)] mb-2 flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">business</span>
                                        <span x-text="item.client_name"></span>
                                    </p>
                                </div>
                                <div class="flex items-center justify-between border-t border-[var(--cc-border)] pt-2 mt-2">
                                    <span class="text-xs text-[var(--cc-text-muted)] flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">person</span>
                                        <span x-text="item.sales_name"></span>
                                    </span>
                                    <span class="font-bold text-indigo-500 text-sm" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.value)"></span>
                                </div>
                            </a>
                        </template>
                    </div>

                    <!-- TABLE FOR CLIENTS, BOOKINGS, FLEET, DRIVERS -->
                    <div x-show="!loading && items.length > 0 && ['clients','bookings','fleet','drivers'].includes(breakdownType)" class="overflow-x-auto rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface-secondary)]">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs uppercase bg-black/5 dark:bg-gray-100/5 text-[var(--cc-text-muted)] border-b border-[var(--cc-border)]">
                                <!-- Clients Header -->
                                <tr x-show="breakdownType === 'clients'">
                                    <th class="px-4 py-3">Client Name</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Contact Person</th>
                                    <th class="px-4 py-3">Phone</th>
                                </tr>
                                <!-- Bookings Header -->
                                <tr x-show="breakdownType === 'bookings'">
                                    <th class="px-4 py-3">Booking #</th>
                                    <th class="px-4 py-3">Client</th>
                                    <th class="px-4 py-3">Vehicle</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-right">Price</th>
                                </tr>
                                <!-- Fleet Header -->
                                <tr x-show="breakdownType === 'fleet'">
                                    <th class="px-4 py-3">Plate Number</th>
                                    <th class="px-4 py-3">Type</th>
                                    <th class="px-4 py-3">Pool</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                                <!-- Drivers Header -->
                                <tr x-show="breakdownType === 'drivers'">
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Phone</th>
                                    <th class="px-4 py-3">Pool</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--cc-border)]">
                                <template x-for="item in items" :key="item.id">
                                    <tr class="hover:bg-black/5 dark:hover:bg-gray-100/5 transition-colors group">
                                        <!-- Clients Row -->
                                        <template x-if="breakdownType === 'clients'">
                                            <>
                                                <td class="px-4 py-3 font-semibold text-[var(--cc-text)]"><a :href="'/clients/' + item.id" class="group-hover:text-indigo-500" x-text="item.name"></a></td>
                                                <td class="px-4 py-3"><span class="px-2 py-1 text-[10px] rounded font-semibold uppercase bg-slate-500/10 text-slate-500" :class="{'bg-emerald-500/10 text-emerald-500': item.status==='active'}" x-text="item.status"></span></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.contact || '-'"></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.phone || '-'"></td>
                                            </>
                                        </template>

                                        <!-- Bookings Row -->
                                        <template x-if="breakdownType === 'bookings'">
                                            <>
                                                <td class="px-4 py-3 font-mono font-semibold text-indigo-500"><a :href="'/bookings/' + item.id" class="hover:underline" x-text="item.booking_number"></a></td>
                                                <td class="px-4 py-3 text-[var(--cc-text)]" x-text="item.client_name"></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.vehicle"></td>
                                                <td class="px-4 py-3"><span class="px-2 py-1 text-[10px] rounded font-semibold uppercase bg-yellow-500/10 text-yellow-500" x-text="item.status"></span></td>
                                                <td class="px-4 py-3 text-right font-bold text-[var(--cc-text)]" x-text="new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.price)"></td>
                                            </>
                                        </template>

                                        <!-- Fleet Row -->
                                        <template x-if="breakdownType === 'fleet'">
                                            <>
                                                <td class="px-4 py-3 font-mono font-semibold text-[var(--cc-text)]"><a href="/fleet" class="group-hover:text-indigo-500" x-text="item.plate_number"></a></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.type"></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.pool"></td>
                                                <td class="px-4 py-3">
                                                    <span class="px-2 py-1 text-[10px] rounded font-semibold uppercase" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-500': item.status==='available',
                                                              'bg-blue-500/10 text-blue-500': item.status==='on_trip',
                                                              'bg-red-500/10 text-red-500': item.status==='maintenance'
                                                          }" x-text="item.status"></span>
                                                </td>
                                            </>
                                        </template>

                                        <!-- Drivers Row -->
                                        <template x-if="breakdownType === 'drivers'">
                                            <>
                                                <td class="px-4 py-3 font-semibold text-[var(--cc-text)]"><a href="/drivers" class="group-hover:text-indigo-500 flex items-center gap-2"><div class="w-6 h-6 rounded-full bg-indigo-500/20 text-indigo-500 flex items-center justify-center text-[10px] uppercase" x-text="item.name.substring(0,2)"></div> <span x-text="item.name"></span></a></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)] font-mono" x-text="item.phone"></td>
                                                <td class="px-4 py-3 text-[var(--cc-text-muted)]" x-text="item.pool"></td>
                                                <td class="px-4 py-3">
                                                    <span class="px-2 py-1 text-[10px] rounded font-semibold uppercase" 
                                                          :class="{
                                                              'bg-emerald-500/10 text-emerald-500': item.status==='available',
                                                              'bg-blue-500/10 text-blue-500': item.status==='assigned'
                                                          }" x-text="item.status"></span>
                                                </td>
                                            </>
                                        </template>

                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
function salesDashboard() {
    return {
        showModal: false,
        breakdownType: '', // 'revenue', 'opportunities', 'clients', 'bookings', 'fleet', 'drivers'
        param: '', 
        title: '',
        modalIcon: 'database',
        items: [],
        loading: false,
        
        getIconForType(type) {
            switch(type) {
                case 'revenue': return 'payments';
                case 'opportunities': return 'handshake';
                case 'clients': return 'corporate_fare';
                case 'bookings': return 'route';
                case 'fleet': return 'local_shipping';
                case 'drivers': return 'badge';
                default: return 'database';
            }
        },

        getEndpoint(type, param) {
            switch(type) {
                case 'revenue': return `/api/revenue/breakdown?period=${param}`;
                case 'opportunities': return `/api/breakdown/opportunities?stage=${param}`;
                case 'clients': return `/api/breakdown/clients`;
                case 'bookings': return `/api/breakdown/bookings`;
                case 'fleet': return `/api/breakdown/fleet`;
                case 'drivers': return `/api/breakdown/drivers`;
                default: return '';
            }
        },

        openBreakdown(type, param, title) {
            this.breakdownType = type;
            this.param = param;
            this.title = title;
            this.modalIcon = this.getIconForType(type);
            this.showModal = true;
            this.loading = true;
            this.items = [];
            
            const endpoint = this.getEndpoint(type, param);
            
            fetch(endpoint)
                .then(r => r.json())
                .then(data => {
                    this.items = data;
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                    CRM_Toast.show('⚠️ Gagal mengambil detail data dari server', 'error');
                });
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.classList.contains('dark');
    var textColor = isDark ? '#94a3b8' : '#64748b';

    // Get Alpine component reference so we can trigger it from chart click
    const dashboardComponent = document.querySelector('[x-data="salesDashboard()"]').__x.$data;

    // High Contrast, Neon/Pastel color palette for Opportunities Funnel
    // Mapping: Call Meeting, Prospecting, Proposal, Negotiation, Won
    const funnelColors = ['#c084fc', '#38bdf8', '#fbbf24', '#f97316', '#34d399'];
    const funnelStages = ['call_meeting', 'prospecting', 'proposal', 'negotiation', 'won'];
    const funnelLabels = ['Call Meeting', 'Prospecting', 'Proposal', 'Negotiation', 'Won'];

    var funnelOptions = {
        series: [{ name: "Deals", data: {!! json_encode($salesFunnel ?? []) !!} }],
        chart: { 
            type: 'bar', 
            height: 280, 
            toolbar: { show: false }, 
            background: 'transparent',
            events: {
                dataPointSelection: function(event, chartContext, config) {
                    const stageIndex = config.dataPointIndex;
                    const stageCode = funnelStages[stageIndex];
                    const stageLabel = funnelLabels[stageIndex];
                    // Trigger breakdown open
                    dashboardComponent.openBreakdown('opportunities', stageCode, 'Opportunities: ' + stageLabel);
                }
            }
        },
        plotOptions: { 
            bar: { 
                borderRadius: 4, 
                horizontal: true, 
                distributed: true, 
                dataLabels: { position: 'bottom' } 
            } 
        },
        colors: funnelColors,
        dataLabels: { enabled: true, textAnchor: 'start', style: { colors: ['#fff'], fontSize: '13px', fontWeight: 'bold' }, offsetX: 0 },
        xaxis: { categories: funnelLabels, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor, fontSize: '13px', fontWeight: 600 } } },
        tooltip: { theme: isDark ? 'dark' : 'light' },
        states: {
            hover: { filter: { type: 'lighten', value: 0.15 } }
        }
    };
    new ApexCharts(document.querySelector("#funnelChart"), funnelOptions).render();

    // Revenue Trend
    var revData = {!! json_encode($revenueTrend ?? ['labels'=>[],'data'=>[]]) !!};
    var revenueOptions = {
        series: [{ name: "Revenue", data: revData.data }],
        chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
        colors: ['#6366f1'], // using indigo
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [50, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        xaxis: { categories: revData.labels, labels: { style: { colors: textColor } } },
        yaxis: { labels: { style: { colors: textColor }, formatter: function (val) { return "Rp " + (val/1000000).toFixed(0) + "M"; } } },
        tooltip: { theme: isDark ? 'dark' : 'light', y: { formatter: function (val) { return "Rp " + new Intl.NumberFormat('id-ID').format(val); } } }
    };
    new ApexCharts(document.querySelector("#revenueChart"), revenueOptions).render();
});
</script>
@endpush
@endsection
