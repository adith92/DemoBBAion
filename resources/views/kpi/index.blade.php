@extends('layouts.app')

@section('header_title', 'Performance Dashboard')

@push('styles')
<style>
    #content-area {
        overflow-x: hidden !important;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid rgba(0, 0, 0, 0.08);
        backdrop-filter: blur(12px);
    }
    .dark .glass-card {
        background: rgba(22, 29, 46, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="dashboardManager()" x-init="initData()" class="space-y-8 flex flex-col h-full pb-8">
    
    <div class="shrink-0 mb-2">
        <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-1">Performance Overview</h1>
        <p class="text-[var(--cc-text-muted)]">
            Sales Performance Monitoring • 
            <span class="text-indigo-400 font-medium" x-text="currentUser.role === 'Sales' ? 'Team View' : 'Company Overview'"></span>
        </p>
    </div>

    {{-- KPI Highlight Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('pipeline.index') }}" class="bg-gradient-to-br from-indigo-500/10 to-indigo-600/5 rounded-2xl border border-indigo-500/20 p-5 flex items-center justify-between hover:border-indigo-500/40 hover:bg-indigo-500/15 transition duration-150 block group">
            <div>
                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-300 uppercase tracking-widest mb-1 group-hover:text-indigo-500 dark:group-hover:text-indigo-200 transition-colors">Active Pipeline</p>
                <p class="text-2xl font-mono font-bold text-[var(--cc-text)]" x-text="formatIDR(metrics.activePipelineValue)"></p>
                <p class="text-xs text-indigo-600/80 dark:text-indigo-200 mt-1"><span x-text="metrics.activeDealsCount"></span> deals currently active</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0 text-indigo-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            </div>
        </a>

        <a href="{{ route('opportunities.index', ['stage' => 'won']) }}" class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 rounded-2xl border border-emerald-500/20 p-5 flex items-center justify-between hover:border-emerald-500/40 hover:bg-emerald-500/15 transition duration-150 block group">
            <div>
                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-300 uppercase tracking-widest mb-1 group-hover:text-emerald-500 dark:group-hover:text-emerald-200 transition-colors">Total Revenue Won</p>
                <p class="text-2xl font-mono font-bold text-[var(--cc-text)]" x-text="formatIDR(metrics.totalActual)"></p>
                <p class="text-xs text-emerald-600/80 dark:text-emerald-200 mt-1">Acquired from closed won deals</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0 text-emerald-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            </div>
        </a>

        <div class="bg-gradient-to-br from-amber-500/10 to-amber-600/5 rounded-2xl border border-amber-500/20 p-5 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 dark:text-amber-300 uppercase tracking-widest mb-1">Win Rate</p>
                <p class="text-2xl font-mono font-bold text-[var(--cc-text)]" x-text="metrics.winRate.toFixed(1) + '%'"></p>
                <p class="text-xs text-amber-600/80 dark:text-amber-200 mt-1">Closing efficiency</p>
            </div>
            <div class="h-12 w-12 rounded-xl bg-amber-500/20 flex items-center justify-center shrink-0 text-amber-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        {{-- Left Column: Progress & Target --}}
        <div class="lg:col-span-2 space-y-6">
            
            <div class="grid grid-cols-1 gap-6" :class="personalMetrics ? 'xl:grid-cols-2' : ''">
                {{-- Personal/Team Target Progress --}}
                <template x-if="personalMetrics">
                    <div class="rounded-3xl border border-emerald-500/10 bg-emerald-900/10 backdrop-blur-md p-6 relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                        
                        <div class="relative z-10">
                            <div class="flex items-center gap-3 mb-6">
                                <div class="h-10 w-10 bg-emerald-500/20 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="h-5 w-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div>
                                    <h3 class="font-bold text-[var(--cc-text)] text-lg" x-text="currentUser.role === 'Sales' ? 'My Individual Trajectory' : 'My Team Trajectory'"></h3>
                                    <p class="text-xs text-emerald-400/70 font-medium" x-text="'Tracking towards ' + formatIDR(personalMetrics.totalTarget)"></p>
                                </div>
                            </div>
                            
                            <div class="flex flex-wrap items-end justify-between mb-3 gap-2">
                                <div class="text-3xl 2xl:text-4xl font-mono font-bold tracking-tight text-[var(--cc-text)] flex flex-wrap items-baseline gap-2">
                                    <span x-text="formatIDR(personalMetrics.totalActual)"></span>
                                    <span class="text-sm 2xl:text-lg text-emerald-500/70 font-medium" x-text="'/ ' + formatIDR(personalMetrics.totalTarget)"></span>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-teal-400" x-text="(personalMetrics.totalTarget > 0 ? ((personalMetrics.totalActual / personalMetrics.totalTarget) * 100).toFixed(1) : '0.0') + '%'"></div>
                                </div>
                            </div>
                            <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800 shadow-inner mt-4">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 transition-all duration-1000 ease-out relative"
                                     :style="`width: ${Math.min(personalMetrics.totalTarget > 0 ? (personalMetrics.totalActual / personalMetrics.totalTarget) * 100 : 0, 100)}%`">
                                    <div class="absolute inset-0 bg-gray-100/20 w-full h-full animate-pulse"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                {{-- Global Target Progress --}}
                <div class="rounded-3xl border border-white/10 glass-card p-6 relative overflow-hidden flex flex-col justify-between">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
                    
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="h-10 w-10 bg-indigo-500/20 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="h-5 w-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-[var(--cc-text)] text-lg" x-text="currentUser.role === 'Sales' ? 'Team Trajectory' : 'Company Trajectory'"></h3>
                                <p class="text-xs text-slate-400 font-medium" x-text="'Tracking towards ' + formatIDR(metrics.totalTarget)"></p>
                            </div>
                        </div>
                        
                        <div class="flex flex-wrap items-end justify-between mb-3 gap-2">
                            <div class="text-3xl 2xl:text-4xl font-mono font-bold tracking-tight text-[var(--cc-text)] flex flex-wrap items-baseline gap-2">
                                <span x-text="formatIDR(metrics.totalActual)"></span>
                                <span class="text-sm 2xl:text-lg text-slate-500 font-medium" x-text="'/ ' + formatIDR(metrics.totalTarget)"></span>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 to-emerald-400" x-text="globalProgress.toFixed(1) + '%'"></div>
                            </div>
                        </div>
                        <div class="h-3 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800 shadow-inner">
                            <div class="h-full bg-gradient-to-r from-indigo-500 via-blue-500 to-emerald-400 transition-all duration-1000 ease-out relative"
                                 :style="`width: ${Math.min(globalProgress, 100)}%`">
                                <div class="absolute inset-0 bg-gray-100/20 w-full h-full animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Core Products Grid --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
                <template x-for="cat in productCategories" :key="cat">
                    <div class="p-5 glass-card hover:border-white/20 transition-colors rounded-2xl group">
                        <div class="flex justify-between items-start mb-4">
                            <div class="text-sm font-bold text-slate-700 dark:text-slate-300" x-text="cat"></div>
                            <div class="text-xs font-black bg-black/5 dark:bg-gray-100/5 px-2 py-0.5 rounded-full" 
                                 :class="getProductBarColorClass(cat, true)" 
                                 x-text="getProductProgress(cat).toFixed(0) + '%'"></div>
                        </div>
                        <div class="text-xl font-mono font-bold text-[var(--cc-text)] mb-1 group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors" x-text="formatIDR(metrics.productMetrics[cat]?.actual || 0)"></div>
                        <div class="text-[10px] uppercase font-bold text-slate-500 tracking-wider mb-4" x-text="'Target: ' + formatIDR(metrics.productMetrics[cat]?.target || 0)"></div>
                        
                        <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-700 delay-100" 
                                 :class="getProductBarColorClass(cat, false)"
                                 :style="`width: ${Math.min(getProductProgress(cat), 100)}%`"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Right Column: Leaderboard --}}
        <div class="rounded-3xl border border-white/10 glass-card p-6 flex flex-col relative overflow-hidden h-[600px] lg:h-auto">
            <div class="absolute top-0 right-0 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl -translate-y-1/2 translate-x-1/2"></div>
            <div class="relative z-10 flex flex-col h-full">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2 text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <h3 class="font-bold text-[var(--cc-text)] text-lg">Top Performers</h3>
                    </div>
                    <template x-if="(currentUser.role === 'GM' || currentUser.role === 'Manager') && selectedManagerId">
                        <button @click="selectedManagerId = null" 
                                class="text-[10px] uppercase font-bold tracking-widest text-indigo-400 hover:text-indigo-300 flex items-center gap-1 transition-colors bg-indigo-500/10 px-2 py-1 rounded-md">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Back
                        </button>
                    </template>
                </div>
                
                <div class="space-y-3 flex-1 overflow-y-auto pr-2 custom-scrollbar">
                    <template x-if="currentUser.role === 'Sales'">
                        <template x-for="(item, idx) in leaderboard" :key="item.user.id">
                            <div class="flex items-center gap-4 bg-black/5 dark:bg-gray-100/5 p-3 rounded-2xl border border-black/5 dark:border-white/5 hover:bg-black/10 dark:hover:bg-gray-100/10 transition-colors">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl font-bold text-xs border" :class="getRankStyle(idx)" x-text="idx + 1"></div>
                                <div class="flex-1 min-w-0">
                                    <a :href="'/sales/' + item.user.id + '/performance'" class="truncate text-sm font-bold text-cc-cyan hover:underline" x-text="item.user.name"></a>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-mono font-bold text-emerald-400" x-text="formatIDR(item.revenue)"></p>
                                </div>
                            </div>
                        </template>
                    </template>

                    <template x-if="currentUser.role !== 'Sales' && selectedManagerId">
                        <template x-for="(item, idx) in managerLeaderboard.find(m => m.user.id === selectedManagerId)?.reps || []" :key="item.user.id">
                            <div class="flex items-center gap-4 bg-black/5 dark:bg-gray-100/5 p-3 rounded-2xl border border-black/5 dark:border-white/5 hover:bg-black/10 dark:hover:bg-gray-100/10 transition-colors">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl font-bold text-xs border" :class="getRankStyle(idx)" x-text="idx + 1"></div>
                                <div class="flex-1 min-w-0">
                                    <a :href="'/sales/' + item.user.id + '/performance'" class="truncate text-sm font-bold text-cc-cyan hover:underline" x-text="item.user.name"></a>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-mono font-bold text-emerald-400" x-text="formatIDR(item.revenue)"></p>
                                </div>
                            </div>
                        </template>
                    </template>

                    <template x-if="currentUser.role !== 'Sales' && !selectedManagerId">
                        <template x-for="(item, idx) in managerLeaderboard" :key="item.user.id">
                            <div @click="selectedManagerId = item.user.id"
                                 class="flex items-center gap-4 bg-black/5 dark:bg-gray-100/5 p-3 rounded-2xl border border-black/5 dark:border-white/5 cursor-pointer hover:border-indigo-500/50 hover:bg-black/10 dark:hover:bg-gray-100/10 transition group">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl font-bold text-xs border" :class="getRankStyle(idx)" x-text="idx + 1"></div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-bold text-[var(--cc-text)] group-hover:text-indigo-300 transition-colors" x-text="item.user.name"></p>
                                        <a :href="'/sales/' + item.user.id + '/performance'" @click.stop
                                           class="text-cc-cyan hover:text-blue-500 flex items-center transition" title="Lihat Performa Manager">
                                            <span class="material-symbols-outlined text-[15px]">info</span>
                                        </a>
                                    </div>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-0.5" x-text="item.reps.length + ' Reps'"></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-mono font-bold text-emerald-400" x-text="formatIDR(item.revenue)"></p>
                                </div>
                            </div>
                        </template>
                    </template>
                    
                    <template x-if="(currentUser.role === 'Sales' && leaderboard.length === 0) || (currentUser.role !== 'Sales' && managerLeaderboard.length === 0)">
                        <div class="text-center text-sm text-slate-500 py-8 border border-dashed border-black/10 dark:border-white/10 rounded-2xl">No data available</div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function dashboardManager() {
    return {
        currentUser: @json($currentUser),
        users: @json($users),
        deals: @json($deals),
        targets: @json($targets),
        
        productCategories: ['Mobil Short Term', 'Bis Short Term', 'Mobil Long Term', 'Bis Long Term', 'E-Voucher', 'Supir'],
        
        metrics: { totalTarget: 0, totalActual: 0, productMetrics: {}, activeDealsCount: 0, activePipelineValue: 0, winRate: 0 },
        personalMetrics: null,
        globalProgress: 0,
        
        leaderboard: [],
        managerLeaderboard: [],
        selectedManagerId: null,

        initData() {
            const allSalesIds = this.users.filter(u => u.role === 'Sales').map(u => u.id);
            const myTeamIds = this.users.filter(u => u.managerId === (this.currentUser.role === 'Manager' ? this.currentUser.id : this.currentUser.managerId) && u.role === 'Sales').map(u => u.id);
            const myIndividualIds = [this.currentUser.id];
            
            const visibleSalesIds = this.currentUser.role === 'GM' ? allSalesIds : (this.currentUser.role === 'Manager' ? allSalesIds : myTeamIds);
            
            this.metrics = this.calculateMetrics(visibleSalesIds);
            
            if (this.currentUser.role === 'Sales') {
                this.personalMetrics = this.calculateMetrics(myIndividualIds);
            } else if (this.currentUser.role === 'Manager') {
                this.personalMetrics = this.calculateMetrics(myTeamIds);
            }
            
            this.globalProgress = this.metrics.totalTarget > 0 ? (this.metrics.totalActual / this.metrics.totalTarget) * 100 : 0;
            
            this.calculateLeaderboards(visibleSalesIds);
        },
        
        calculateMetrics(salesIds) {
            let totalTarget = 0;
            let totalActual = 0;
            let productMetrics = {};
            this.productCategories.forEach(cat => {
                productMetrics[cat] = { target: 0, actual: 0 };
            });
            
            this.targets.forEach(t => {
                if (salesIds.includes(t.userId)) {
                    this.productCategories.forEach(cat => {
                        productMetrics[cat].target += t.productTargets[cat] || 0;
                        totalTarget += t.productTargets[cat] || 0;
                    });
                }
            });
            
            let activeDealsCount = 0;
            let activePipelineValue = 0;
            let wonDealsCount = 0;
            let lostDealsCount = 0;
            
            this.deals.forEach(d => {
                if (salesIds.includes(d.salesId)) {
                    if (d.stage === 'Won') {
                        const val = d.actualValue || 0;
                        let prods = d.products || [];
                        if (typeof prods === 'string') prods = JSON.parse(prods);
                        
                        // Fallback to productName if products list is empty
                        if (prods.length === 0 && d.productName) {
                            prods = [{
                                category: d.productName,
                                estimatedValue: val,
                                quantity: 1
                            }];
                        }

                        let totalEst = prods.reduce((acc, p) => acc + (p.estimatedValue * (p.quantity || 1)), 0);
                        if (totalEst === 0) totalEst = 1;
                        prods.forEach(p => {
                            if (productMetrics[p.category]) {
                                const prop = (p.estimatedValue * (p.quantity || 1)) / totalEst;
                                productMetrics[p.category].actual += Math.round(val * prop);
                            }
                        });
                        totalActual += val;
                        wonDealsCount++;
                    } else if (d.stage === 'Lost') {
                        lostDealsCount++;
                    } else {
                        activeDealsCount++;
                        activePipelineValue += (d.estimatedValue || 0);
                    }
                }
            });
            
            const totalClosed = wonDealsCount + lostDealsCount;
            const winRate = totalClosed > 0 ? (wonDealsCount / totalClosed) * 100 : 0;
            
            return { totalTarget, totalActual, productMetrics, activeDealsCount, activePipelineValue, winRate };
        },

        calculateLeaderboards(visibleSalesIds) {
            // Reps leaderboard
            let scores = {};
            this.users.filter(u => u.role === 'Sales').forEach(u => {
                if (visibleSalesIds.includes(u.id)) {
                    scores[u.id] = { user: u, revenue: 0 };
                }
            });
            this.deals.forEach(d => {
                if (d.stage === 'Won' && scores[d.salesId]) {
                    scores[d.salesId].revenue += (d.actualValue || 0);
                }
            });
            this.leaderboard = Object.values(scores).sort((a, b) => b.revenue - a.revenue);
            
            // Managers leaderboard
            let managers = {};
            this.users.filter(u => u.role === 'Manager').forEach(u => {
                managers[u.id] = { user: u, revenue: 0, reps: [] };
            });
            
            let reps = {};
            this.users.filter(u => u.role === 'Sales').forEach(u => {
                if (visibleSalesIds.includes(u.id)) {
                    reps[u.id] = { user: u, revenue: 0, managerId: u.managerId };
                }
            });
            
            this.deals.forEach(d => {
                if (d.stage === 'Won' && reps[d.salesId]) {
                    reps[d.salesId].revenue += (d.actualValue || 0);
                }
            });
            
            Object.values(reps).forEach(rep => {
                if (rep.managerId && managers[rep.managerId]) {
                    managers[rep.managerId].reps.push(rep);
                    managers[rep.managerId].revenue += rep.revenue;
                }
            });
            
            Object.values(managers).forEach(m => {
                m.reps.sort((a, b) => b.revenue - a.revenue);
            });
            
            this.managerLeaderboard = Object.values(managers)
                .filter(m => m.reps.length > 0)
                .sort((a, b) => b.revenue - a.revenue);
        },

        formatIDR(val) {
            if (!val) val = 0;
            return 'Rp ' + parseInt(val).toLocaleString('id-ID');
        },
        
        getProductProgress(cat) {
            const m = this.metrics.productMetrics[cat];
            if(!m || m.target === 0) return 0;
            return (m.actual / m.target) * 100;
        },
        
        getProductBarColorClass(cat, isText) {
            const p = this.getProductProgress(cat);
            if (p >= 100) return isText ? 'text-emerald-600 dark:text-emerald-400' : 'bg-emerald-500';
            if (p >= 75) return isText ? 'text-indigo-600 dark:text-indigo-400' : 'bg-indigo-500';
            if (p >= 50) return isText ? 'text-amber-600 dark:text-amber-400' : 'bg-amber-500';
            return isText ? 'text-rose-600 dark:text-rose-400' : 'bg-rose-500';
        },
        
        getRankStyle(idx) {
            if (idx === 0) return 'bg-amber-500/20 text-amber-700 dark:text-amber-300 border-amber-500/30';
            if (idx === 1) return 'bg-slate-300/20 text-slate-700 dark:text-slate-300 border-slate-300/30';
            if (idx === 2) return 'bg-amber-700/20 text-amber-800 dark:text-amber-600 border-amber-700/30';
            return 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-white/5';
        }
    }
}
</script>
@endpush
