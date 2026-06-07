<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>{{ $title ?? 'Golden Bird CRM | Command Center' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* ── SPA SHELL ── */
        html, body { height: 100%; overflow: hidden; }

        .app-shell {
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: var(--cc-bg);
            transition: background 200ms ease;
        }

        #sidebar {
            flex-shrink: 0;
            width: 224px;
            min-width: 224px;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
            z-index: 40;
            scrollbar-width: none;
            transition: width 250ms cubic-bezier(0.16,1,0.3,1), min-width 250ms cubic-bezier(0.16,1,0.3,1);
        }
        #sidebar::-webkit-scrollbar { display: none; }
        #sidebar.collapsed { width: 0 !important; min-width: 0 !important; overflow: hidden !important; }

        .app-main {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        #topbar { flex-shrink: 0; height: 56px; }

        #content-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
        }
        #content-area::-webkit-scrollbar { width: 4px; }
        #content-area::-webkit-scrollbar-track { background: transparent; }
        #content-area::-webkit-scrollbar-thumb { background: var(--cc-scrollbar); border-radius: 4px; }
        #content-area::-webkit-scrollbar-thumb:hover { background: var(--cc-border-h); }

        /* Mobile sidebar */
        @media (max-width: 767px) {
            #sidebar {
                position: fixed;
                left: 0; top: 0; bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.2s ease;
                z-index: 50;
            }
            #sidebar.open { transform: translateX(0); }
            #sidebar.collapsed { transform: translateX(-100%) !important; width: 224px !important; min-width: 224px !important; }
            .app-shell { flex-direction: column; }
            #content-area { overflow-x: hidden; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div class="app-shell">

    {{-- ── SIDEBAR ── --}}
    <x-sidebar/>

    {{-- ── MAIN COLUMN ── --}}
    <div class="app-main">

        {{-- Mobile topbar --}}
        <div class="md:hidden flex items-center justify-between px-5 py-3 flex-shrink-0"
             style="background:var(--cc-sidebar);border-bottom:1px solid var(--cc-border)">
            <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center"
                     style="background:var(--cc-accent-dim);border:1px solid rgba(20,104,168,0.2)">
                    <span class="material-symbols-outlined text-[16px]" style="color:var(--cc-accent)">directions_bus</span>
                </div>
                <span class="text-sm font-bold" style="color:var(--cc-text)">Golden Bird CRM</span>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="CRM_Palette.toggle()" class="topbar-icon-btn w-8 h-8" title="Search ⌘K">
                    <span class="material-symbols-outlined text-[19px]">search</span>
                </button>
                <button id="hamburger-btn" class="topbar-icon-btn w-8 h-8" title="Menu">
                    <span class="material-symbols-outlined text-[22px]">menu</span>
                </button>
            </div>
        </div>

        {{-- Topbar --}}
        <x-topbar/>

        {{-- Flash messages --}}
        <x-flash/>

        {{-- ── CONTENT ── --}}
        <div id="content-area">
            <div class="p-6">
                @yield('content')
            </div>
        </div>

    </div>

</div>

{{-- Mobile backdrop --}}
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden"
     onclick="closeSidebar()"></div>

{{-- FAB --}}
<x-fab/>

{{-- Keyboard shortcuts help overlay (? key) --}}
<div id="shortcuts-overlay" style="display:none;position:fixed;inset:0;background:var(--cc-overlay);z-index:9500;align-items:center;justify-content:center" onclick="this.style.display='none'">
    <div style="background:var(--cc-modal-bg);border:1px solid var(--cc-border-h);border-radius:16px;padding:28px 32px;max-width:480px;width:90vw">
        <div style="font-size:15px;font-weight:700;color:var(--cc-text);margin-bottom:16px">⌨️ Keyboard Shortcuts</div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px">
            @foreach([
                ['⌘K','Command Palette'], ['⌘B','Focus / Presentation Mode'],
                ['N','New Opportunity'],   ['E','Inline Edit'],
                ['W','Mark Won'],          ['A','Add Activity'],
                ['V','360° View'],         ['F','Filter Panel'],
                ['1-7','Jump to Nav'],     ['?','Show Shortcuts'],
                ['Esc','Close Modals'],    ['⌘D','Toggle Dark/Light'],
            ] as [$key, $desc])
            <div style="display:flex;align-items:center;gap:8px">
                <span class="kbd-hint">{{ $key }}</span>
                <span style="color:var(--cc-text-muted)">{{ $desc }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    /* ── Sidebar mobile ── */
    function openSidebar()  {
        document.getElementById('sidebar').classList.add('open');
        document.getElementById('sidebar-backdrop').classList.remove('hidden');
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('open');
        document.getElementById('sidebar-backdrop').classList.add('hidden');
    }
    const hamburgerBtn = document.getElementById('hamburger-btn');
    if (hamburgerBtn) hamburgerBtn.addEventListener('click', openSidebar);

    /* ── ? key: show shortcuts overlay ── */
    document.addEventListener('keydown', e => {
        const tag = document.activeElement?.tagName?.toLowerCase();
        if (['input','textarea','select'].includes(tag)) return;
        if (e.key === '?') {
            const el = document.getElementById('shortcuts-overlay');
            el.style.display = el.style.display === 'none' ? 'flex' : 'none';
        }
    });
</script>

@stack('scripts')
</body>
</html>
