<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>{{ $title ?? 'Golden Bird CRM | B2B Fleet Management' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#f8f9ff",
                        "on-surface-variant": "#434652",
                        "surface-container-low": "#eff4ff",
                        "on-primary": "#ffffff",
                        "primary-container": "#1e4fa8",
                        "on-error": "#ffffff",
                        "secondary-fixed-dim": "#a4c9ff",
                        "on-secondary-fixed": "#001c39",
                        "on-primary-container": "#b2c7ff",
                        "inverse-primary": "#b0c6ff",
                        "tertiary-container": "#24548d",
                        "secondary-fixed": "#d4e3ff",
                        "primary": "#003887",
                        "surface-container-highest": "#d3e4fe",
                        "on-tertiary-fixed-variant": "#124780",
                        "on-primary-fixed-variant": "#04429b",
                        "tertiary": "#003c73",
                        "surface-variant": "#d3e4fe",
                        "tertiary-fixed": "#d4e3ff",
                        "background": "#f8f9ff",
                        "surface-container": "#e5eeff",
                        "surface": "#f8f9ff",
                        "tertiary-fixed-dim": "#a5c8ff",
                        "on-error-container": "#93000a",
                        "error-container": "#ffdad6",
                        "surface-dim": "#cbdbf5",
                        "surface-container-lowest": "#ffffff",
                        "outline-variant": "#c3c6d4",
                        "inverse-on-surface": "#eaf1ff",
                        "error": "#ba1a1a",
                        "surface-container-high": "#dce9ff",
                        "secondary": "#1960a6",
                        "on-surface": "#0b1c30",
                        "on-tertiary": "#ffffff",
                        "on-secondary": "#ffffff",
                        "on-background": "#0b1c30",
                        "on-secondary-fixed-variant": "#004883",
                        "inverse-surface": "#213145",
                        "secondary-container": "#7ab3ff",
                        "on-tertiary-container": "#a7c9ff",
                        "surface-tint": "#2d5bb4",
                        "on-primary-fixed": "#001945",
                        "outline": "#737783",
                        "primary-fixed-dim": "#b0c6ff",
                        "on-secondary-container": "#00447e",
                        "primary-fixed": "#d9e2ff",
                        "on-tertiary-fixed": "#001c3a"
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .sidebar-active-glow { box-shadow: 0 0 15px rgba(164, 201, 255, 0.15); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c3c6d4; border-radius: 10px; }
    </style>
    @stack('styles')
</head>
<body class="bg-surface-bright text-on-surface min-h-screen flex flex-col md:flex-row">

    <!-- Mobile Header -->
    <div class="md:hidden w-full bg-primary text-on-primary flex justify-between items-center px-6 py-4 shadow-md z-50">
        <div class="flex items-center gap-2">
            <div class="p-1 bg-secondary rounded-lg">
                <span class="material-symbols-outlined text-on-primary text-[20px]">directions_bus</span>
            </div>
            <span class="text-lg font-bold tracking-wider">Golden Bird CRM</span>
        </div>
        <button id="hamburger-btn" class="p-1 text-on-primary focus:outline-none rounded">
            <span class="material-symbols-outlined text-[24px]">menu</span>
        </button>
    </div>

    <!-- SIDEBAR -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-200 ease-in-out z-50 bg-primary w-64 flex flex-col py-6 px-4 shadow-xl min-h-screen text-on-primary">

        <!-- Brand -->
        <div class="mb-8 flex items-center gap-3 px-2 hidden md:flex">
            <div class="p-1.5 bg-secondary rounded-lg">
                <span class="material-symbols-outlined text-on-primary text-[24px]">directions_bus</span>
            </div>
            <div>
                <h1 class="text-base font-bold text-on-primary leading-tight">Golden Bird CRM</h1>
                <p class="text-[9px] uppercase tracking-widest text-on-primary-container opacity-85 font-semibold">B2B Fleet Management</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-grow space-y-1 overflow-y-auto px-1">
            @php $role = Auth::user()->role ?? ''; @endphp

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('dashboard') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span class="text-sm font-semibold">Dashboard</span>
            </a>

            @if(in_array($role, ['director','gm','manager','sales']))
            <a href="{{ route('pipeline.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('pipeline*','opportunities*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">funnel</span>
                <span class="text-sm font-semibold">Sales Pipeline</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager']))
            <a href="{{ route('approvals.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('approvals*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">approval</span>
                <span class="text-sm font-semibold">Approval Queue</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','sales','finance']))
            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('clients*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">business</span>
                <span class="text-sm font-semibold">Clients</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','operational']))
            <a href="{{ route('fleet.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('fleet*','vehicles*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">local_shipping</span>
                <span class="text-sm font-semibold">Fleet Armada</span>
            </a>
            @endif

            <a href="{{ route('bookings.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('bookings*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">distance</span>
                <span class="text-sm font-semibold">Dispatch (Booking)</span>
            </a>

            @if(in_array($role, ['director','gm','manager','finance']))
            <a href="{{ route('subscriptions.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('subscriptions*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">autorenew</span>
                <span class="text-sm font-semibold">Subscriptions</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','finance']))
            <a href="{{ route('vouchers.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('vouchers*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">confirmation_number</span>
                <span class="text-sm font-semibold">E-Voucher</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','sales']))
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('products*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">menu_book</span>
                <span class="text-sm font-semibold">Price Book</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','finance']))
            <a href="{{ route('finance.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('finance*','invoices*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">payments</span>
                <span class="text-sm font-semibold">Finance & Billing</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','sales']))
            <a href="{{ route('kpi.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('kpi*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">leaderboard</span>
                <span class="text-sm font-semibold">KPI & Target</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager','sales']))
            <a href="{{ route('activities.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('activities*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">event_note</span>
                <span class="text-sm font-semibold">Activity Log</span>
            </a>
            @endif

            @if(in_array($role, ['director','gm','manager']))
            <a href="{{ route('analytics.index') }}" class="flex items-center gap-3 py-2.5 px-4 rounded-xl transition duration-150 {{ Request::routeIs('analytics*') ? 'bg-secondary text-on-secondary sidebar-active-glow' : 'text-on-primary-container hover:bg-primary-container hover:text-on-primary-container' }}">
                <span class="material-symbols-outlined">assessment</span>
                <span class="text-sm font-semibold">Reports & Analytics</span>
            </a>
            @endif
        </nav>

        <!-- Sidebar Footer -->
        <div class="mt-auto pt-4 border-t border-primary-container">
            @php
                $roleIcons = ['director'=>'👔','gm'=>'🏢','manager'=>'📊','sales'=>'💼','operational'=>'🚗','finance'=>'💰'];
                $roleLabels = ['director'=>'Director HQ','gm'=>'GM HQ','manager'=>'Manager HQ','sales'=>'Sales Officer','operational'=>'Ops Head','finance'=>'Finance Admin'];
            @endphp
            <div class="flex items-center gap-3 mb-4 px-2">
                <div class="w-10 h-10 rounded-full bg-secondary flex items-center justify-center text-xl border-2 border-secondary-fixed-dim flex-shrink-0">
                    {{ $roleIcons[$role] ?? '👤' }}
                </div>
                <div class="overflow-hidden">
                    <p class="font-bold text-xs text-on-primary truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-on-primary-container opacity-85 truncate uppercase tracking-wider font-semibold">{{ $roleLabels[$role] ?? strtoupper($role) }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 py-2 text-red-200 hover:text-white hover:bg-red-700 rounded-xl transition duration-200 font-semibold text-sm">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-grow min-h-screen flex flex-col bg-surface-bright">
        <!-- TOP APP BAR -->
        <header class="sticky top-0 h-16 flex items-center justify-between px-6 bg-surface-container-lowest border-b border-outline-variant z-40 shadow-sm">
            <nav class="flex items-center gap-1.5 text-xs font-semibold text-slate-500">
                <span class="text-slate-400">Golden Bird CRM</span>
                <span class="material-symbols-outlined text-sm opacity-50">chevron_right</span>
                <span class="text-primary font-extrabold uppercase tracking-wide">@yield('header_title', 'Dashboard')</span>
            </nav>
            <div class="flex items-center gap-3">
                @php
                    $roleColors = ['director'=>'bg-purple-100 text-purple-800 border-purple-200','gm'=>'bg-blue-100 text-blue-800 border-blue-200','manager'=>'bg-green-100 text-green-800 border-green-200','sales'=>'bg-yellow-100 text-yellow-800 border-yellow-200','operational'=>'bg-orange-100 text-orange-800 border-orange-200','finance'=>'bg-emerald-100 text-emerald-800 border-emerald-200'];
                    $roleColorClass = $roleColors[$role] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                @endphp
                <span class="px-2.5 py-1 rounded-full text-[10px] border font-bold uppercase tracking-wider {{ $roleColorClass }}">
                    {{ strtoupper($role) }} GATEWAY
                </span>
                <span class="flex items-center gap-1 bg-green-50 text-green-700 px-2 py-1 rounded-md text-[9px] font-extrabold uppercase border border-green-200 tracking-wide">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    Live
                </span>
            </div>
        </header>

        <!-- FLASH MESSAGES -->
        <div class="px-6 pt-4">
            @if (session('success'))
                <div class="mb-2 flex items-center gap-2 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm font-medium">
                    <span class="material-symbols-outlined text-green-600 text-[18px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mb-2 flex items-center gap-2 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl text-sm font-medium">
                    <span class="material-symbols-outlined text-red-600 text-[18px]">error</span>
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <!-- PAGE CONTENT -->
        <div class="p-6 flex-grow">
            @yield('content')
        </div>
    </main>

    <!-- Mobile Sidebar JS -->
    <script>
        const hamburgerBtn = document.getElementById('hamburger-btn');
        const sidebar = document.getElementById('sidebar');
        if (hamburgerBtn) {
            let open = false;
            hamburgerBtn.addEventListener('click', () => {
                open = !open;
                sidebar.classList.toggle('-translate-x-full', !open);
            });
            document.addEventListener('click', (e) => {
                if (open && !sidebar.contains(e.target) && !hamburgerBtn.contains(e.target)) {
                    open = false;
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
