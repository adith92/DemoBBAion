@extends('layouts.app')

@section('header_title', 'Sales Pipeline')

@push('styles')
<style>
    /* ── KANBAN SPA LAYOUT ──
       The content-area (#content-area) handles vertical scroll.
       The kanban board itself scrolls HORIZONTALLY inside a fixed-height wrapper.
       Each column: header is sticky, cards scroll vertically inside the column.
    ── */

    /* ── PIPELINE: Override layout defaults ──────────────────────────────────
       The SPA layout has:
         #content-area { overflow-x: hidden }   ← blocks kanban horiz scroll
         #content-area > .p-6 { padding: 24px } ← shrinks height
       We override both here so kanban gets full freedom.
    ──────────────────────────────────────────────────────────────────────── */
    #content-area {
        overflow-x: visible !important;
        overflow: hidden !important;  /* kanban manages its own scroll */
    }
    #content-area > div.p-6 {
        padding: 0 !important;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    /* kanban-page-wrapper: fill the now-padding-free content-area */
    .kanban-page-wrapper {
        display: flex;
        flex-direction: column;
        height: 100%;          /* fill parent — no magic number needed */
        min-height: 0;
    }

    .kanban-page-header {
        flex-shrink: 0;
        padding-bottom: 12px;
    }

    .kanban-summary-bar {
        flex-shrink: 0;
        padding-bottom: 12px;
    }

    /* Horizontal scroll container for the board */
    .kanban-scroll-x {
        flex: 1;
        min-height: 0;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 12px;
        cursor: grab;
    }
    .kanban-scroll-x:active { cursor: grabbing; }
    .kanban-scroll-x::-webkit-scrollbar { height: 6px; }
    .kanban-scroll-x::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); border-radius: 3px; }
    .kanban-scroll-x::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    .kanban-scroll-x::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    /* Board: full height of scroll container */
    .kanban-board {
        display: flex;
        gap: 14px;
        height: 100%;
        min-width: max-content;
        align-items: flex-start;
    }

    /* Each column: fixed width, full height, flex column */
    .kanban-column {
        width: 280px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        height: 100%;
        max-height: 100%;
    }

    /* Column header: sticky at top of column, never scrolls */
    .kanban-col-header {
        flex-shrink: 0;
        margin-bottom: 8px;
    }

    /* Drop zone: fills remaining column height, cards scroll here */
    .kanban-drop-zone {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        gap: 10px;
        border-radius: 12px;
        padding: 6px 4px 6px 4px;
        transition: background 0.15s, outline 0.15s;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
    .kanban-drop-zone::-webkit-scrollbar { width: 3px; }
    .kanban-drop-zone::-webkit-scrollbar-track { background: transparent; }
    .kanban-drop-zone::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 3px; }
    .kanban-drop-zone.sortable-over { background: rgba(0,229,255,0.04); outline: 2px dashed rgba(0,229,255,0.25); outline-offset: -2px; }
    /* Kanban card — adapts to dark/light via CSS vars */
    .kanban-card {
        background: var(--cc-card);
        border: 1px solid var(--cc-border);
        border-radius: 12px;
        padding: 14px;
        cursor: grab;
        user-select: none;
        transition: transform 0.15s, box-shadow 0.15s, border-color 0.15s;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    }
    .kanban-card:hover {
        border-color: var(--cc-border-h);
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        background: var(--cc-card-hover);
    }
    html.dark .kanban-card { box-shadow: 0 1px 4px rgba(0,0,0,0.4); }
    html.dark .kanban-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.5); }
    .kanban-card.sortable-ghost { opacity: 0.25; }
    .kanban-card.sortable-drag  { opacity: 1; transform: rotate(1.5deg) scale(1.02); box-shadow: 0 12px 32px rgba(0,0,0,0.25); border-color: var(--cc-accent); cursor: grabbing; }

    /* Stage column headers — lighter background, darker text for readability */
    .stage-header { border-radius: 12px; padding: 14px 16px; }
    /* Dark mode: subtle tint */
    html.dark .stage-call { background: rgba(20,104,168,0.05);  border: 1px solid rgba(20,104,168,0.15); }
    html.dark .stage-pro  { background: rgba(20,104,168,0.10);  border: 1px solid rgba(20,104,168,0.20); }
    html.dark .stage-prop { background: rgba(20,104,168,0.15);  border: 1px solid rgba(20,104,168,0.25); }
    html.dark .stage-neg  { background: rgba(20,104,168,0.20);  border: 1px solid rgba(20,104,168,0.30); }
    html.dark .stage-won  { background: rgba(16,185,129,0.08);  border: 1px solid rgba(16,185,129,0.18); }
    html.dark .stage-lost { background: rgba(239,68,68,0.06);   border: 1px solid rgba(239,68,68,0.14); }
    /* Light mode: very light pastel — high contrast text */
    html.light .stage-call { background: rgba(20,104,168,0.04);  border: 1px solid rgba(20,104,168,0.12); }
    html.light .stage-pro  { background: rgba(20,104,168,0.07);  border: 1px solid rgba(20,104,168,0.18); }
    html.light .stage-prop { background: rgba(20,104,168,0.10);  border: 1px solid rgba(20,104,168,0.24); }
    html.light .stage-neg  { background: rgba(20,104,168,0.13);  border: 1px solid rgba(20,104,168,0.30); }
    html.light .stage-won  { background: rgba(16,185,129,0.07);  border: 1px solid rgba(16,185,129,0.20); }
    html.light .stage-lost { background: rgba(239,68,68,0.05);   border: 1px solid rgba(239,68,68,0.16); }
    /* Fallback for when no class set */
    .stage-call { background: rgba(20,104,168,0.05);  border: 1px solid rgba(20,104,168,0.15); }
    .stage-pro  { background: rgba(20,104,168,0.10);  border: 1px solid rgba(20,104,168,0.20); }
    .stage-prop { background: rgba(20,104,168,0.15);  border: 1px solid rgba(20,104,168,0.25); }
    .stage-neg  { background: rgba(20,104,168,0.20);  border: 1px solid rgba(20,104,168,0.30); }
    .stage-won  { background: rgba(16,185,129,0.08);  border: 1px solid rgba(16,185,129,0.18); }
    .stage-lost { background: rgba(239,68,68,0.06);   border: 1px solid rgba(239,68,68,0.14); }
    /* Stage header text — force readable contrast */
    .stage-header .text-slate-100 { color: var(--cc-text) !important; }
    html.light .stage-header span[style*="color"] { filter: saturate(1.3) brightness(0.7); }

    /* Drop zone background — subtle */
    .kanban-drop-zone { background: rgba(0,0,0,0.02); }
    html.dark .kanban-drop-zone { background: rgba(255,255,255,0.01); }
    /* Card text overrides for light mode */
    html.light .kanban-card h3 { color: var(--cc-text) !important; }
    html.light .kanban-card .text-slate-100 { color: var(--cc-text) !important; }
    html.light .kanban-card .text-slate-200 { color: var(--cc-text) !important; }
    html.light .kanban-card .text-slate-500,
    html.light .kanban-card .text-slate-600 { color: var(--cc-text-muted) !important; }
    html.light .kanban-card .border-white\/5 { border-color: rgba(0,0,0,0.08) !important; }
    html.light .kanban-card .bg-blue-500\/20 { background: rgba(59,130,246,0.15) !important; }
    html.light .kanban-card .text-blue-400 { color: #1d4ed8 !important; }

    /* Summary bar cards in light mode */
    html.light .kanban-summary-bar .cc-card { box-shadow: 0 1px 3px rgba(0,0,0,0.08); }

    .edit-input { background: var(--cc-input-bg); border: 1px solid var(--cc-input-bd); color: var(--cc-text); border-radius: 8px; padding: 6px 10px; font-size: 13px; width: 100%; outline: none; }
    .edit-input:focus { border-color: var(--cc-accent); box-shadow: 0 0 0 3px var(--cc-accent-dim); }
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal-box { background: #0f0f1a; border: 1px solid rgba(255,255,255,0.08); border-radius: 20px; width: 100%; max-width: 800px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; animation: modal-in 0.25s cubic-bezier(0.16,1,0.3,1); }
    @keyframes modal-in { from { opacity:0; transform: scale(0.95) translateY(16px); } to { opacity:1; transform: scale(1) translateY(0); } }
    .modal-body { overflow-y: auto; flex: 1; }
    .tab-btn { padding: 7px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; color: #475569; cursor: pointer; transition: all 0.15s; border: 1px solid transparent; }
    .tab-btn.active { background: rgba(0,229,255,0.1); color: #00e5ff; border-color: rgba(0,229,255,0.2); }
    .tab-btn:hover:not(.active) { background: rgba(255,255,255,0.04); color: #94a3b8; }
    .info-row { display: flex; padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05); gap: 12px; }
    .info-row:last-child { border-bottom: none; }
    .info-label { font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; min-width: 140px; padding-top: 1px; }
    .info-val { font-size: 13px; color: #cbd5e1; flex: 1; }
    #toast { position: fixed; bottom: 24px; right: 24px; z-index: 200; min-width: 240px; padding: 12px 18px; border-radius: 12px; font-size: 13px; font-weight: 600; display: none; }
    #toast.success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
    #toast.error   { background: rgba(239,68,68,0.15);  border: 1px solid rgba(239,68,68,0.3);  color: #f87171; }
    @media (max-width: 768px) {
        .kanban-column { width: 256px; }
        .kanban-page-wrapper { height: 100%; } /* inherit from content-area */
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-spin { animation: spin 1s linear infinite; }

    /* ── TABLE VIEW ── */
    #pipeline-table-view { display: none; flex: 1; overflow: auto; }
    #pipeline-table-view.active { display: block; }
    .pipeline-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .pipeline-table thead th {
        position: sticky; top: 0; z-index: 2;
        background: var(--cc-card); color: var(--cc-text-muted);
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
        padding: 10px 14px; border-bottom: 1px solid var(--cc-border); text-align: left;
        white-space: nowrap;
    }
    .pipeline-table tbody tr { border-bottom: 1px solid var(--cc-border); transition: background 0.1s; cursor: pointer; }
    .pipeline-table tbody tr:hover { background: var(--cc-row-hover); }
    .pipeline-table tbody td { padding: 10px 14px; color: var(--cc-text); vertical-align: middle; }
    .pipeline-table tbody td.muted { color: var(--cc-text-muted); }
    .stage-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 9px; border-radius: 20px; font-size: 11px; font-weight: 700;
    }

    /* Momentum scroll for board on touch */
    @media (hover: none) {
        .kanban-scroll-x { cursor: default; }
    }
</style>
@endpush

@section('content')
<div
    x-data="kanbanBoard()"
    x-init="init()"
    @keydown.escape.window="closeModal()"
    class="kanban-page-wrapper"
>

    {{-- PAGE HEADER --}}
    <div class="kanban-page-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-[22px] font-extrabold text-slate-100 tracking-tight flex items-center gap-2">
                <span class="material-symbols-outlined text-[#00e5ff]">view_kanban</span>
                Sales Pipeline
            </h1>
            <p class="text-[13px] text-slate-500 mt-0.5">Drag & drop deal antar tahap — semua tersimpan otomatis</p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-[16px]">search</span>
                <input x-model="search" type="text" placeholder="Cari deal, client..." class="pl-9 pr-4 py-2 text-[13px] dark-input rounded-xl w-48"/>
            </div>
            <select x-model="filterStage" class="dark-input text-[13px] py-2 px-3 rounded-xl">
                <option value="">Semua Stage</option>
                @foreach($stages as $s)
                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            {{-- Sales filter (manager/gm/director) --}}
            @if($salesUsers->isNotEmpty())
            <form method="GET" id="pipeline-filter-form">
                <input type="hidden" name="sort_by" value="{{ $sortBy }}">
                <select name="filter_sales" onchange="document.getElementById('pipeline-filter-form').submit()"
                        class="dark-input text-[13px] py-2 px-3 rounded-xl">
                    <option value="">Semua Sales</option>
                    @foreach($salesUsers as $su)
                    <option value="{{ $su->id }}" {{ request('filter_sales') == $su->id ? 'selected' : '' }}>{{ $su->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif
            {{-- Sort --}}
            <form method="GET" id="pipeline-sort-form">
                @if(request('filter_sales'))<input type="hidden" name="filter_sales" value="{{ request('filter_sales') }}">@endif
                <select name="sort_by" onchange="document.getElementById('pipeline-sort-form').submit()"
                        class="dark-input text-[13px] py-2 px-3 rounded-xl">
                    <option value="updated"    {{ $sortBy === 'updated'    ? 'selected' : '' }}>Terbaru diupdate</option>
                    <option value="newest"     {{ $sortBy === 'newest'     ? 'selected' : '' }}>Terbaru dibuat</option>
                    <option value="value_desc" {{ $sortBy === 'value_desc' ? 'selected' : '' }}>Nilai ↓</option>
                    <option value="value_asc"  {{ $sortBy === 'value_asc'  ? 'selected' : '' }}>Nilai ↑</option>
                    <option value="close_date" {{ $sortBy === 'close_date' ? 'selected' : '' }}>Close date</option>
                </select>
            </form>
            {{-- View Toggle: Board / List / Table --}}
            <div class="view-toggle">
                <button class="view-btn active" id="view-board" onclick="setKanbanView('board')" title="Board view">
                    <span class="material-symbols-outlined text-[15px]">view_kanban</span>
                    <span class="hidden sm:inline">Board</span>
                </button>
                <button class="view-btn" id="view-list" onclick="setKanbanView('list')" title="List view">
                    <span class="material-symbols-outlined text-[15px]">view_list</span>
                    <span class="hidden sm:inline">List</span>
                </button>
                <button class="view-btn" id="view-table" onclick="setKanbanView('table')" title="Table view">
                    <span class="material-symbols-outlined text-[15px]">table</span>
                    <span class="hidden sm:inline">Table</span>
                </button>
            </div>

            <a href="{{ route('opportunities.create') }}" class="btn-primary" data-add-activity id="fab-pipeline-add">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span class="hidden sm:inline">Tambah Deal</span>
            </a>
        </div>
    </div>

    {{-- SUMMARY BAR --}}
    @php
    $stageConf = [
        'call_meeting'=> ['label'=>'Call/Meeting','color'=>'#38bdf8','icon'=>'phone_in_talk'],
        'prospecting' => ['label'=>'Prospekting','color'=>'#0ea5e9','icon'=>'radar'],
        'proposal'    => ['label'=>'Proposal',   'color'=>'#0284c7','icon'=>'description'],
        'negotiation' => ['label'=>'Negosiasi',  'color'=>'#0369a1','icon'=>'handshake'],
        'won'         => ['label'=>'Menang',     'color'=>'#10b981','icon'=>'emoji_events'],
        'lost'        => ['label'=>'Kalah',      'color'=>'#ef4444','icon'=>'cancel'],
    ];
    @endphp
    <div class="kanban-summary-bar grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
        @foreach($stages as $s)
        @php $c = $stageConf[$s]; $col = $kanban[$s]; @endphp
        <div class="cc-card px-4 py-3 flex items-center gap-3">
            <span class="material-symbols-outlined text-[20px]" style="color:{{ $c['color'] }}">{{ $c['icon'] }}</span>
            <div>
                <div class="text-[10px] font-bold uppercase tracking-wide text-slate-500">{{ $c['label'] }}</div>
                <div class="text-[15px] font-bold text-slate-100"
                     id="count-{{ $s }}" data-count-badge="{{ $s }}">{{ $col['count'] }}</div>
                <div class="text-[10px] text-slate-500"
                     data-value-badge="{{ $s }}">Rp {{ number_format($col['total_value'],0,',','.') }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- KANBAN BOARD: horizontal scroll wrapper --}}
    <div class="kanban-scroll-x" id="kanban-scroll-x">
        <div class="kanban-board">

            @foreach($stages as $stage)
            @php
            $c = $stageConf[$stage];
            $col = $kanban[$stage];
            $opps = $col['opportunities'];
            $stageClass = ['call_meeting'=>'stage-call','prospecting'=>'stage-pro','proposal'=>'stage-prop','negotiation'=>'stage-neg','won'=>'stage-won','lost'=>'stage-lost'][$stage];
            @endphp

            <div class="kanban-column" x-show="filterStage === '' || filterStage === '{{ $stage }}'">

                {{-- Column header: fixed, does NOT scroll with cards --}}
                <div class="kanban-col-header">
                    <div class="{{ $stageClass }} stage-header flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]" style="color:{{ $c['color'] }}">{{ $c['icon'] }}</span>
                            <span class="text-sm font-bold text-slate-100 uppercase tracking-wide">{{ $c['label'] }}</span>
                        </div>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full"
                              style="background:rgba(255,255,255,0.1); color:{{ $c['color'] }}"
                              id="badge-{{ $stage }}" data-count-badge="{{ $stage }}">{{ $col['count'] }}</span>
                    </div>
                    <div class="text-[11px] text-slate-500 font-semibold px-1 pb-1"
                         data-col-value="{{ $stage }}">Rp {{ number_format($col['total_value'],0,',','.') }}</div>
                </div>

                {{-- Drop zone: scrolls independently per column --}}
                <div class="kanban-drop-zone" data-stage="{{ $stage }}" id="zone-{{ $stage }}">
                    @forelse($opps as $opp)
                    <div class="kanban-card"
                         data-id="{{ $opp->id }}"
                         data-deal-id="{{ $opp->id }}"
                         data-deal-title="{{ addslashes($opp->title) }}"
                         data-deal-stage="{{ $opp->stage }}"
                         data-deal-num="{{ $opp->opp_number }}"
                         data-stage="{{ $opp->stage }}"
                         data-value="{{ $opp->estimated_value ?? 0 }}"
                         x-show="matchesSearch('{{ addslashes($opp->title) }}','{{ addslashes($opp->client->company_name ?? '') }}')"
                    >
                        @php
                            $lastAct   = $opp->activityLogs->max('created_at');
                            $daysSince = $lastAct ? now()->diffInDays($lastAct) : 99;
                            $stageDays = $opp->updated_at->diffInDays(now());
                            $risk      = $daysSince + ($stageDays * 0.5);
                            $hlEmoji   = $risk < 7  ? '💚' : ($risk < 14 ? '💛' : '❤️');
                            $hlClass   = $risk < 7  ? 'health-green' : ($risk < 14 ? 'health-yellow' : 'health-red');
                            $hlLabel   = $risk < 7  ? 'Healthy' : ($risk < 14 ? 'Warming' : 'At Risk');
                        @endphp

                        {{-- Card top row --}}
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="flex items-center gap-1.5 min-w-0">
                                <span class="text-[10px] text-slate-600 font-mono truncate">{{ $opp->opp_number }}</span>
                                <span class="{{ $hlClass }}" title="{{ $hlLabel }} · Last activity: {{ $daysSince }}d ago · Stage: {{ $stageDays }}d">{{ $hlEmoji }}</span>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button
                                    @click.stop="openEdit({{ $opp->id }},'{{ addslashes($opp->title) }}',{{ $opp->estimated_value ?? 'null' }},'{{ $opp->expected_close_date?->format('Y-m-d') ?? '' }}','{{ addslashes($opp->notes ?? '') }}',{{ $opp->pax ?? 'null' }})"
                                    class="p-0.5 rounded text-slate-600 hover:text-slate-300 transition-colors" title="Edit (E)" data-inline-edit>
                                    <span class="material-symbols-outlined text-[14px]">edit</span>
                                </button>
                                <button
                                    @click.stop="open360({{ $opp->id }})"
                                    class="p-0.5 rounded text-slate-600 hover:text-[#00e5ff] transition-colors" title="360° View (V)" data-view-360>
                                    <span class="material-symbols-outlined text-[14px]">360</span>
                                </button>
                            </div>
                        </div>

                        {{-- Title --}}
                        <h3 class="text-[13px] font-semibold leading-snug line-clamp-2 mb-2" style="color:var(--cc-text)">{{ $opp->title }}</h3>

                        {{-- Client (click-to-reveal) --}}
                        <div x-data="{ show: false }" class="mb-2">
                            <button @click.stop="show = !show"
                                    class="flex items-center gap-1.5 text-[12px] hover:opacity-80 transition-opacity w-full text-left"
                                    style="color:var(--cc-text-muted)">
                                <span class="material-symbols-outlined text-[13px]">corporate_fare</span>
                                <span x-show="!show" class="truncate italic text-slate-600 text-[11px]">Klik untuk lihat klien</span>
                                <span x-show="show" x-cloak class="truncate">{{ $opp->client->company_name ?? '-' }}</span>
                                <span class="material-symbols-outlined text-[12px] ml-auto" x-text="show ? 'visibility_off' : 'visibility'"></span>
                            </button>
                            <div x-show="show" x-cloak class="mt-1 pl-5">
                                <a href="{{ route('clients.show', $opp->client_id) }}"
                                   class="text-[11px] text-blue-400 hover:underline" @click.stop>
                                    {{ $opp->client->pic_name ?? '' }}
                                    @if($opp->client?->phone) · {{ $opp->client->phone }} @endif
                                </a>
                            </div>
                        </div>

                        {{-- Value --}}
                        @if($opp->estimated_value)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[13px] font-bold text-slate-100">Rp {{ number_format((float)$opp->estimated_value,0,',','.') }}</span>
                            @if($opp->pax)
                            <span class="text-[11px] text-slate-500">{{ $opp->pax }} pax</span>
                            @endif
                        </div>
                        @endif

                        {{-- Close date --}}
                        @if($opp->expected_close_date)
                        <div class="flex items-center gap-1.5 text-[11px] mb-2 {{ $opp->expected_close_date->isPast() && !in_array($opp->stage,['won','lost']) ? 'text-red-400' : 'text-slate-500' }}">
                            <span class="material-symbols-outlined text-[12px]">calendar_month</span>
                            {{ $opp->expected_close_date->format('d M Y') }}
                            @if($opp->expected_close_date->isPast() && !in_array($opp->stage,['won','lost']))
                            <span class="text-[10px] font-bold ml-1">OVERDUE</span>
                            @endif
                        </div>
                        @endif

                        {{-- Discount warning --}}
                        @if($opp->discount_percent > 0 && !$opp->discount_approved)
                        <div class="flex items-center gap-1.5 text-[11px] text-amber-400 bg-amber-500/10 rounded-lg px-2 py-1 mb-2">
                            <span class="material-symbols-outlined text-[12px]">warning</span>
                            Diskon {{ $opp->discount_percent }}% pending
                        </div>
                        @endif

                        {{-- Footer: sales + product --}}
                        <div class="flex items-center justify-between pt-2 border-t border-white/5">
                            @if($opp->sales && !auth()->user()->isSales())
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-blue-500/20 flex items-center justify-center text-blue-400 text-[10px] font-bold flex-shrink-0">
                                    {{ strtoupper(substr($opp->sales->name,0,1)) }}
                                </div>
                                <span class="text-[11px] text-slate-500 truncate max-w-[80px]">{{ $opp->sales->name }}</span>
                            </div>
                            @else
                            <div></div>
                            @endif
                            @if($opp->product)
                            <span class="text-[10px] text-slate-600 truncate max-w-[90px]">{{ $opp->product->name }}</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-10 text-[13px] text-slate-600" id="empty-{{ $stage }}">
                        <span class="material-symbols-outlined text-[32px] block mb-2 opacity-30">inbox</span>
                        Belum ada deal
                    </div>
                    @endforelse
                </div>

            </div>
            @endforeach
        </div>
    </div>

    {{-- ── TABLE VIEW (inline toggle, no redirect) ── --}}
    <div id="pipeline-table-view">
        <table class="pipeline-table">
            <thead>
                <tr>
                    <th>Deal</th>
                    <th>Client</th>
                    <th>Stage</th>
                    <th>Nilai</th>
                    <th>Close Date</th>
                    <th>Sales</th>
                    <th>Hari</th>
                </tr>
            </thead>
            <tbody>
                @php
                $allOpps = collect($kanban)->flatMap(fn($col) => $col['opportunities'])->sortByDesc('estimated_value');
                $stageColors = ['call_meeting'=>['bg'=>'rgba(20,104,168,0.05)','color'=>'#38bdf8'],'prospecting'=>['bg'=>'rgba(20,104,168,0.10)','color'=>'#0ea5e9'],'proposal'=>['bg'=>'rgba(20,104,168,0.15)','color'=>'#0284c7'],'negotiation'=>['bg'=>'rgba(20,104,168,0.20)','color'=>'#0369a1'],'won'=>['bg'=>'rgba(16,185,129,0.15)','color'=>'#10b981'],'lost'=>['bg'=>'rgba(239,68,68,0.12)','color'=>'#ef4444']];
                $stageLabels = ['call_meeting'=>'Call/Meeting','prospecting'=>'Prospekting','proposal'=>'Proposal','negotiation'=>'Negosiasi','won'=>'Menang','lost'=>'Kalah'];
                @endphp
                @forelse($allOpps as $opp)
                @php
                $sc = $stageColors[$opp->stage] ?? ['bg'=>'rgba(148,163,184,0.1)','color'=>'#94a3b8'];
                $days = $opp->created_at->diffInDays(now());
                @endphp
                <tr onclick="Alpine.store ? null : null" data-id="{{ $opp->id }}">
                    <td class="font-semibold max-w-[220px]">
                        <div class="truncate">{{ $opp->title }}</div>
                    </td>
                    <td class="muted">{{ $opp->client->company_name ?? '-' }}</td>
                    <td>
                        <span class="stage-pill" style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }}">
                            {{ $stageLabels[$opp->stage] ?? $opp->stage }}
                        </span>
                    </td>
                    <td class="font-semibold">
                        @if($opp->estimated_value)
                            <span style="color:var(--cc-text)">Rp {{ number_format($opp->estimated_value,0,',','.') }}</span>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="muted {{ $opp->expected_close_date && $opp->expected_close_date->isPast() && !in_array($opp->stage,['won','lost']) ? 'text-red-400' : '' }}">
                        {{ $opp->expected_close_date ? $opp->expected_close_date->format('d M Y') : '—' }}
                    </td>
                    <td class="muted">{{ $opp->sales->name ?? '—' }}</td>
                    <td class="muted text-right">{{ $days }}d</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-10 muted">Tidak ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ── INLINE EDIT MODAL ── --}}
    <div x-show="editModal.open"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="modal-overlay" style="display:none;" @click.self="editModal.open=false">
        <div class="modal-box max-w-md" @click.stop>
            <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 class="text-[15px] font-bold text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-[#00e5ff]">edit</span>Edit Deal
                </h3>
                <button @click="editModal.open=false" class="text-slate-500 hover:text-slate-300"><span class="material-symbols-outlined">close</span></button>
            </div>
            <form class="p-6 space-y-4" @submit.prevent="saveEdit()">
                <div>
                    <label class="dark-label block mb-1.5">Judul Deal *</label>
                    <input x-model="editModal.title" type="text" class="edit-input" required/>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="dark-label block mb-1.5">Nilai (Rp)</label>
                        <input x-model="editModal.estimated_value" type="number" min="0" class="edit-input" placeholder="0"/>
                    </div>
                    <div>
                        <label class="dark-label block mb-1.5">PAX</label>
                        <input x-model="editModal.pax" type="number" min="1" class="edit-input" placeholder="0"/>
                    </div>
                </div>
                <div>
                    <label class="dark-label block mb-1.5">Target Close</label>
                    <input x-model="editModal.expected_close_date" type="date" class="edit-input"/>
                </div>
                <div>
                    <label class="dark-label block mb-1.5">Notes</label>
                    <textarea x-model="editModal.notes" rows="3" class="edit-input resize-none"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="btn-primary flex-1" :disabled="editModal.saving">
                        <span x-show="!editModal.saving" class="material-symbols-outlined text-[16px]">save</span>
                        <span x-show="editModal.saving" class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>
                        <span x-text="editModal.saving ? 'Menyimpan...' : 'Simpan'"></span>
                    </button>
                    <button type="button" @click="editModal.open=false" class="btn-secondary">Batal</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── 360° MODAL ── --}}
    <div x-show="modal360.open"
         x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="modal-overlay" style="display:none;" @click.self="modal360.open=false">
        <div class="modal-box" @click.stop>
            {{-- Header --}}
            <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <div>
                    <h3 class="text-[15px] font-bold text-slate-100 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-[#00e5ff]">360</span>
                        <span x-text="modal360.data?.title ?? 'Detail Deal'"></span>
                    </h3>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5" x-text="modal360.data?.opp_number ?? ''"></div>
                </div>
                <div class="flex items-center gap-2.5">
                    <a :href="'/opportunities/' + modal360.id" class="btn-secondary text-[12px] py-1.5 px-3">
                        <span class="material-symbols-outlined text-[14px]">open_in_new</span>Full View
                    </a>
                    <button @click="modal360.open=false" class="text-slate-500 hover:text-slate-300"><span class="material-symbols-outlined">close</span></button>
                </div>
            </div>
            {{-- Tabs --}}
            <div class="px-6 py-3 flex gap-2 flex-wrap" style="border-bottom:1px solid rgba(255,255,255,0.04);">
                <button class="tab-btn" :class="{'active':modal360.tab==='info'}"     @click="modal360.tab='info'">Info</button>
                <button class="tab-btn" :class="{'active':modal360.tab==='activity'}" @click="modal360.tab='activity'">Aktivitas</button>
                <button class="tab-btn" :class="{'active':modal360.tab==='approval'}" @click="modal360.tab='approval'">Approval</button>
                <button class="tab-btn" :class="{'active':modal360.tab==='linked'}"   @click="modal360.tab='linked'">Data Terhubung</button>
            </div>
            {{-- Body --}}
            <div class="modal-body p-6">
                <div x-show="modal360.loading" class="flex items-center justify-center py-16">
                    <span class="material-symbols-outlined text-[32px] text-slate-600 animate-spin">progress_activity</span>
                </div>

                {{-- INFO --}}
                <div x-show="!modal360.loading && modal360.tab==='info'">
                    <template x-if="modal360.data">
                    <div>
                        <div class="grid grid-cols-2 gap-4 mb-5">
                            <div class="cc-card p-4">
                                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mb-1">Estimasi Nilai</div>
                                <div class="text-xl font-extrabold text-slate-100" x-text="'Rp ' + (modal360.data.estimated_value ? Number(modal360.data.estimated_value).toLocaleString('id-ID') : '-')"></div>
                            </div>
                            <div class="cc-card p-4">
                                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mb-1">Stage</div>
                                <div class="text-xl font-extrabold" :style="'color:' + stageColor(modal360.data.stage)" x-text="stageLabel(modal360.data.stage)"></div>
                            </div>
                        </div>
                        <div>
                            <div class="info-row"><span class="info-label">Client</span><span class="info-val" x-text="modal360.data.client?.company_name ?? '-'"></span></div>
                            <div class="info-row"><span class="info-label">Sales</span><span class="info-val" x-text="modal360.data.sales?.name ?? '-'"></span></div>
                            <div class="info-row"><span class="info-label">Produk</span><span class="info-val" x-text="modal360.data.product?.name ?? '-'"></span></div>
                            <div class="info-row"><span class="info-label">PAX</span><span class="info-val" x-text="modal360.data.pax ?? '-'"></span></div>
                            <div class="info-row">
                                <span class="info-label">Diskon</span>
                                <span class="info-val flex items-center gap-2">
                                    <span x-text="modal360.data.discount_percent > 0 ? modal360.data.discount_percent + '%' : '-'"></span>
                                    <span x-show="modal360.data.discount_percent > 0"
                                          :class="modal360.data.discount_approved ? 'status-badge status-confirmed' : 'status-badge status-pending'"
                                          x-text="modal360.data.discount_approved ? 'Approved' : 'Pending'"></span>
                                </span>
                            </div>
                            <div class="info-row"><span class="info-label">Target Close</span><span class="info-val" x-text="modal360.data.expected_close_date ? new Date(modal360.data.expected_close_date).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : '-'"></span></div>
                            <div class="info-row"><span class="info-label">Actual Close</span><span class="info-val" x-text="modal360.data.actual_close_date ? new Date(modal360.data.actual_close_date).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}) : '-'"></span></div>
                            <div x-show="modal360.data.lost_reason" class="info-row"><span class="info-label">Alasan Kalah</span><span class="info-val text-red-400" x-text="modal360.data.lost_reason"></span></div>
                            <div x-show="modal360.data.notes" class="info-row"><span class="info-label">Notes</span><span class="info-val text-slate-400" x-text="modal360.data.notes"></span></div>
                        </div>
                    </div>
                    </template>
                </div>

                {{-- ACTIVITY --}}
                <div x-show="!modal360.loading && modal360.tab==='activity'">
                    <template x-if="modal360.data">
                    <div class="space-y-3">
                        <template x-if="!modal360.data.activity_logs || modal360.data.activity_logs.length === 0">
                            <p class="text-slate-500 text-[13px] text-center py-8">Belum ada aktivitas</p>
                        </template>
                        <template x-for="log in (modal360.data.activity_logs || [])" :key="log.id">
                            <div class="flex gap-3 p-3 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                                <span class="material-symbols-outlined text-[16px] text-slate-500 mt-0.5 flex-shrink-0">radio_button_checked</span>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[12px] font-semibold text-slate-200" x-text="log.subject"></div>
                                    <div x-show="log.notes" class="text-[11px] text-slate-500 mt-0.5" x-text="log.notes"></div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[10px] text-slate-600" x-text="log.sales?.name ?? ''"></span>
                                        <span class="text-[10px] text-slate-700" x-text="log.activity_date ? new Date(log.activity_date).toLocaleDateString('id-ID') : ''"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                    </template>
                </div>

                {{-- APPROVAL --}}
                <div x-show="!modal360.loading && modal360.tab==='approval'">
                    <template x-if="modal360.data">
                    <div class="space-y-3">
                        <template x-if="!modal360.data.approval_requests || modal360.data.approval_requests.length === 0">
                            <p class="text-slate-500 text-[13px] text-center py-8">Tidak ada approval request</p>
                        </template>
                        <template x-for="req in (modal360.data.approval_requests || [])" :key="req.id">
                            <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[12px] font-bold text-slate-200">Level <span x-text="req.level"></span> Approval</span>
                                    <span :class="req.status === 'approved' ? 'status-badge status-confirmed' : req.status === 'rejected' ? 'status-badge status-cancelled' : 'status-badge status-pending'" x-text="req.status"></span>
                                </div>
                                <div class="text-[11px] text-slate-500">Diskon: <span class="text-amber-400 font-semibold" x-text="req.discount_percent + '%'"></span></div>
                                <div x-show="req.notes" class="text-[11px] text-slate-500 mt-1" x-text="req.notes"></div>
                            </div>
                        </template>
                    </div>
                    </template>
                </div>

                {{-- LINKED --}}
                <div x-show="!modal360.loading && modal360.tab==='linked'">
                    <template x-if="modal360.data">
                    <div class="space-y-3">
                        <div class="p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mb-2">Client</div>
                            <div class="text-[13px] font-semibold text-slate-200" x-text="modal360.data.client?.company_name ?? '-'"></div>
                            <div class="text-[11px] text-slate-500" x-text="modal360.data.client?.industry ?? ''"></div>
                            <div class="text-[11px] text-slate-500 mt-0.5" x-text="modal360.data.client?.pic_name ?? ''"></div>
                            <a x-show="modal360.data.client_id" :href="'/clients/' + modal360.data.client_id"
                               class="inline-flex items-center gap-1 mt-2 text-[11px] text-[#00e5ff] hover:underline">
                                <span class="material-symbols-outlined text-[13px]">open_in_new</span>Lihat Client
                            </a>
                        </div>
                        <div x-show="modal360.data.booking_id" class="p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mb-2">Booking Terhubung</div>
                            <a :href="'/bookings/' + modal360.data.booking_id" class="inline-flex items-center gap-1 text-[12px] text-[#00e5ff] hover:underline">
                                <span class="material-symbols-outlined text-[13px]">route</span>
                                Booking #<span x-text="modal360.data.booking_id"></span>
                            </a>
                        </div>
                        <div x-show="modal360.data.subscription_id" class="p-4 rounded-xl" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.05);">
                            <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wide mb-2">Subscription Terhubung</div>
                            <a :href="'/subscriptions/' + modal360.data.subscription_id" class="inline-flex items-center gap-1 text-[12px] text-[#00e5ff] hover:underline">
                                <span class="material-symbols-outlined text-[13px]">autorenew</span>
                                Subscription #<span x-text="modal360.data.subscription_id"></span>
                            </a>
                        </div>
                        <div x-show="!modal360.data.booking_id && !modal360.data.subscription_id" class="text-[13px] text-slate-500 text-center py-4">
                            Belum ada data terhubung
                        </div>
                    </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STAGE TRANSITION DIALOG ── --}}
    <div x-show="transitionDialog.open"
         class="modal-overlay" style="display:none;"
         @click.self="transitionDialog.open=false; transitionDialog.revertFn && transitionDialog.revertFn()">
        <div class="modal-box max-w-md" @click.stop>
            <div class="px-6 py-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <h3 class="text-[14px] font-bold text-slate-100 flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#00e5ff] text-[18px]">move_up</span>
                    Pindah Stage: <span class="text-[#00e5ff]" x-text="stageLabel(transitionDialog.pendingStage)"></span>
                </h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl p-3 mb-2">
                    <div class="text-[11px] text-blue-400 font-bold uppercase mb-1">Deal</div>
                    <div class="text-sm text-slate-200 font-semibold" x-text="transitionDialog.title"></div>
                </div>

                <div>
                    <label class="dark-label block mb-1.5">Estimasi Nilai Terbaru (Rp)</label>
                    <input x-model="transitionDialog.estimated_value" type="number" min="0" class="edit-input" placeholder="0"/>
                </div>

                <div>
                    <label class="dark-label block mb-1.5">Catatan Progres</label>
                    <textarea x-model="transitionDialog.notes" rows="2" class="edit-input resize-none" placeholder="Catatan aktivitas ini..."></textarea>
                </div>

                <div x-show="transitionDialog.pendingStage === 'lost'">
                    <label class="dark-label block mb-1.5 text-red-400">Alasan Kalah *</label>
                    <textarea x-model="transitionDialog.lost_reason" rows="2" class="edit-input resize-none border-red-500/30 focus:border-red-500" placeholder="Harga terlalu tinggi, dll..."></textarea>
                </div>

                <div class="flex gap-3 pt-2">
                    <button @click="confirmTransition()" class="btn-primary flex-1">
                        <span class="material-symbols-outlined text-[16px]">save</span>Simpan & Pindah
                    </button>
                    <button @click="transitionDialog.open=false; transitionDialog.revertFn && transitionDialog.revertFn()" class="btn-secondary">Batal</button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Toast notification --}}
<div id="toast"></div>
@endsection

@push('scripts')
<script>
function kanbanBoard() {
    return {
        search: '',
        filterStage: '',
        editModal: { open:false, saving:false, id:null, title:'', estimated_value:'', expected_close_date:'', notes:'', pax:'' },
        modal360:  { open:false, loading:false, id:null, data:null, tab:'info' },
        transitionDialog:{ open:false, pendingId:null, pendingStage:null, title:'', estimated_value:'', notes:'', lost_reason:'', revertFn:null },
        _toastTimer: null,

        init() {
            this.$nextTick(() => {
                this.initSortable();
                this.initBoardDragScroll();
            });
        },

        // Mouse drag-to-scroll on board (desktop)
        initBoardDragScroll() {
            const board = document.getElementById('kanban-scroll-x');
            if (!board) return;
            let isDragging = false, startX = 0, scrollLeft = 0;

            board.addEventListener('mousedown', (e) => {
                // Don't hijack if clicking a card or button
                if (e.target.closest('.kanban-card') || e.target.closest('button') || e.target.closest('a')) return;
                isDragging = true;
                startX = e.pageX - board.offsetLeft;
                scrollLeft = board.scrollLeft;
                board.style.cursor = 'grabbing';
            });
            document.addEventListener('mouseup',   () => { isDragging = false; board.style.cursor = ''; });
            document.addEventListener('mouseleave', () => { isDragging = false; board.style.cursor = ''; });
            board.addEventListener('mousemove', (e) => {
                if (!isDragging) return;
                e.preventDefault();
                const x = e.pageX - board.offsetLeft;
                board.scrollLeft = scrollLeft - (x - startX) * 1.2;
            });
        },

        initSortable() {
            document.querySelectorAll('.kanban-drop-zone').forEach(zone => {
                Sortable.create(zone, {
                    group: 'kanban',
                    animation: 180,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    draggable: '.kanban-card',
                    onOver: (evt) => { evt.to.classList.add('sortable-over'); },
                    onMove: (evt) => { evt.related.classList && evt.to.classList.add('sortable-over'); },
                    onEnd: (evt) => {
                        document.querySelectorAll('.kanban-drop-zone').forEach(z => z.classList.remove('sortable-over'));
                        const card     = evt.item;
                        const newZone  = evt.to;
                        const oldZone  = evt.from;
                        const oppId    = card.dataset.id;
                        const newStage = newZone.dataset.stage;
                        const oldStage = card.dataset.stage;
                        const oppTitle = card.dataset.dealTitle;
                        const oppValue = card.dataset.value;

                        if (newStage === oldStage) return;

                        const revertFn = () => {
                            const ref = oldZone.children[evt.oldIndex] || null;
                            oldZone.insertBefore(card, ref);
                            card.dataset.stage = oldStage;
                        };

                        this.transitionDialog = { 
                            open:true, 
                            pendingId:oppId, 
                            pendingStage:newStage, 
                            title:oppTitle,
                            estimated_value:oppValue || '',
                            notes:'',
                            lost_reason:'',
                            revertFn 
                        };
                    },
                });
            });
        },

        async doMoveStage(oppId, newStage, payload, revertFn) {
            const token = document.querySelector('meta[name="csrf-token"]').content;
            
            try {
                const res  = await fetch(`/opportunities/${oppId}/move-stage`, {
                    method: 'PATCH',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':token, 'Accept':'application/json' },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();
                if (!res.ok || !data.ok) { revertFn && revertFn(); this.toast(data.message ?? 'Gagal.', 'error'); return; }
                
                // Update DOM value data attribute
                const card = document.querySelector(`.kanban-card[data-id="${oppId}"]`);
                if (card && payload.estimated_value) {
                    card.dataset.value = payload.estimated_value;
                    
                    // Note: This only updates the data attribute, not the rendered HTML of the value.
                    // The easiest fix is to let the user see the updated value in 360 view or board refresh,
                    // but we can also manually update the rendered text if needed.
                    const valEl = card.querySelector('.text-[13px].font-bold.text-slate-100');
                    if (valEl) valEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(payload.estimated_value);
                }

                this.updateColumnCounts(data.summary);
                this.toast(data.message, 'success');
                // 🎊 Konfetti celebration when deal moves to Won!
                if (payload.stage === 'won') {
                    if (window.CRM_Confetti) CRM_Confetti.fire();
                }
            } catch(e) {
                revertFn && revertFn();
                this.toast('Koneksi error. Coba lagi.', 'error');
            }
        },

        confirmTransition() {
            const { pendingId, pendingStage, estimated_value, notes, lost_reason, revertFn } = this.transitionDialog;
            
            if (pendingStage === 'lost' && !lost_reason.trim()) { 
                this.toast('Alasan kalah wajib diisi.','error'); 
                return; 
            }

            const card = document.querySelector(`.kanban-card[data-id="${pendingId}"]`);
            if (card) card.dataset.stage = pendingStage;
            
            this.transitionDialog.open = false;
            
            this.doMoveStage(pendingId, pendingStage, {
                stage: pendingStage,
                estimated_value: estimated_value || null,
                notes: notes || null,
                lost_reason: lost_reason || null
            }, revertFn);
        },

        openEdit(id, title, value, closeDate, notes, pax) {
            this.editModal = { open:true, saving:false, id, title, estimated_value:value??'', expected_close_date:closeDate??'', notes:notes??'', pax:pax??'' };
        },

        async saveEdit() {
            this.editModal.saving = true;
            const token = document.querySelector('meta[name="csrf-token"]').content;
            try {
                const res = await fetch(`/opportunities/${this.editModal.id}/quick-update`, {
                    method: 'PATCH',
                    headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':token, 'Accept':'application/json' },
                    body: JSON.stringify({
                        title: this.editModal.title,
                        estimated_value: this.editModal.estimated_value || null,
                        expected_close_date: this.editModal.expected_close_date || null,
                        notes: this.editModal.notes || null,
                        pax: this.editModal.pax || null,
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.ok) { this.toast('Gagal menyimpan.','error'); return; }

                // Update DOM title
                const card = document.querySelector(`.kanban-card[data-id="${this.editModal.id}"]`);
                if (card) { const h = card.querySelector('h3'); if (h) h.textContent = data.opportunity.title; }

                this.editModal.open = false;
                this.toast('Deal berhasil diperbarui.','success');

                if (this.modal360.open && this.modal360.id == this.editModal.id) this.fetchModal360(this.editModal.id);
            } catch(e) { this.toast('Koneksi error.','error'); }
            finally    { this.editModal.saving = false; }
        },

        async open360(id) {
            this.modal360 = { open:true, loading:true, id, data:null, tab:'info' };
            await this.fetchModal360(id);
        },

        async fetchModal360(id) {
            this.modal360.loading = true;
            try {
                const res  = await fetch(`/opportunities/${id}/360`, { headers:{'Accept':'application/json'} });
                const data = await res.json();
                if (data.ok) { this.modal360.data = data.opportunity; }
                else { this.toast('Gagal memuat data.','error'); this.modal360.open = false; }
            } catch(e) { this.toast('Koneksi error.','error'); this.modal360.open = false; }
            finally    { this.modal360.loading = false; }
        },

        closeModal() {
            this.modal360.open = false;
            this.editModal.open = false;
            if (this.transitionDialog.open) { this.transitionDialog.revertFn && this.transitionDialog.revertFn(); this.transitionDialog.open = false; }
        },

        matchesSearch(title, client) {
            if (!this.search) return true;
            const q = this.search.toLowerCase();
            return title.toLowerCase().includes(q) || client.toLowerCase().includes(q);
        },

        stageColor(s) { return {prospecting:'#3b82f6',proposal:'#f59e0b',negotiation:'#f97316',won:'#10b981',lost:'#ef4444'}[s]??'#94a3b8'; },
        stageLabel(s) { return {prospecting:'Prospekting',proposal:'Proposal',negotiation:'Negosiasi',won:'Menang',lost:'Kalah'}[s]??s; },

        updateColumnCounts(summary) {
            const fmt = (n) => new Intl.NumberFormat('id-ID').format(Math.round(n));

            if (summary) {
                // Update counts from server-authoritative summary
                document.querySelectorAll('[data-count-badge]').forEach(el => {
                    const stage = el.dataset.countBadge;
                    if (summary[stage] !== undefined) el.textContent = summary[stage].count;
                });
                // Update Rupiah values in summary bar
                document.querySelectorAll('[data-value-badge]').forEach(el => {
                    const stage = el.dataset.valueBadge;
                    if (summary[stage] !== undefined) el.textContent = 'Rp ' + fmt(summary[stage].total);
                });
                // Update Rupiah in column headers
                document.querySelectorAll('[data-col-value]').forEach(el => {
                    const stage = el.dataset.colValue;
                    if (summary[stage] !== undefined) el.textContent = 'Rp ' + fmt(summary[stage].total);
                });
            } else {
                // Fallback: count DOM cards (no rupiah update possible)
                setTimeout(() => {
                    document.querySelectorAll('[data-count-badge]').forEach(el => {
                        const stage = el.dataset.countBadge;
                        const zone  = document.getElementById(`zone-${stage}`);
                        if (zone) el.textContent = zone.querySelectorAll('.kanban-card').length;
                    });
                }, 80);
            }
        },

        toast(msg, type='success') {
            // Use global CRM_Toast if available, fallback to local
            if (window.CRM_Toast) { CRM_Toast.show(msg, type); return; }
            const el = document.getElementById('toast');
            if (!el) return;
            el.textContent = msg;
            el.className = type;
            el.style.display = 'block';
            clearTimeout(this._toastTimer);
            this._toastTimer = setTimeout(() => { el.style.display = 'none'; }, 3200);
        },
    };
}

/* ── Kanban Multi-View Toggle ── */
function setKanbanView(view) {
    const board     = document.getElementById('kanban-scroll-x');
    const tableView = document.getElementById('pipeline-table-view');

    // Update button states
    ['board','list','table'].forEach(v => {
        const btn = document.getElementById(`view-${v}`);
        if (btn) btn.className = 'view-btn' + (v === view ? ' active' : '');
    });
    localStorage.setItem('kanban-view', view);

    if (!board) return;

    if (view === 'board') {
        board.style.display = '';
        board.style.flexDirection = '';
        board.style.overflowX = 'auto';
        if (tableView) tableView.classList.remove('active');
        document.querySelectorAll('.kanban-column').forEach(c => {
            c.style.width = '280px';
            c.style.flexDirection = 'column';
        });
    } else if (view === 'list') {
        board.style.display = 'flex';
        board.style.flexDirection = 'column';
        board.style.overflowX = 'hidden';
        if (tableView) tableView.classList.remove('active');
        document.querySelectorAll('.kanban-column').forEach(c => {
            c.style.width = '100%';
            c.style.maxWidth = 'none';
        });
        CRM_Toast && CRM_Toast.show('📋 List view aktif', 'info');
    } else if (view === 'table') {
        board.style.display = 'none';
        if (tableView) tableView.classList.add('active');
        CRM_Toast && CRM_Toast.show('📊 Table view aktif', 'info');
    }
}

// Restore saved view on load
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('kanban-view') || 'board';
    if (saved !== 'board') setKanbanView(saved);
    initBoardDragScroll && initBoardDragScroll();
    CRM_CtxMenu && CRM_CtxMenu.init();
});
</script>
@endpush
