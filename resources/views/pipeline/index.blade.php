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

    .kanban-column-panel {
        background: rgba(0, 0, 0, 0.02);
        border: 1px solid var(--cc-border);
    }
    .dark .kanban-column-panel {
        background: rgba(22, 29, 46, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .kanban-card-panel {
        background: var(--cc-card);
        border: 1px solid var(--cc-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    .dark .kanban-card-panel {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: none;
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
        cursor: default;
        transition: all 0.2s;
    }
    .kanban-card:hover {
        border-color: rgba(99, 102, 241, 0.5); /* indigo-500/50 */
    }

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
            <h1 class="text-3xl font-bold tracking-tight text-[var(--cc-text)] mb-1">Sales Pipeline</h1>
        </div>
        <div class="flex items-center gap-3 flex-wrap">
            {{-- Search --}}
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-[var(--cc-text-muted)]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input type="text" x-model="searchQuery" placeholder="Cari deal / klien..."
                       class="rounded-xl border border-[var(--cc-border)] bg-[var(--cc-card)] pl-9 pr-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500 w-48 transition-all focus:w-64 placeholder:text-[var(--cc-text-faint)]">
            </div>

            {{-- Sort By --}}
            <select x-model="sortBy"
                    class="rounded-xl border border-[var(--cc-border)] bg-[var(--cc-card)] px-4 py-2 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500">
                <option value="updated_desc">Terbaru Diupdate</option>
                <option value="created_desc">Terbaru Dibuat</option>
                <option value="value_desc">Nilai Terbesar</option>
                <option value="value_asc">Nilai Terkecil</option>
            </select>

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
                    class="flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-[var(--cc-text)] hover:bg-indigo-500 shadow-md shadow-indigo-600/20 transition">
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
            <div class="kanban-column kanban-column-panel" data-stage="{{ $key }}">
                <div class="p-4 border-b border-[var(--cc-border)] flex items-center justify-between shrink-0">
                    <div class="flex flex-col min-w-0 pr-2">
                        <h3 class="font-bold text-[var(--cc-text)] text-sm tracking-wide truncate">{{ $label }}</h3>
                        <span class="text-[10px] font-mono text-emerald-400 font-bold mt-0.5" x-text="formatIDR(getStageValueSum('{{ $key }}'))"></span>
                    </div>
                    <span class="text-xs font-bold text-[var(--cc-text-muted)] bg-[var(--cc-bg)] px-2.5 py-0.5 rounded-full border border-[var(--cc-border)]" x-text="getDealCount('{{ $key }}')">0</span>
                </div>
                
                <div class="kanban-drop-zone custom-scrollbar" id="col-{{ $key }}">
                    <template x-for="deal in filteredDeals('{{ $key }}')" :key="deal.id">
                        <div class="kanban-card kanban-card-panel group relative" :data-id="deal.id">
                            {{-- Clickable Header to Expand/Collapse --}}
                            <div class="flex justify-between items-start mb-1 cursor-pointer" @click="deal.expanded = !deal.expanded">
                                <h4 class="font-bold text-[var(--cc-text)] text-sm leading-tight pr-4" x-text="deal.client_name"></h4>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-mono font-bold text-emerald-500" x-text="formatIDR(deal.stage === 'won' ? deal.final_value : deal.estimated_value)"></span>
                                    <svg class="w-4 h-4 text-[var(--cc-text-muted)] transition-transform duration-200" :class="{'rotate-180': deal.expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>

                            {{-- Quick Info (Always Visible) --}}
                            <div class="flex justify-between items-center text-[10px] text-[var(--cc-text-muted)] mt-1 cursor-pointer" @click="deal.expanded = !deal.expanded">
                                <span class="truncate pr-2 max-w-[70%]" x-text="deal.title"></span>
                                <span class="font-semibold px-1.5 py-0.5 rounded bg-[var(--cc-border)]/40 text-[9px] text-[var(--cc-text-muted)] shrink-0" x-text="getStageAgeString(deal.stage_changed_at || deal.updated_at)"></span>
                            </div>
                            
                            {{-- Expanded Details --}}
                            <div x-show="deal.expanded" x-collapse x-transition>
                                <div class="mt-3 mb-2 space-y-1.5 border-t border-[var(--cc-border)]/50 pt-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest">Detail Deal</span>
                                        <div class="flex items-center gap-1 text-[10px] text-[var(--cc-text-muted)] font-medium whitespace-nowrap">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span x-text="formatDate(deal.created_at)"></span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-4 h-4 rounded border border-[var(--cc-border)] bg-[var(--cc-border)] flex items-center justify-center text-[9px] font-bold text-[var(--cc-text)]" x-text="deal.sales_name.charAt(0)"></div>
                                        <span class="text-xs text-[var(--cc-text-muted)] truncate" x-text="deal.sales_name"></span>
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
                                </div>

                                <div class="flex gap-2">
                                    <button @click.stop="openHistoryModal(deal)"
                                            class="text-[10px] font-bold text-[var(--cc-text-muted)] hover:text-indigo-400 flex items-center gap-1.5 transition-colors uppercase tracking-widest mt-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        History
                                    </button>
                                </div>
                                
                                <template x-if="deal.stage === 'lost' && deal.lost_reason">
                                    <div class="mt-2 text-[10px] text-rose-300 bg-rose-500/20 p-2 rounded-lg flex items-start gap-1 border border-rose-500/20">
                                        <svg class="w-3 h-3 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span x-text="deal.lost_reason"></span>
                                    </div>
                                </template>

                                 <template x-if="currentUserRole === 'sales' && deal.sales_id === currentUserId">
                                    <div class="mt-3 pt-3 border-t border-[var(--cc-border)] flex items-center justify-between" @click.stop>
                                        <span class="text-[10px] font-bold text-[var(--cc-text-muted)] uppercase tracking-widest">Update Stage:</span>
                                        <select :value="deal.stage" @change="openStageModal(deal, $event.target.value)" class="rounded-lg border border-[var(--cc-border)] bg-[var(--cc-modal-bg)] px-2 py-1 text-xs text-[var(--cc-text)] outline-none focus:border-indigo-500">
                                            <option class="text-slate-900" value="call_meeting">Call/Meeting</option>
                                            <option class="text-slate-900" value="prospecting">Prospecting</option>
                                            <option class="text-slate-900" value="proposal">Proposal</option>
                                            <option class="text-slate-900" value="negotiation">Negotiation</option>
                                            <option class="text-slate-900" value="won">Won</option>
                                            <option class="text-slate-900" value="lost">Lost</option>
                                        </select>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- MODALS --}}
    <div x-show="isModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-[var(--cc-overlay)] backdrop-blur-sm p-4">
        <div class="w-full max-w-md rounded-3xl bg-[var(--cc-modal-bg)] shadow-2xl border border-[var(--cc-border)] flex flex-col" @click.away="closeModal()">
            
            <div class="p-6 border-b border-[var(--cc-border)]">
                <h2 class="text-lg font-bold text-[var(--cc-text)]" x-text="modalTitle"></h2>
            </div>
            
            <div class="p-6 space-y-4 max-h-[60vh] overflow-y-auto custom-scrollbar">
                
                {{-- HISTORY VIEW --}}
                <template x-if="modalMode === 'history'">
                    <div class="space-y-4">
                        <template x-if="!editingDeal.history_timeline || editingDeal.history_timeline.length === 0">
                            <div class="text-sm text-[var(--cc-text-muted)] text-center py-4">No history available for this deal.</div>
                        </template>
                        <template x-for="entry in (editingDeal.history_timeline || [])" :key="entry.id">
                            <div class="relative pl-6 pb-4 border-l border-[var(--cc-border)] last:border-0 last:pb-0">
                                <div class="absolute left-[-5px] top-1 w-2 h-2 rounded-full bg-indigo-500 ring-4 ring-[var(--cc-modal-bg)]"></div>
                                
                                <div class="cursor-pointer group flex justify-between items-start rounded-xl -ml-2 p-2 transition hover:bg-[var(--cc-surface)]"
                                     @click="expandedHistoryId = (expandedHistoryId === entry.id ? null : entry.id)">
                                    <div>
                                        <div class="text-sm font-bold text-[var(--cc-text)] group-hover:text-indigo-300 transition-colors"
                                             x-text="stageLabel(entry.stage) + (entry.subType ? ' (' + entry.subType + ')' : '')"></div>
                                        <div class="text-xs text-[var(--cc-text-muted)]" x-text="formatDate(entry.timestamp, true)"></div>
                                    </div>
                                    <template x-if="entry.note || entry.products?.length || entry.estimatedValue">
                                        <svg class="w-4 h-4 text-[var(--cc-text-muted)]" :class="{'rotate-180': expandedHistoryId === entry.id}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </template>
                                </div>

                                <div x-show="expandedHistoryId === entry.id" class="mt-2 text-sm bg-[var(--cc-bg)] rounded-xl p-3 border border-[var(--cc-border)] space-y-3" x-transition>
                                    <template x-if="entry.note">
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[var(--cc-text-muted)] uppercase tracking-wider mb-1">Note</h4>
                                            <p class="text-[var(--cc-text)]/80 whitespace-pre-wrap leading-relaxed" x-text="entry.note"></p>
                                        </div>
                                    </template>
                                    <template x-if="entry.products && entry.products.length > 0">
                                        <div>
                                            <h4 class="text-[10px] font-bold text-[var(--cc-text-muted)] uppercase tracking-wider mb-1.5">Products</h4>
                                            <div class="space-y-1.5">
                                                <template x-for="p in entry.products" :key="p.id">
                                                    <div class="flex flex-col text-xs bg-[var(--cc-surface)] px-2 py-1.5 rounded-lg border border-[var(--cc-border)]">
                                                        <div class="flex justify-between">
                                                            <div>
                                                                <span class="text-[var(--cc-text)]" x-text="p.category"></span>
                                                                <span class="text-[var(--cc-text-muted)] ml-1" x-text="'x' + (p.quantity || 1)"></span>
                                                            </div>
                                                            <span class="text-emerald-400 font-mono" x-text="formatIDR(p.estimatedValue * (p.quantity || 1))"></span>
                                                        </div>
                                                        <template x-if="p.details">
                                                            <div class="text-[var(--cc-text)]/60 mt-1.5 text-[10px] bg-[var(--cc-bg)] p-1.5 rounded-md border border-[var(--cc-border)] leading-relaxed">
                                                                <span class="font-semibold text-[var(--cc-text-muted)] mr-1">Note:</span>
                                                                <span x-text="p.details"></span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="entry.estimatedValue !== undefined">
                                        <div class="pt-2 border-t border-[var(--cc-border)] flex justify-between items-center">
                                            <span class="text-[10px] font-bold text-[var(--cc-text-muted)] uppercase tracking-wider">Total Est. Value</span>
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
                            <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Deal Title <span class="text-rose-500">*</span></label>
                            <input type="text" x-model="editingDeal.title" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Company <span class="text-rose-500">*</span></label>
                            <select x-model="editingDeal.client_id" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500">
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
                                <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest">Products</label>
                                <span class="text-xs font-bold text-emerald-400" x-text="'Total Est: ' + formatIDR(calculateTotalEst())"></span>
                            </div>
                            <div class="space-y-2">
                                <template x-for="(p, idx) in editingDeal.products" :key="p.id">
                                    <div class="p-3 rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] space-y-2 relative group">
                                        <button type="button" @click="editingDeal.products.splice(idx, 1)" class="absolute top-2 right-2 text-[var(--cc-text-muted)] hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                        <select x-model="p.category" class="w-[90%] bg-transparent text-sm text-[var(--cc-text)] font-bold outline-none">
                                            <option class="text-slate-900" value="Mobil Short Term">Mobil Short Term</option>
                                            <option class="text-slate-900" value="Mobil Long Term">Mobil Long Term</option>
                                            <option class="text-slate-900" value="Bis Short Term">Bis Short Term</option>
                                            <option class="text-slate-900" value="Bis Long Term">Bis Long Term</option>
                                            <option class="text-slate-900" value="E-Voucher">E-Voucher</option>
                                            <option class="text-slate-900" value="Supir">Supir</option>
                                        </select>
                                        <div class="flex gap-2">
                                            <div class="w-20">
                                                <label class="text-[10px] text-[var(--cc-text-muted)] uppercase font-bold tracking-widest pl-1">Qty</label>
                                                <input type="number" min="1" x-model.number="p.quantity" class="w-full rounded-lg border border-[var(--cc-border)] bg-[var(--cc-modal-bg)] px-3 py-1.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                                            </div>
                                            <div class="flex-1">
                                                <label class="text-[10px] text-[var(--cc-text-muted)] uppercase font-bold tracking-widest pl-1">Unit Price</label>
                                                <div class="relative">
                                                    <span class="absolute left-3 top-1.5 text-sm text-[var(--cc-text-muted)]">Rp</span>
                                                    <input type="text" x-model="p.formattedPrice" @input="handlePriceInput(p, $event)" class="w-full rounded-lg border border-[var(--cc-border)] bg-[var(--cc-modal-bg)] pl-8 pr-3 py-1.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" />
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <input type="text" placeholder="Details / Note (Optional)" x-model="p.details" class="w-full rounded-lg border border-transparent bg-[var(--cc-modal-bg)]/50 px-3 py-1.5 text-xs text-[var(--cc-text)] outline-none focus:border-[var(--cc-border)] placeholder:text-slate-600" />
                                        </div>
                                    </div>
                                </template>
                                <button @click="addProduct()" type="button" class="w-full rounded-xl border border-dashed border-white/20 py-3 text-sm font-bold text-[var(--cc-text-muted)] hover:text-indigo-300 hover:border-indigo-400/50 hover:bg-indigo-500/10 transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    Add Product Item
                                </button>
                            </div>
                        </div>

                        <template x-if="targetStage === 'call_meeting'">
                            <div>
                                <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Activity Type</label>
                                <select x-model="subType" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500">
                                    <option value="Call">Call</option>
                                    <option value="Offline Meeting">Offline Meeting</option>
                                </select>
                            </div>
                        </template>

                        <div>
                            <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Note / Highlights</label>
                            <textarea x-model="note" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500 min-h-[80px]" placeholder="Add details..."></textarea>
                        </div>

                        <template x-if="targetStage === 'won' && modalMode !== 'history'">
                            <div class="bg-emerald-500/10 text-emerald-300 p-4 rounded-2xl text-sm border border-emerald-500/20">
                                <p class="font-bold">Deal Won!</p>
                                <p class="mt-1">100% of the Estimated Value (<span x-text="formatIDR(calculateTotalEst())"></span>) will be recognized as Actual Revenue.</p>
                            </div>
                        </template>

                        <template x-if="targetStage === 'lost' && modalMode !== 'history'">
                            <div>
                                <label class="block text-xs font-bold text-[var(--cc-text-muted)] uppercase tracking-widest mb-1.5">Lost Reason <span class="text-rose-500">*</span></label>
                                <textarea x-model="editingDeal.lost_reason" class="w-full rounded-xl border border-[var(--cc-border)] bg-[var(--cc-surface)] px-4 py-2.5 text-sm text-[var(--cc-text)] outline-none focus:border-indigo-500" rows="3" placeholder="Why was this deal lost?"></textarea>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <div class="p-5 bg-[var(--cc-bg)] rounded-b-3xl flex justify-end gap-3 border-t border-[var(--cc-border)] mt-auto">
                <button @click="closeModal()" class="px-4 py-2 text-sm font-bold text-[var(--cc-text-muted)] hover:text-[var(--cc-text)] transition">
                    <span x-text="modalMode === 'history' ? 'Close' : 'Cancel'"></span>
                </button>
                <template x-if="modalMode !== 'history'">
                    <button @click="saveDeal()" 
                            :disabled="isSaving || (targetStage === 'lost' && !editingDeal.lost_reason) || (modalMode === 'create' && (!editingDeal.title || !editingDeal.client_id))"
                            class="bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-[var(--cc-text)] px-6 py-2 rounded-xl justify-center items-center text-sm font-bold transition shadow-lg shadow-indigo-500/20">
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
        searchQuery: '',
        sortBy: 'updated_desc',
        isModalOpen: false,
        modalMode: 'create', // create, edit-stage, history
        modalTitle: '',
        targetStage: 'call_meeting',
        subType: 'Call',
        note: '',
        expandedHistoryId: null,
        isSaving: false,
        currentUserId: {{ auth()->id() }},
        currentUserRole: '{{ auth()->user()->role }}',
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
                d.expanded = false;
                if(d.products) {
                    d.products.forEach(p => p.formattedPrice = p.estimatedValue.toLocaleString('id-ID'));
                }
                return d;
            });
        },

        filteredDeals(stage) {
            let deals = this.rawDeals.filter(d => {
                if (d.stage !== stage) return false;
                if (this.selectedSalesFilter !== 'all' && d.sales_id != this.selectedSalesFilter) return false;
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const titleMatch = d.title && d.title.toLowerCase().includes(q);
                    const clientMatch = d.client_name && d.client_name.toLowerCase().includes(q);
                    if (!titleMatch && !clientMatch) return false;
                }
                return true;
            });
            
            deals.sort((a, b) => {
                let valA = parseFloat(a.stage === 'won' ? a.final_value : a.estimated_value) || 0;
                let valB = parseFloat(b.stage === 'won' ? b.final_value : b.estimated_value) || 0;
                let dateA = new Date(a.updated_at).getTime() || 0;
                let dateB = new Date(b.updated_at).getTime() || 0;
                let createdA = new Date(a.created_at).getTime() || 0;
                let createdB = new Date(b.created_at).getTime() || 0;

                if (this.sortBy === 'value_desc') return valB - valA;
                if (this.sortBy === 'value_asc') return valA - valB;
                if (this.sortBy === 'created_desc') return createdB - createdA;
                return dateB - dateA;
            });
            
            return deals;
        },

        getDealCount(stage) {
            return this.filteredDeals(stage).length;
        },

        getStageValueSum(stage) {
            return this.filteredDeals(stage).reduce((sum, d) => {
                let val = parseFloat(d.stage === 'won' ? d.final_value : d.estimated_value) || 0;
                return sum + val;
            }, 0);
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

        getStageAgeString(dateStr) {
            if (!dateStr) return '0 hari';
            const diffTime = Math.abs(new Date() - new Date(dateStr));
            const diffDays = diffTime / (1000 * 60 * 60 * 24);
            const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
            if (diffHours < 24) {
                if (diffHours === 0) {
                    const diffMins = Math.floor(diffTime / (1000 * 60));
                    if (diffMins === 0) return 'Baru saja';
                    return `${diffMins} menit`;
                }
                return `${diffHours} jam`;
            }
            return `${Math.floor(diffDays)} hari`;
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

        async openHistoryModal(deal) {
            this.editingDeal = JSON.parse(JSON.stringify(deal));
            this.modalMode = 'history';
            this.modalTitle = 'Loading History...';
            this.expandedHistoryId = null;
            this.isModalOpen = true;

            try {
                const res = await fetch(`/api/opportunities/${deal.id}/history`);
                const data = await res.json();
                this.editingDeal.history_timeline = data.history_timeline || [];
                if(typeof this.editingDeal.history_timeline === 'string') {
                    this.editingDeal.history_timeline = JSON.parse(this.editingDeal.history_timeline);
                }
                this.modalTitle = 'Deal History';
            } catch (e) {
                console.error('Error fetching history:', e);
                this.modalTitle = 'Error loading history';
            }
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

    }
}
</script>
@endpush
