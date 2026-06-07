@php
    $role = Auth::user()->role ?? '';
    $roleIcons  = ['director'=>'👔','gm'=>'🏢','manager'=>'📊','sales'=>'💼','operational'=>'🚗','finance'=>'💰'];
    $roleLabels = ['director'=>'Director HQ','gm'=>'General Manager','manager'=>'Manager','sales'=>'Sales Officer','operational'=>'Operations','finance'=>'Finance'];
@endphp

<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-50 flex flex-col min-h-screen" style="width:224px; min-width:224px;">

    {{-- Brand --}}
    <div class="hidden md:flex items-center gap-3 px-5 py-5" style="border-bottom:1px solid rgba(255,255,255,0.05);">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,rgba(0,229,255,0.15),rgba(59,130,246,0.15)); border:1px solid rgba(0,229,255,0.2);">
            <span class="material-symbols-outlined text-[20px]" style="color:#00e5ff;">directions_bus</span>
        </div>
        <div>
            <div class="text-sm font-bold text-white leading-tight">{{ __('ui.bluebird_crm') }}</div>
            <div class="text-[9px] uppercase tracking-widest font-semibold" style="color:#334155;">{{ __('ui.command_center') }}</div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-grow overflow-y-auto px-3 py-3 space-y-0.5">

        <div class="nav-section-label">{{ __('ui.main') }}</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ Request::routeIs('dashboard') ? 'active' : '' }}">
            <span class="material-symbols-outlined">space_dashboard</span>
            <span>{{ __('ui.dashboard') }}</span>
        </a>

        @if(in_array($role, ['director','gm','manager','sales']))
        <div class="nav-section-label">{{ __('ui.sales') }}</div>

        <a href="{{ route('pipeline.index') }}" class="nav-item {{ Request::routeIs('pipeline*','opportunities*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">view_kanban</span>
            <span>{{ __('ui.sales_pipeline') }}</span>
        </a>

        <a href="{{ route('clients.index') }}" class="nav-item {{ Request::routeIs('clients*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">corporate_fare</span>
            <span>{{ __('ui.clients') }}</span>
        </a>

        <a href="{{ route('activities.index') }}" class="nav-item {{ Request::routeIs('activities*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">event_note</span>
            <span>{{ __('ui.activity_log') }}</span>
        </a>
        @endif

        @if(in_array($role, ['director','gm','manager']))
        <div class="nav-section-label">{{ __('ui.operations') }}</div>

        <a href="{{ route('approvals.index') }}" class="nav-item {{ Request::routeIs('approvals*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">fact_check</span>
            <span>{{ __('ui.approval_queue') }}</span>
        </a>
        @endif

        @if(in_array($role, ['director','gm','manager','operational']))
        <a href="{{ route('fleet.index') }}" class="nav-item {{ Request::routeIs('fleet*','vehicles*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">local_shipping</span>
            <span>{{ __('ui.fleet_armada') }}</span>
        </a>
        @endif

        <a href="{{ route('bookings.index') }}" class="nav-item {{ Request::routeIs('bookings*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">route</span>
            <span>{{ __('ui.dispatch') }}</span>
        </a>

        @if(in_array($role, ['director','gm','manager','finance']))
        <div class="nav-section-label">{{ __('ui.finance') }}</div>

        <a href="{{ route('subscriptions.index') }}" class="nav-item {{ Request::routeIs('subscriptions*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">autorenew</span>
            <span>{{ __('ui.subscriptions') }}</span>
        </a>

        <a href="{{ route('vouchers.index') }}" class="nav-item {{ Request::routeIs('vouchers*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">confirmation_number</span>
            <span>{{ __('ui.e_voucher') }}</span>
        </a>

        <a href="{{ route('finance.index') }}" class="nav-item {{ Request::routeIs('finance*','invoices*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">payments</span>
            <span>{{ __('ui.finance_billing') }}</span>
        </a>
        @endif

        @if(in_array($role, ['director','gm','manager','sales']))
        <a href="{{ route('products.index') }}" class="nav-item {{ Request::routeIs('products*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">menu_book</span>
            <span>{{ __('ui.price_book') }}</span>
        </a>

        <div class="nav-section-label">{{ __('ui.intelligence') }}</div>

        <a href="{{ route('kpi.index') }}" class="nav-item {{ Request::routeIs('kpi*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">leaderboard</span>
            <span>{{ __('ui.kpi_target') }}</span>
        </a>
        @endif

        @if(in_array($role, ['director','gm','manager']))
        <a href="{{ route('analytics.index') }}" class="nav-item {{ Request::routeIs('analytics*') ? 'active' : '' }}">
            <span class="material-symbols-outlined">query_stats</span>
            <span>{{ __('ui.reports_analytics') }}</span>
        </a>
        @endif

    </nav>

    {{-- Sidebar Footer --}}
    <div class="px-3 py-4" style="border-top:1px solid rgba(255,255,255,0.05);">
        <div class="flex items-center gap-2.5 mb-3 px-1">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-base flex-shrink-0" style="background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08);">
                {{ $roleIcons[$role] ?? '👤' }}
            </div>
            <div class="overflow-hidden">
                <div class="text-xs font-bold text-slate-200 truncate">{{ Auth::user()->name ?? 'User' }}</div>
                <div class="text-[9px] uppercase tracking-wider font-semibold truncate" style="color:#334155;">{{ $roleLabels[$role] ?? strtoupper($role) }}</div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 rounded-lg text-xs font-semibold transition-all"
                style="color:#ef4444; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.15);"
                onmouseover="this.style.background='rgba(239,68,68,0.18)'"
                onmouseout="this.style.background='rgba(239,68,68,0.08)'">
                <span class="material-symbols-outlined text-[15px]">logout</span>
                <span>{{ __('ui.logout') }}</span>
            </button>
        </form>
    </div>

</aside>
