@extends('layouts.app')

@section('header_title', 'Active Pipeline Funnel')

@push('styles')
<style>
    /* ── KANBAN SPA LAYOUT & GLASSMORPHISM ── */
    #content-area {
        overflow-x: visible !important;
        overflow: hidden !important;
    }
    #content-area > div.p-6 {
        padding: 0 !important;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .glass-panel {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
    }
    .dark .glass-panel {
        background: rgba(22, 29, 46, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .kanban-page-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 0;
        background: var(--cc-bg);
        padding: 24px;
    }

    .kanban-scroll-x {
        flex: 1;
        min-height: 0;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 12px;
    }
    .kanban-scroll-x::-webkit-scrollbar { height: 6px; }
    .kanban-scroll-x::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); border-radius: 3px; }
    .kanban-scroll-x::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

    .kanban-board {
        display: flex;
        gap: 16px;
        height: 100%;
        min-width: max-content;
        align-items: flex-start;
    }

    .kanban-column {
        width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 100%;
        border-radius: 24px;
    }

    .kanban-drop-zone {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 12px;
    }

    .kanban-card {
        border-radius: 16px;
        padding: 16px;
        cursor: grab;
        transition: all 0.2s;
    }
    .kanban-card:hover {
        border-color: rgba(99, 102, 241, 0.5); /* indigo-500/50 */
    }
    .kanban-card:active { cursor: grabbing; }

    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

    /* For smooth animations in alpine */
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
@php
    $stages = [
        'call_meeting' => 'Call/Meeting',
        'prospecting'  => 'Prospecting',
        'proposal'     => 'Proposal',
        'negotiation'  => 'Negotiation',
        'won'          => 'Won',
        'lost'         => 'Lost'
    ];
    $isManager = auth()->user()->isManager() || auth()->user()->isDirector() || auth()->user()->isGM();
    $userId = auth()->id();
@endphp

<div x-data="pipelineManager()" x-init="initData()" class="kanban-page-wrapper">
    
    {{-- HEADER --}}
    <div class="mb-6 flex items-center justify-between shrink-0">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-1">Active Pipeline Funnel</h1>
            <p class="text-[var(--cc-text-muted)]">Manage your deals across sales stages.</p>
        </div>
        <div class="flex items-center gap-4">
            @if($isManager)
            <select x-model="selectedSalesFilter"
                    class="rounded-xl border border-[var(--cc-border)] bg-[var(--cc-card)] px-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500">
                <option value="all">All Sales</option>
                @foreach($salesUsers as $su)
                    <option value="{{ $su->id }}">{{ $su->name }}</option>
                @endforeach
            </select>
            @endif

            @if(auth()->user()->isSales())
            <button @click="openCreateModal()"
                    class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 shadow-md shadow-indigo-600/20 transition">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Deal
            </button>
            @endif
        </div>
    </div>

    {{-- KANBAN BOARD --}}
    <div class="kanban-scroll-x custom-scrollbar">
        <div class="kanban-board">
            @foreach($stages as $key => $label)
            <div class="kanban-column glass-panel" data-stage="{{ $key }}">
                <div class="p-4 border-b border-white/5 flex items-center justify-between shrink-0">
                    <h3 class="font-bold text-[var(--cc-text)] text-sm tracking-wide">{{ $label }}</h3>
                    <span class="text-xs font-bold text-slate-400 bg-black/20 px-2.5 py-0.5 rounded-full border border-white/5" x-text="getDealCount('{{ $key }}')">0</span>
                </div>
                
                <div class="kanban-drop-zone custom-scrollbar" id="col-{{ $key }}">
                    <template x-for="deal in filteredDeals('{{ $key }}')" :key="deal.id">
                        <div class="kanban-card glass-panel group relative" :data-id="deal.id">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-[var(--cc-text)] text-sm leading-tight" x-text="deal.title"></h4>
                            </div>
                            
                            <div class="mb-2 space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-widest truncate max-w-[65%]" x-text="deal.client_name"></p>
                                    <div class="flex items-center gap-1 text-[10px] text-slate-500 font-medium whitespace-nowrap">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span x-text="formatDate(deal.created_at)"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-4 h-4 rounded border border-white/10 bg-slate-700/50 flex items-center justify-center text-[9px] font-bold text-slate-300" x-text="deal.sales_name.charAt(0)"></div>
                                    <span class="text-xs text-slate-400 truncate" x-text="deal.sales_name"></span>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between mt-3 mb-2">
                                <div class="flex flex-wrap gap-1">
                                    <template x-for="p in (deal.products || [])" :key="p.id">
                                        <span class="inline-flex items-center rounded-lg bg-indigo-500/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-300 ring-1 ring-inset ring-indigo-500/30">
                                            <span x-text="(p.quantity > 1 ? p.quantity + 'x ' : '') + p.category"></span>
                                        </span>
                                    </template>
                                </div>
                                <span class="text-xs font-mono font-bold text-emerald-400" x-text="formatIDR(deal.stage === 'won' ? deal.final_value : deal.estimated_value)"></span>
                            </div>

                            <button @click="openHistoryModal(deal)"
                                    class="text-[10px] font-bold text-slate-400 hover:text-indigo-400 flex items-center gap-1.5 transition-colors uppercase tracking-widest mt-2">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                View History
                            </button>
                            
                            <template x-if="deal.stage === 'lost' && deal.lost_reason">
                                <div class="mt-2 text-[10px] text-rose-300 bg-rose-500/20 p-2 rounded-lg flex items-start gap-1 border border-rose-500/20">
                                    <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span x-text="deal.lost_reason"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MODALS --}}
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[#0f172a]/80 backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl bg-[#161d2e] shadow-2xl border border-white/10 flex flex-col" @click.away="closeModal()">
            
            <div class="p-6 border-b border-white/5">
                <h2 class="text-lg font-bold text-white" x-text="modalTitle"></h2>
            </div>
            
            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                
                {{-- HISTORY VIEW --}}
                <template x-if="modalMode === 'history'">
                    <div class="space-y-4">
                        <template x-if="!editingDeal.history_timeline || editingDeal.history_timeline.length === 0">
                            <div class="text-sm text-slate-400 text-center py-4">No history available for this deal.</div>
                        </template>
                        <template x-for="entry in (editingDeal.history_timeline || [])" :key="entry.id">
                            <div class="relative pl-6 pb-4 border-l border-white/10 last:border-0 last:pb-0">
                                <div class="absolute left-[-5px] top-1 w-2 h-2 rounded-full bg-indigo-500 ring-4 ring-[#161d2e]"></div>
                                
                                <div class="cursor-pointer group flex justify-between items-start rounded-xl -ml-2 p-2 transition hover:bg-white/5"
                                     @click="expandedHistoryId = (expandedHistoryId === entry.id ? null : entry.id)">
                                    <div>
                                        <div class="text-sm font-bold text-white group-hover:text-indigo-300 transition-colors"
                                             x-text="stageLabel(entry.stage) + (entry.subType ? ' (' + entry.subType + ')' : '')"></div>
                                        <div class="text-xs text-slate-400" x-text="formatDate(entry.timestamp, true)"></div>
                                    </div>
                                    <template x-if="entry.note || entry.products?.length || entry.estimatedValue">
                                        <svg class="w-4 h-4 text-slate-400" :class="{'rotate-180': expandedHistoryId === entry.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </template>
                                </div>

                                <div x-show="expandedHistoryId === entry.id" class="mt-2 text-sm bg-black/20 rounded-xl p-3 border border-white/5 space-y-3" x-transition>
                                    <template x-if="entry.note">
                                        <div>
                                            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Note</h4>
                                            <p class="text-white/80 whitespace-pre-wrap leading-relaxed" x-text="entry.note"></p>
                                        </div>
                                    </template>
                                    <template x-if="entry.products && entry.products.length > 0">
                                        <div>
                                            <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5">Products</h4>
                                            <div class="space-y-1.5">
                                                <template x-for="p in entry.products" :key="p.id">
                                                    <div class="flex flex-col text-xs bg-white/5 px-2 py-1.5 rounded-lg border border-white/5">
                                                        <div class="flex justify-between">
                                                            <div>
                                                                <span class="text-white" x-text="p.category"></span>
                                                                <span class="text-slate-400 ml-1" x-text="'x' + (p.quantity || 1)"></span>
                                                            </div>
                                                            <span class="text-emerald-400 font-mono" x-text="formatIDR(p.estimatedValue * (p.quantity || 1))"></span>
                                                        </div>
                                                        <template x-if="p.details">
                                                            <div class="text-white/60 mt-1.5 text-[10px] bg-black/20 p-1.5 rounded-md border border-white/5 leading-relaxed">
                                                                <span class="font-semibold text-slate-500 mr-1">Note:</span>
                                                                <span x-text="p.details"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="entry.estimatedValue !== undefined">
                                        <div class="pt-2 border-t border-white/10 flex justify-between items-center">
                                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Total Est. Value</span>
                                            <span class="text-sm text-emerald-400 font-mono font-bold" x-text="formatIDR(entry.estimatedValue)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                {{-- CREATE / EDIT STAGE --}}
                <template x-if="modalMode === 'create'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Deal Title <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="editingDeal.title" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Company <span class="text-rose-500">*</span></label>
                            <select x-model="editingDeal.client_id" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500">
                                <option value="" disabled>Select a company</option>
                                @foreach($clients as $c)
                                    <option value="{{ $c->id }}">{{ $c->company_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </template>

                <template x-if="modalMode === 'create' || modalMode === 'edit-stage'">
                    <div class="space-y-4 mt-4">
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest">Products</label>
                                <span class="text-xs font-bold text-emerald-400" x-text="'Total Est: ' + formatIDR(calculateTotalEst())"></span>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(p, idx) in editingDeal.products" :key="p.id">
                                    <div class="p-3 rounded-xl border border-white/10 bg-white/5 space-y-2 relative group">
                                        <button type="button" @click="editingDeal.products.splice(idx, 1)" class="absolute top-2 right-2 text-slate-500 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        <select x-model="p.category" class="w-[90%] bg-transparent text-sm text-white font-bold outline-none">
                                            <option class="text-slate-900" value="Mobil Short Term">Mobil Short Term</option>
                                            <option class="text-slate-900" value="Mobil Long Term">Mobil Long Term</option>
                                            <option class="text-slate-900" value="Bis Short Term">Bis Short Term</option>
                                            <option class="text-slate-900" value="Bis Long Term">Bis Long Term</option>
                                            <option class="text-slate-900" value="E-Voucher">E-Voucher</option>
                                            <option class="text-slate-900" value="Supir">Supir</option>
                                        </select>
                                        <div class="flex gap-2">
                                            <div class="w-20">
                                                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest pl-1">Qty</label>
                                                <input type="number" min="1" x-model.number="p.quantity" class="w-full rounded-lg border border-white/10 bg-[#161d2e] px-3 py-1.5 text-sm text-white outline-none focus:border-indigo-500" />
                                            </div>
                                            <div class="flex-1">
                                                <label class="text-[10px] text-slate-500 uppercase font-bold tracking-widest pl-1">Unit Price</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1.5 text-sm text-slate-400">Rp</span>
                                                    <input type="text" x-model="p.formattedPrice" @input="handlePriceInput(p, $event)" class="w-full rounded-lg border border-white/10 bg-[#161d2e] pl-8 pr-3 py-1.5 text-sm text-white outline-none focus:border-indigo-500" />
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="text" placeholder="Details / Note (Optional)" x-model="p.details" class="w-full rounded-lg border border-transparent bg-[#161d2e]/50 px-3 py-1.5 text-xs text-white outline-none focus:border-white/10 placeholder:text-slate-600" />
                                        </div>
                                    </div>
                                </template>
                                <button @click="addProduct()" type="button" class="w-full rounded-xl border border-dashed border-white/20 py-3 text-sm font-bold text-slate-400 hover:text-indigo-300 hover:border-indigo-400/50 hover:bg-indigo-500/10 transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add Product Item
                                </button>
                            </div>
                        </div>

                        <template x-if="targetStage === 'call_meeting'">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Activity Type</label>
                                <select x-model="subType" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500">
                                    <option value="Call">Call</option>
                                    <option value="Offline Meeting">Offline Meeting</option>
                                </select>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Note / Highlights</label>
                            <textarea x-model="note" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500 min-h-[80px]" placeholder="Add details..."></textarea>
                        </div>

                        <template x-if="targetStage === 'won' && modalMode !== 'history'">
                            <div class="bg-emerald-500/10 text-emerald-300 p-4 rounded-2xl text-sm border border-emerald-500/20">
                                <p class="font-bold">Deal Won!</p>
                                <p class="mt-1">100% of the Estimated Value (<span x-text="formatIDR(calculateTotalEst())"></span>) will be recognized as Actual Revenue.</p>
                            </div>
                        </template>

                        <template x-if="targetStage === 'lost' && modalMode !== 'history'">
                            <div>
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1.5">Lost Reason <span class="text-rose-500">*</span></label>
                                <textarea x-model="editingDeal.lost_reason" class="w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm text-white outline-none focus:border-indigo-500" rows="3" placeholder="Why was this deal lost?"></textarea>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <div class="p-5 bg-black/20 rounded-b-3xl flex justify-end gap-3 border-t border-white/5 mt-auto">
                <button @click="closeModal()" class="px-4 py-2 text-sm font-bold text-slate-400 hover:text-white transition">
                    <span x-text="modalMode === 'history' ? 'Close' : 'Cancel'"></span>
                </button>
                <template x-if="modalMode !== 'history'">
                    <button @click="saveDeal()" 
                            :disabled="isSaving || (targetStage === 'lost' && !editingDeal.lost_reason) || (modalMode === 'create' && (!editingDeal.title || !editingDeal.client_id))"
                            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white px-6 py-2 rounded-xl justify-center items-center text-sm font-bold transition shadow-lg shadow-indigo-500/20">
                        <span x-text="isSaving ? 'Saving...' : 'Save'"></span>
                    </button>
                </template>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
function pipelineManager() {
    return {
        rawDeals: @json($opportunities->items()),
        selectedSalesFilter: 'all',
        isModalOpen: false,
        modalMode: 'create', // create, edit-stage, history
        modalTitle: '',
        targetStage: 'call_meeting',
        subType: 'Call',
        note: '',
        expandedHistoryId: null,
        isSaving: false,
        editingDeal: {
            title: '',
            client_id: '',
            products: [],
            lost_reason: ''
        },

        initData() {
            this.rawDeals = this.rawDeals.map(d => {
                if(d.products && typeof d.products === 'string') d.products = JSON.parse(d.products);
                if(d.history_timeline && typeof d.history_timeline === 'string') d.history_timeline = JSON.parse(d.history_timeline);
                d.client_name = d.client?.company_name || '';
                d.sales_name = d.sales?.name || '';
                if(d.products) {
                    d.products.forEach(p => p.formattedPrice = p.estimatedValue.toLocaleString('id-ID'));
                }
                return d;
            });
            this.initSortable();
        },

        filteredDeals(stage) {
            return this.rawDeals.filter(d => {
                if (d.stage !== stage) return false;
                if (this.selectedSalesFilter !== 'all' && d.sales_id != this.selectedSalesFilter) return false;
                return true;
            });
        },

        getDealCount(stage) {
            return this.filteredDeals(stage).length;
        },

        formatIDR(val) {
            if(!val) val = 0;
            return 'Rp ' + parseInt(val).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        },

        formatDate(iso, time = false) {
            if(!iso) return '';
            const d = new Date(iso);
            if(time) {
                return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
            }
            return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
        },

        stageLabel(st) {
            const map = {
                'call_meeting': 'Call/Meeting',
                'prospecting': 'Prospecting',
                'proposal': 'Proposal',
                'negotiation': 'Negotiation',
                'won': 'Won',
                'lost': 'Lost'
            };
            return map[st] || st;
        },

        calculateTotalEst() {
            if(!this.editingDeal.products) return 0;
            return this.editingDeal.products.reduce((acc, p) => acc + (p.estimatedValue * (p.quantity || 1)), 0);
        },

        addProduct() {
            if(!this.editingDeal.products) this.editingDeal.products = [];
            this.editingDeal.products.push({
                id: 'p' + Date.now(),
                category: 'Mobil Short Term',
                quantity: 1,
                estimatedValue: 0,
                formattedPrice: '',
                details: ''
            });
        },

        handlePriceInput(p, event) {
            let val = parseInt(event.target.value.replace(/[^0-9]/g, '')) || 0;
            p.estimatedValue = val;
            p.formattedPrice = val > 0 ? val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") : '';
        },

        openHistoryModal(deal) {
            this.editingDeal = JSON.parse(JSON.stringify(deal));
            this.modalMode = 'history';
            this.modalTitle = 'Deal History';
            this.expandedHistoryId = null;
            this.isModalOpen = true;
        },

        openCreateModal() {
            this.editingDeal = {
                title: '',
                client_id: '',
                products: [],
                lost_reason: ''
            };
            this.addProduct();
            this.targetStage = 'call_meeting';
            this.subType = 'Call';
            this.note = '';
            this.modalMode = 'create';
            this.modalTitle = 'Create New Deal';
            this.isModalOpen = true;
        },

        openStageModal(deal, newStage) {
            this.editingDeal = JSON.parse(JSON.stringify(deal));
            // Initialize formattedPrice for editing
            if(this.editingDeal.products) {
                this.editingDeal.products.forEach(p => p.formattedPrice = (p.estimatedValue || 0).toString().replace(/\B(?=(\d{3})+(?!\d))/g, "."));
            }
            this.targetStage = newStage;
            this.subType = 'Call';
            this.note = '';
            this.modalMode = 'edit-stage';
            this.modalTitle = 'Move to ' + this.stageLabel(newStage);
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
        },

        async saveDeal() {
            this.isSaving = true;
            try {
                const payload = {
                    title: this.editingDeal.title,
                    client_id: this.editingDeal.client_id,
                    stage: this.targetStage,
                    products: this.editingDeal.products,
                    subType: this.subType,
                    notes: this.note,
                    lost_reason: this.editingDeal.lost_reason
                };

                let url = '{{ route("opportunities.store") }}';
                let method = 'POST';
                if(this.modalMode === 'edit-stage') {
                    url = `/opportunities/${this.editingDeal.id}`;
                    payload._method = 'PUT';
                }

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if(!res.ok) {
                    throw new Error(data.message || 'Error saving deal');
                }

                // Update UI
                if(this.modalMode === 'create') {
                    let newDeal = data.opportunity;
                    newDeal.client_name = {{ \Illuminate\Support\Js::from($clients->pluck('company_name', 'id')) }}[newDeal.client_id] || '';
                    newDeal.sales_name = '{{ auth()->user()->name }}'; // since sales created it
                    this.rawDeals.unshift(newDeal);
                } else {
                    let idx = this.rawDeals.findIndex(d => d.id === this.editingDeal.id);
                    if(idx !== -1) {
                        data.opportunity.client_name = this.rawDeals[idx].client_name;
                        data.opportunity.sales_name = this.rawDeals[idx].sales_name;
                        this.rawDeals[idx] = data.opportunity;
                    }
                }
                
                if(typeof CRM_Toast !== 'undefined') {
                    CRM_Toast.show('Deal saved successfully', 'success');
                } else {
                    alert('Deal saved successfully');
                }
                
                this.closeModal();
            } catch (e) {
                if(typeof CRM_Toast !== 'undefined') {
                    CRM_Toast.show(e.message, 'error');
                } else {
                    alert(e.message);
                }
            } finally {
                this.isSaving = false;
            }
        },

        initSortable() {
            const self = this;
            document.querySelectorAll('.kanban-drop-zone').forEach(el => {
                new Sortable(el, {
                    group: 'pipeline',
                    animation: 150,
                    ghostClass: 'opacity-50',
                    onEnd: function (evt) {
                        if (evt.from === evt.to) return;
                        
                        const itemEl = evt.item;
                        const dealId = itemEl.getAttribute('data-id');
                        const newStage = evt.to.parentElement.getAttribute('data-stage');
                        
                        // Find deal
                        const deal = self.rawDeals.find(d => d.id == dealId);
                        
                        // Revert DOM instantly
                        evt.from.insertBefore(itemEl, evt.from.children[evt.oldIndex]);
                        
                        self.openStageModal(deal, newStage);
                    }
                });
            });
        }
    }
}
</script>
@endpush
