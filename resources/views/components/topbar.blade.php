@php
    $role = Auth::user()->role ?? '';
    $roleBadge = ['director'=>'👔 Director','gm'=>'🏢 GM','manager'=>'📊 Manager','sales'=>'💼 Sales','operational'=>'🚗 Ops','pool'=>'🅿️ Pool','finance'=>'💰 Finance'];
    $roleEmoji = ['director'=>'👔','gm'=>'🏢','manager'=>'📊','sales'=>'💼','operational'=>'🚗','pool'=>'🅿️','finance'=>'💰'];
@endphp

<header id="topbar" class="topbar sticky top-0 h-14 flex items-center justify-between px-5 z-40 flex-shrink-0">

    {{-- LEFT: Breadcrumb + page title --}}
    <div class="flex items-center gap-2 text-xs min-w-0">
        <span class="font-bold text-xs hidden md:block" style="color:var(--cc-text-faint)">{{ __('ui.bluebird_crm') }}</span>
        <span class="material-symbols-outlined text-[13px] hidden md:block" style="color:var(--cc-text-faint)">chevron_right</span>
        <span class="font-black uppercase tracking-widest text-[11px] truncate" style="color:var(--cc-accent)">
            @yield('header_title', 'Dashboard')
        </span>
    </div>

    {{-- CENTER: Quick search bar (desktop) --}}
    <button onclick="CRM_Palette.toggle()"
            class="hidden lg:flex items-center gap-2 px-4 py-2 rounded-xl text-[12px] transition-all duration-150 mx-4"
            style="background:var(--cc-card);border:1px solid var(--cc-border);color:var(--cc-text-muted);min-width:200px;max-width:300px;flex:1">
        <span class="material-symbols-outlined text-[15px]" style="color:var(--cc-accent)">search</span>
        <span>{{ __('ui.quick_search') }}</span>
        <span class="kbd-hint ml-auto">⌘K</span>
    </button>

    {{-- RIGHT: Action icons --}}
    <div class="flex items-center gap-1.5">

        {{-- Search (mobile) --}}
        <button class="topbar-icon-btn lg:hidden" onclick="CRM_Palette.toggle()" title="Search ⌘K">
            <span class="material-symbols-outlined text-[19px]">search</span>
        </button>

        {{-- Notification bell --}}
        <button class="topbar-icon-btn" id="notif-btn" onclick="CRM_Notif.toggle()" title="Notifications ⌘J">
            <span class="material-symbols-outlined text-[19px]">notifications</span>
            <span id="notif-badge" class="notif-badge" style="display:flex">4</span>
        </button>

        {{-- Dark/Light toggle --}}
        <x-theme-toggle />

        {{-- Focus / Presentation mode --}}
        <button class="topbar-icon-btn hidden md:flex" onclick="CRM_Focus.toggle()" title="Presentation mode ⌘B">
            <span class="material-symbols-outlined text-[19px]">fullscreen</span>
        </button>

        {{-- Widget Customizer --}}
        <button class="topbar-icon-btn hidden md:flex" data-widget-toggle onclick="CRM_Widget && CRM_Widget.open()" title="Customize dashboard widgets">
            <span class="material-symbols-outlined text-[19px]">dashboard_customize</span>
        </button>

        {{-- Keyboard shortcuts hint --}}
        <button class="topbar-icon-btn hidden md:flex" onclick="CRM_Toast.show('⌨️ Shortcuts: ⌘K=search · N=new · E=edit · W=won · A=activity · 1-7=nav · ⌘B=focus · ?=help', 'info', 6000)" title="Keyboard shortcuts ?">
            <span class="material-symbols-outlined text-[19px]">keyboard</span>
        </button>

        {{-- Divider --}}
        <div class="w-px h-6 mx-1 hidden md:block" style="background:var(--cc-border)"></div>

        {{-- Language switch --}}
        <div class="hidden sm:flex items-center rounded-xl overflow-hidden"
             style="background:var(--cc-card);border:1px solid var(--cc-border);">
            <a href="{{ route('language.switch', 'id') }}"
               class="px-2.5 py-1.5 text-[10px] font-black"
               style="color:{{ app()->getLocale() === 'id' ? '#fff' : 'var(--cc-text-muted)' }};background:{{ app()->getLocale() === 'id' ? 'var(--cc-accent)' : 'transparent' }};">ID</a>
            <a href="{{ route('language.switch', 'en') }}"
               class="px-2.5 py-1.5 text-[10px] font-black"
               style="color:{{ app()->getLocale() === 'en' ? '#fff' : 'var(--cc-text-muted)' }};background:{{ app()->getLocale() === 'en' ? 'var(--cc-accent)' : 'transparent' }};">EN</a>
        </div>

        {{-- Role badge --}}
        <div class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-[11px] font-bold cursor-default"
             style="background:var(--cc-card);border:1px solid var(--cc-border);color:var(--cc-text-muted)">
            <span>{{ $roleEmoji[$role] ?? '👤' }}</span>
            <span class="hidden md:inline">{{ $roleBadge[$role] ?? strtoupper($role) }}</span>
        </div>

        {{-- Live badge --}}
        <span class="badge-live hidden sm:inline-flex">
            <span class="pulse-dot" style="width:5px;height:5px;flex-shrink:0"></span>
            <span class="hidden md:inline">{{ __('ui.live') }}</span>
        </span>

    </div>
</header>
