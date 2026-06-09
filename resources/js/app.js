import Alpine from 'alpinejs';
import Sortable from 'sortablejs';
import Chart from 'chart.js/auto';
import { GridStack } from 'gridstack';

window.Alpine = Alpine;
window.Sortable = Sortable;
window.Chart = Chart;
window.GridStack = GridStack;

/* ════════════════════════════════════════════════════════════
   ALPINE STORES — native reactive state (replaces window.CRM_Theme etc)
   Access in Blade: $store.theme.toggle() / $store.notif.open etc
   ════════════════════════════════════════════════════════════ */
document.addEventListener('alpine:init', () => {

    /* ── Store: Theme ── */
    Alpine.store('theme', {
        mode: 'light',

        init() {
            // 1. Saved preference  2. Corporate light dashboard default
            const saved = localStorage.getItem('crm-theme');
            if (saved) {
                this.mode = saved;
            } else {
                this.mode = 'light';
            }
            this._apply();
        },

        toggle() {
            this.mode = this.mode === 'dark' ? 'light' : 'dark';
            this._apply();
            localStorage.setItem('crm-theme', this.mode);
            CRM_Toast.show(this.mode === 'dark' ? '🌑 Dark mode' : '☀️ Light mode', 'info', 2000);
        },

        _apply() {
            const html = document.documentElement;
            html.classList.remove('dark', 'light');
            html.classList.add(this.mode);
            const icon  = document.getElementById('theme-icon');
            const label = document.getElementById('theme-label');
            if (icon)  icon.textContent  = this.mode === 'dark' ? '☀️' : '🌙';
            if (label) label.textContent = this.mode === 'dark' ? 'Light' : 'Dark';
        },
    });

    /* ── Store: Focus/Presentation Mode ── */
    Alpine.store('focus', {
        active: false,

        toggle() {
            this.active = !this.active;
            const sb = document.getElementById('sidebar');
            if (sb) sb.classList.toggle('collapsed', this.active);
            CRM_Toast.show(
                this.active ? '⛶ Presentation mode ON' : '↩ Normal mode restored',
                'info', 2000
            );
        },
    });

    /* ── Store: Notifications ── */
    Alpine.store('notif', {
        open: false,
        unread: 4,
        items: [
            { icon: '🎉', title: 'Deal Won! PT Gojek',      body: 'Rp 4,8M closed by Sari Dewi',          time: '2m ago',  type: 'won',      url: '/pipeline'    },
            { icon: '⏳', title: '2 Approvals Pending',     body: 'PT Unilever 15% disc. — needs GM sign', time: '15m ago', type: 'approval', url: '/approvals'   },
            { icon: '⚠️', title: 'Deal Aging Alert',        body: 'PT BCA stuck in Proposal for 14 days',  time: '1h ago',  type: 'aging',    url: '/pipeline'    },
            { icon: '🚌', title: 'Fleet Alert',             body: 'Bus BB-0023 maintenance due tomorrow',  time: '2h ago',  type: 'fleet',    url: '/fleet'       },
            { icon: '📅', title: 'Follow-up Due Today',     body: '5 activities scheduled — Andi Pratama', time: '3h ago',  type: 'activity', url: '/activities'  },
            { icon: '💰', title: 'Invoice Overdue',         body: 'INV-240315-0012 PT Astra — 7 days',     time: '5h ago',  type: 'finance',  url: '/finance'     },
        ],

        toggle() {
            this.open = !this.open;
            const drawer = document.getElementById('notif-drawer');
            if (!drawer) { this._mount(); return; }
            drawer.classList.toggle('open', this.open);
            if (this.open) { this.unread = 0; this._updateBadge(); }
        },

        _updateBadge() {
            const badge = document.getElementById('notif-badge');
            if (!badge) return;
            badge.textContent = this.unread > 0 ? this.unread : '';
            badge.style.display = this.unread > 0 ? 'flex' : 'none';
        },

        _mount() {
            const el = document.createElement('div');
            el.id = 'notif-drawer';
            el.className = 'notif-drawer' + (this.open ? ' open' : '');
            el.innerHTML = `
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                    <div style="font-size:15px;font-weight:700;color:var(--cc-text)">🔔 Notifications</div>
                    <button onclick="Alpine.store('notif').toggle()" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--cc-text-muted)">✕</button>
                </div>
                ${this.items.map(n => `
                    <div class="notif-item" onclick="window.location='${n.url}';Alpine.store('notif').toggle()">
                        <span class="notif-icon">${n.icon}</span>
                        <div style="flex:1;min-width:0">
                            <div class="notif-title">${n.title}</div>
                            <div class="notif-body">${n.body}</div>
                            <div class="notif-time">${n.time}</div>
                        </div>
                    </div>`).join('')}
                <div style="margin-top:12px;text-align:center">
                    <a href="/notifications" style="font-size:12px;color:var(--cc-accent);text-decoration:none">View all →</a>
                </div>`;
            document.body.appendChild(el);
            document.addEventListener('click', e => {
                if (this.open && !el.contains(e.target) && !e.target.closest('#notif-btn')) {
                    this.open = false;
                    el.classList.remove('open');
                }
            });
            setTimeout(() => { el.classList.add('open'); this.unread = 0; this._updateBadge(); }, 10);
        },
    });

});

/* ════════════════════════════════════════════════════════════
   LEGACY COMPATIBILITY — keep window.CRM_* as thin proxies
   so existing Blade templates keep working without changes
   ════════════════════════════════════════════════════════════ */
window.CRM_Theme = {
    toggle() { Alpine.store('theme').toggle(); },
    apply(mode) { Alpine.store('theme').mode = mode; Alpine.store('theme')._apply(); },
    init() { Alpine.store('theme').init(); },
};
window.CRM_Focus = {
    get active() { return Alpine.store('focus').active; },
    toggle() { Alpine.store('focus').toggle(); },
};
window.CRM_Notif = {
    get open()   { return Alpine.store('notif').open; },
    get unread() { return Alpine.store('notif').unread; },
    toggle() { Alpine.store('notif').toggle(); },
    _updateBadge() { Alpine.store('notif')._updateBadge(); },
};

/* ════════════════════════════════════════════════════════════
   3. TOAST NOTIFICATIONS
   ════════════════════════════════════════════════════════════ */
window.CRM_Toast = {
    show(msg, type = 'info', duration = 3200) {
        let el = document.getElementById('crm-toast');
        if (!el) {
            el = document.createElement('div');
            el.id = 'crm-toast';
            el.style.cssText = `
                position:fixed;bottom:28px;left:50%;transform:translateX(-50%);
                padding:11px 20px;border-radius:12px;font-size:13px;font-weight:600;
                z-index:9999;pointer-events:none;max-width:420px;text-align:center;
                backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);
                transition:opacity 0.2s,transform 0.2s;
                opacity:0;transform:translateX(-50%) translateY(8px);
            `;
            document.body.appendChild(el);
        }
        const colors = {
            info:    'background:rgba(20,104,168,0.12);color:#1468a8;border:1px solid rgba(20,104,168,0.24)',
            success: 'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)',
            error:   'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)',
            warning: 'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)',
        };
        el.setAttribute('style', el.style.cssText + ';' + (colors[type] || colors.info));
        el.textContent = msg;
        requestAnimationFrame(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateX(-50%) translateY(0)';
        });
        clearTimeout(el._t);
        el._t = setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(-50%) translateY(8px)';
        }, duration);
    },
};

/* ════════════════════════════════════════════════════════════
   4. COMMAND PALETTE ⌘K
   ════════════════════════════════════════════════════════════ */
window.CRM_Palette = {
    open: false,
    query: '',
    selected: 0,
    results: [],

    navItems: [
        { icon: '🏠', label: 'Dashboard',         sub: 'Go to dashboard',       url: '/dashboard' },
        { icon: '🗂️', label: 'Sales Pipeline',    sub: 'Kanban board',          url: '/pipeline' },
        { icon: '🏢', label: 'Clients',            sub: 'Manage clients',        url: '/clients' },
        { icon: '📅', label: 'Activity Log',       sub: 'Sales activities',      url: '/activities' },
        { icon: '✅', label: 'Approval Queue',     sub: 'Pending approvals',     url: '/approvals' },
        { icon: '🚌', label: 'Fleet Armada',       sub: 'Vehicles & drivers',    url: '/fleet' },
        { icon: '🗺️', label: 'Dispatch',           sub: 'Bookings & routes',     url: '/bookings' },
        { icon: '📊', label: 'Analytics',          sub: 'Reports & charts',      url: '/analytics' },
        { icon: '🔄', label: 'Subscriptions',      sub: 'Recurring contracts',   url: '/subscriptions' },
        { icon: '⚙️', label: 'Settings',           sub: 'App settings',          url: '/settings' },
        { icon: '🌙', label: 'Toggle Dark/Light',  sub: 'Switch theme ⌘D',      action: 'theme' },
        { icon: '⛶',  label: 'Focus Mode',         sub: 'Presentation mode ⌘B', action: 'focus' },
        { icon: '➕', label: 'New Opportunity',    sub: 'Create deal (N)',        action: 'new-opp' },
    ],

    show() {
        this.open = true;
        this.query = '';
        this.selected = 0;
        this.results = [...this.navItems];
        this._render();
        setTimeout(() => document.getElementById('cmd-input')?.focus(), 50);
    },

    hide() {
        this.open = false;
        const el = document.getElementById('crm-cmd-palette');
        if (el) el.remove();
    },

    async search(q) {
        this.query = q;
        this.selected = 0;
        if (!q.trim()) {
            this.results = [...this.navItems];
            this._render(); return;
        }
        const ql = q.toLowerCase();
        const nav = this.navItems.filter(i =>
            i.label.toLowerCase().includes(ql) || i.sub.toLowerCase().includes(ql)
        );
        try {
            const res = await fetch(`/search/global?q=${encodeURIComponent(q)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error();
            const data = await res.json();
            this.results = [...(data.results || []), ...nav];
        } catch {
            this.results = nav;
        }
        this._render();
    },

    execute(item) {
        this.hide();
        if (item.action === 'theme')   { Alpine.store('theme').toggle(); return; }
        if (item.action === 'focus')   { Alpine.store('focus').toggle(); return; }
        if (item.action === 'new-opp') { document.getElementById('fab-quick-add')?.click(); return; }
        if (item.url) window.location.href = item.url;
    },

    moveDown() { this.selected = Math.min(this.selected + 1, this.results.length - 1); this._highlight(); },
    moveUp()   { this.selected = Math.max(this.selected - 1, 0); this._highlight(); },
    confirm()  { if (this.results[this.selected]) this.execute(this.results[this.selected]); },

    _highlight() {
        document.querySelectorAll('.cmd-result-item').forEach((el, i) => {
            el.classList.toggle('selected', i === this.selected);
        });
    },

    _render() {
        const list = document.getElementById('cmd-list');
        if (!list) return;
        list.innerHTML = this.results.length
            ? this.results.map((r, i) => `
                <div class="cmd-result-item ${i === this.selected ? 'selected' : ''}"
                     onclick="CRM_Palette.execute(CRM_Palette.results[${i}])">
                    <span class="cmd-icon">${r.icon || '📄'}</span>
                    <div style="flex:1;min-width:0">
                        <span class="cmd-label">${r.label}</span>
                        <span class="cmd-sub">${r.sub || ''}</span>
                    </div>
                    ${r.type ? `<span style="font-size:9px;font-weight:700;background:var(--cc-accent-dim);color:var(--cc-accent);padding:1px 6px;border-radius:8px;text-transform:uppercase">${r.type}</span>` : ''}
                </div>`).join('')
            : `<div style="padding:24px;text-align:center;color:var(--cc-text-faint);font-size:13px">No results for "${this.query}"</div>`;
    },

    mount() {
        if (document.getElementById('crm-cmd-palette')) return;
        const el = document.createElement('div');
        el.id = 'crm-cmd-palette';
        el.className = 'cmd-palette-overlay';
        el.innerHTML = `
            <div class="cmd-palette-box">
                <div style="display:flex;align-items:center;padding:0 16px;border-bottom:1px solid var(--cc-border)">
                    <span style="font-size:18px;margin-right:8px;color:var(--cc-text-muted)">🔍</span>
                    <input id="cmd-input" class="cmd-palette-input"
                           placeholder="Search clients, deals, pages... (⌘K)"
                           autocomplete="off" spellcheck="false" />
                    <span class="kbd-hint" style="flex-shrink:0">ESC</span>
                </div>
                <div id="cmd-list" style="max-height:340px;overflow-y:auto;padding:6px;"></div>
                <div style="padding:8px 16px;border-top:1px solid var(--cc-border);display:flex;gap:12px;font-size:11px;color:var(--cc-text-faint)">
                    <span>↑↓ navigate</span><span>↵ open</span><span>ESC close</span>
                </div>
            </div>`;
        document.body.appendChild(el);
        this._render();

        const input = document.getElementById('cmd-input');
        input?.addEventListener('input', e => this.search(e.target.value));
        input?.addEventListener('keydown', e => {
            if (e.key === 'ArrowDown')  { e.preventDefault(); this.moveDown(); }
            if (e.key === 'ArrowUp')    { e.preventDefault(); this.moveUp(); }
            if (e.key === 'Enter')      { e.preventDefault(); this.confirm(); }
            if (e.key === 'Escape')     this.hide();
        });
        el.addEventListener('click', e => { if (e.target === el) this.hide(); });
    },

    toggle() {
        if (this.open) { this.hide(); } else { this.mount(); this.show(); }
    },
};

/* ════════════════════════════════════════════════════════════
   5. WIN CELEBRATION KONFETTI 🎊
   ════════════════════════════════════════════════════════════ */
window.CRM_Confetti = {
    fire() {
        const colors = ['#1468a8','#4fa4c7','#2f9d7e','#d7a72f','#8d6bb8','#b94a48','#ffffff','#cd7c2f'];
        const count = 120;
        const fragment = document.createDocumentFragment();
        for (let i = 0; i < count; i++) {
            const el = document.createElement('div');
            const size = Math.random() * 10 + 6;
            el.style.cssText = `
                position:fixed;
                left:${Math.random() * 100}vw;top:-20px;
                width:${size}px;height:${size * (Math.random() > 0.5 ? 0.4 : 1)}px;
                background:${colors[Math.floor(Math.random() * colors.length)]};
                border-radius:${Math.random() > 0.5 ? '50%' : '2px'};
                z-index:99999;pointer-events:none;
                animation:confetti-fall ${1.5 + Math.random() * 2}s ease-in ${Math.random() * 0.8}s forwards;
            `;
            el.addEventListener('animationend', () => el.remove(), { once: true });
            fragment.appendChild(el);
        }
        document.body.appendChild(fragment);
        CRM_Toast.show('🎊 DEAL WON! Congratulations! 🏆', 'success', 4000);
    },
};

/* ════════════════════════════════════════════════════════════
   6. GLOBAL KEYBOARD SHORTCUTS (12 total)
   ════════════════════════════════════════════════════════════ */
window.CRM_Keys = {
    init() {
        document.addEventListener('keydown', e => {
            const tag    = document.activeElement?.tagName?.toLowerCase();
            const typing = ['input','textarea','select'].includes(tag);

            // ⌘K / Ctrl+K — command palette
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault(); CRM_Palette.toggle(); return;
            }
            // ⌘B / Ctrl+B — focus mode
            if ((e.metaKey || e.ctrlKey) && e.key === 'b') {
                e.preventDefault(); Alpine.store('focus').toggle(); return;
            }
            // ⌘D / Ctrl+D — dark/light toggle
            if ((e.metaKey || e.ctrlKey) && e.key === 'd') {
                e.preventDefault(); Alpine.store('theme').toggle(); return;
            }
            // Escape — close everything
            if (e.key === 'Escape') {
                CRM_Palette.hide();
                const nd = document.getElementById('notif-drawer');
                if (nd) { Alpine.store('notif').open = false; nd.classList.remove('open'); }
                const shortcuts = document.getElementById('shortcuts-overlay');
                if (shortcuts) shortcuts.style.display = 'none';
                return;
            }

            if (typing || e.metaKey || e.ctrlKey || e.altKey) return;

            switch (e.key) {
                case 'n': case 'N': e.preventDefault(); document.getElementById('fab-quick-add')?.click(); break;
                case 'f': case 'F': e.preventDefault(); document.querySelector('[data-filter-toggle]')?.click(); CRM_Toast.show('🔎 Filter panel', 'info'); break;
                case 'w': case 'W': e.preventDefault(); document.querySelector('[data-mark-won]')?.click(); break;
                case 'e': case 'E': e.preventDefault(); document.querySelector('[data-inline-edit]')?.click(); CRM_Toast.show('✏️ Inline edit mode', 'info'); break;
                case 'a': case 'A': e.preventDefault(); document.querySelector('[data-add-activity]')?.click(); CRM_Toast.show('📅 Add activity', 'info'); break;
                case 'v': case 'V': e.preventDefault(); document.querySelector('[data-view-360]')?.click(); break;
                case '1': window.location.href = '/dashboard';     break;
                case '2': window.location.href = '/pipeline';      break;
                case '3': window.location.href = '/clients';       break;
                case '4': window.location.href = '/bookings';      break;
                case '5': window.location.href = '/analytics';     break;
                case '6': window.location.href = '/fleet';         break;
                case '7': window.location.href = '/approvals';     break;
                case '?': {
                    const overlay = document.getElementById('shortcuts-overlay');
                    if (overlay) overlay.style.display = overlay.style.display === 'none' ? 'flex' : 'none';
                    break;
                }
            }
        });
    },
};

/* ════════════════════════════════════════════════════════════
   7. DASHBOARD SPARKLINES
   ════════════════════════════════════════════════════════════ */
window.CRM_Sparkline = {
    render(canvasId, data, color = '#1468a8') {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        // Destroy existing instance if any
        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();
        new Chart(canvas, {
            type: 'line',
            data: {
                labels: data.map((_, i) => i),
                datasets: [{
                    data,
                    borderColor: color,
                    borderWidth: 1.8,
                    fill: true,
                    backgroundColor: color + '14',
                    pointRadius: 0,
                    tension: 0.4,
                }]
            },
            options: {
                plugins: { legend: { display: false }, tooltip: { enabled: false } },
                scales: { x: { display: false }, y: { display: false } },
                animation: { duration: 500, easing: 'easeOutQuart' },
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 100,
            }
        });
    },
};

/* ════════════════════════════════════════════════════════════
   8. DEAL HEALTH SCORE
   ════════════════════════════════════════════════════════════ */
window.CRM_Health = {
    score(daysSinceActivity, stageDurationDays) {
        const total = daysSinceActivity + stageDurationDays * 0.5;
        if (total < 7)  return { cls: 'health-green',  emoji: '💚', label: 'Healthy' };
        if (total < 14) return { cls: 'health-yellow', emoji: '💛', label: 'Warming' };
        return              { cls: 'health-red',    emoji: '❤️',  label: 'At Risk' };
    },
};

/* ════════════════════════════════════════════════════════════
   9. RIGHT-CLICK CONTEXT MENU (Kanban cards)
   Usage: CRM_CtxMenu.init() — call once on kanban page
   Cards need data-deal-id, data-deal-title, data-deal-stage attrs
   ════════════════════════════════════════════════════════════ */
window.CRM_CtxMenu = {
    _el: null,
    _current: null,

    _menuItems: [
        { icon: '✏️',  label: 'Edit Deal',        action: 'edit'    },
        { icon: '🏆',  label: 'Mark as Won',       action: 'won'     },
        { icon: '❌',  label: 'Mark as Lost',      action: 'lost'    },
        { icon: '👤',  label: 'Reassign Sales',    action: 'assign'  },
        { icon: '📋',  label: 'Copy Deal No.',     action: 'copy'    },
        { icon: '➡️',  label: 'Move to Stage',     action: 'move'    },
        { icon: '🗑️',  label: 'Delete',            action: 'delete', cls: 'ctx-danger' },
    ],

    init() {
        if (this._el) return;
        // Build menu DOM
        this._el = document.createElement('div');
        this._el.id = 'crm-ctx-menu';
        this._el.style.cssText = `
            position:fixed;z-index:9990;min-width:180px;
            background:var(--cc-card);border:1px solid var(--cc-border-h);
            border-radius:12px;padding:4px;
            box-shadow:0 8px 32px rgba(0,0,0,0.5);
            backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
            display:none;animation:slide-down 0.15s ease;
        `;
        this._el.innerHTML = this._menuItems.map(m => `
            <div class="ctx-item ${m.cls || ''}" data-action="${m.action}"
                 style="display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:8px;
                        font-size:13px;cursor:pointer;transition:background 0.1s;
                        color:${m.cls === 'ctx-danger' ? 'var(--color-danger)' : 'var(--cc-text)'};"
                 onmouseover="this.style.background='var(--cc-accent-dim)'"
                 onmouseout="this.style.background='transparent'">
                <span style="font-size:15px;">${m.icon}</span>
                <span style="font-weight:500;">${m.label}</span>
            </div>`).join('');

        document.body.appendChild(this._el);

        // Click handler
        this._el.addEventListener('click', e => {
            const item = e.target.closest('[data-action]');
            if (!item || !this._current) return;
            this._handle(item.dataset.action, this._current);
            this.hide();
        });

        // Dismiss
        document.addEventListener('click', e => {
            if (!this._el.contains(e.target)) this.hide();
        });
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') this.hide();
        });

        // Attach to kanban cards (and future cards via delegation)
        document.addEventListener('contextmenu', e => {
            const card = e.target.closest('.kanban-card[data-deal-id]');
            if (!card) return;
            e.preventDefault();
            this.show(e.clientX, e.clientY, {
                id:    card.dataset.dealId,
                title: card.dataset.dealTitle,
                stage: card.dataset.dealStage,
                num:   card.dataset.dealNum,
                card,
            });
        });
    },

    show(x, y, data) {
        this._current = data;
        this._el.style.display = 'block';
        // Keep menu inside viewport
        const vw = window.innerWidth, vh = window.innerHeight;
        const mw = 190, mh = this._menuItems.length * 38 + 8;
        this._el.style.left = (x + mw > vw ? x - mw : x) + 'px';
        this._el.style.top  = (y + mh > vh ? y - mh : y) + 'px';
    },

    hide() {
        if (this._el) this._el.style.display = 'none';
        this._current = null;
    },

    _handle(action, deal) {
        switch (action) {
            case 'edit':
                deal.card?.querySelector('[data-inline-edit]')?.click();
                break;
            case 'won':
                deal.card?.querySelector('[data-mark-won]')?.click();
                break;
            case 'lost':
                if (confirm(`Mark "${deal.title}" as Lost?`)) {
                    fetch(`/pipeline/opportunities/${deal.id}/stage`, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                        body: JSON.stringify({ stage: 'lost' }),
                    }).then(() => {
                        deal.card?.remove();
                        CRM_Toast.show('❌ Marked as Lost', 'error');
                    });
                }
                break;
            case 'copy':
                navigator.clipboard.writeText(deal.num || deal.id);
                CRM_Toast.show('📋 Deal number copied!', 'success', 2000);
                break;
            case 'assign':
                CRM_Toast.show('👤 Reassign — coming soon in v7.8', 'info');
                break;
            case 'move':
                CRM_Toast.show('➡️ Drag card to move stage', 'info');
                break;
            case 'delete':
                if (confirm(`Delete "${deal.title}"? This cannot be undone.`)) {
                    fetch(`/pipeline/opportunities/${deal.id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
                    }).then(() => {
                        deal.card?.remove();
                        CRM_Toast.show('🗑️ Deal deleted', 'error');
                    });
                }
                break;
        }
    },
};

/* ════════════════════════════════════════════════════════════
   10. KANBAN BOARD DRAG-SCROLL
   ════════════════════════════════════════════════════════════ */
window.initBoardDragScroll = function() {
    const board = document.getElementById('kanban-scroll-x');
    if (!board) return;
    let isDown = false, startX, scrollLeft;
    board.addEventListener('mousedown', e => {
        if (e.target.closest('.kanban-card')) return;
        isDown = true;
        startX = e.pageX - board.offsetLeft;
        scrollLeft = board.scrollLeft;
        board.style.cursor = 'grabbing';
    });
    const stopDrag = () => { isDown = false; board.style.cursor = 'grab'; };
    document.addEventListener('mouseup', stopDrag);
    board.addEventListener('mouseleave', stopDrag);
    board.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        board.scrollLeft = scrollLeft - (e.pageX - board.offsetLeft - startX) * 1.5;
    });
};

/* ════════════════════════════════════════════════════════════
   11. DASHBOARD WIDGET CUSTOMIZER
   Usage: CRM_Widget.open() from topbar button
   Saves to /widgets/save — per-user via WidgetPreference model
   ════════════════════════════════════════════════════════════ */
window.CRM_Widget = {
    _drawer: null,
    widgets: [],
    _saveTimer: null,

    _defaults: [
        // GM Widgets (with widget- prefix)
        { id: 'widget-kpi-row',        label: '📊 KPI Cards',         visible: true,  order: 1 },
        { id: 'widget-quick-shortcuts',label: '⚡ Quick Shortcuts',    visible: true,  order: 2 },
        { id: 'widget-exec-summary',   label: '🤖 Executive Summary',  visible: true,  order: 3 },
        { id: 'widget-fleet-league',   label: '🏆 Fleet League',       visible: true,  order: 4 },
        { id: 'widget-revenue-chart',  label: '📈 Revenue Chart',      visible: true,  order: 5 },
        { id: 'widget-sales-ranking',  label: '🥇 Sales Ranking',      visible: true,  order: 6 },
        { id: 'widget-recent-books',   label: '🚌 Recent Bookings',    visible: true,  order: 7 },
        { id: 'widget-approval-q',     label: '✅ Approval Queue',     visible: true,  order: 8 },
        { id: 'widget-charts-section', label: '📉 Analytics Charts',   visible: true,  order: 9 },

        // Sales Widgets
        { id: 'w-revenue-today',   label: '💰 My Revenue Today',     visible: true,  order: 10 },
        { id: 'w-revenue-week',    label: '💵 My Revenue Week',      visible: true,  order: 11 },
        { id: 'w-revenue-month',   label: '📅 My Revenue Month',     visible: true,  order: 12 },
        { id: 'w-revenue-year',    label: '🏆 My Revenue Year',      visible: true,  order: 13 },
        { id: 'w-active-bookings', label: '🚌 Active Bookings',      visible: true,  order: 14 },
        { id: 'w-my-clients',      label: '👥 My Clients',           visible: true,  order: 15 },
        { id: 'w-new-booking',     label: '➕ New Booking Button',   visible: true,  order: 16 },
        { id: 'w-funnel-chart',    label: '📊 Sales Pipeline Chart', visible: true,  order: 17 },
        { id: 'w-revenue-chart',   label: '📈 Revenue Trend Chart',  visible: true,  order: 18 },
        { id: 'w-recent-bookings', label: '📋 Recent Bookings Table',visible: true,  order: 19 },

        // Manager Widgets
        { id: 'w-pipeline-tim',        label: '👥 Pipeline Value',     visible: true,  order: 20 },
        { id: 'w-won-alltime',         label: '🏆 Won Deals Count',    visible: true,  order: 21 },
        { id: 'w-approval-l1',         label: '✅ Pending Approvals',  visible: true,  order: 22 },
        { id: 'w-anggota-tim',         label: '📋 Team Members list',  visible: true,  order: 23 },
        { id: 'w-pipeline-breakdown',  label: '📊 Team Pipeline Chart',visible: true,  order: 24 },
        { id: 'w-approval-queue',      label: '📋 Approval Request Queue', visible: true,  order: 25 },
        { id: 'w-recent-activities',   label: '📅 Team Recent Activities', visible: true,  order: 26 },
        { id: 'w-kpi-achievement',     label: '🎯 KPI Achievement Chart',  visible: true,  order: 27 },

        // Finance Widgets
        { id: 'w-today-revenue',   label: '💰 Today\'s Paid Revenue', visible: true,  order: 28 },
        { id: 'w-month-revenue',   label: '💵 Month\'s Paid Revenue', visible: true,  order: 29 },
        { id: 'w-pending-invoice', label: '📋 Pending Unpaid Invoices', visible: true,  order: 30 },
        { id: 'w-outstanding',     label: '⚠️ Outstanding Invoices Value', visible: true,  order: 31 },
        { id: 'w-finance-summary', label: '📈 Month Revenue Trend Chart',  visible: true,  order: 32 },
        { id: 'w-overdue',         label: '🚨 Overdue Invoices List',  visible: true,  order: 33 },

        // Operational Widgets
        { id: 'w-available-fleet', label: '🚗 Available Fleet Vehicles', visible: true,  order: 34 },
        { id: 'w-on-trip',         label: '🛣️ On Trip Fleet Vehicles', visible: true,  order: 35 },
        { id: 'w-maintenance',     label: '🔧 Maintenance Fleet Vehicles', visible: true,  order: 36 },
        { id: 'w-active-trips',    label: '📋 Active Dispatch Bookings', visible: true,  order: 37 }
    ],

    _t(key) {
        const id = document.documentElement.lang === 'en' ? {
            customize: 'Customize Dashboard',
            helper: 'Show/hide widgets',
            reset: 'Reset',
            save: 'Save Layout',
            saved: 'Layout saved in realtime',
            local: 'Saved locally',
            resetDone: 'Reset to default layout',
        } : {
            customize: 'Kustomisasi Dashboard',
            helper: 'Tampilkan/sembunyikan widget',
            reset: 'Reset',
            save: 'Simpan Layout',
            saved: 'Layout tersimpan realtime',
            local: 'Tersimpan lokal',
            resetDone: 'Layout dikembalikan ke default',
        };

        return id[key] || key;
    },

    _label(widget) {
        const labels = document.documentElement.lang === 'en' ? {
            'widget-kpi-row': 'KPI Cards',
            'widget-quick-shortcuts': 'Quick Shortcuts',
            'widget-exec-summary': 'Executive Summary',
            'widget-fleet-league': 'Fleet League',
            'widget-revenue-chart': 'Revenue Chart',
            'widget-sales-ranking': 'Sales Performance',
            'widget-recent-books': 'Recent Bookings',
            'widget-approval-q': 'Approval Queue',
            'widget-charts-section': 'Analytics Charts',

            'w-revenue-today': 'My Revenue Today',
            'w-revenue-week': 'My Revenue Week',
            'w-revenue-month': 'My Revenue Month',
            'w-revenue-year': 'My Revenue Year',
            'w-active-bookings': 'Active Bookings',
            'w-my-clients': 'My Clients',
            'w-new-booking': 'New Booking Button',
            'w-funnel-chart': 'Sales Pipeline',
            'w-revenue-chart': 'Revenue Trend',
            'w-recent-bookings': 'Recent Bookings',

            'w-pipeline-tim': 'Team Pipeline Value',
            'w-won-alltime': 'Team Won Deals',
            'w-approval-l1': 'Pending Approvals',
            'w-anggota-tim': 'Team Members',
            'w-pipeline-breakdown': 'Team Pipeline Breakdown',
            'w-approval-queue': 'Approval Queue Table',
            'w-recent-activities': 'Recent Activities',
            'w-kpi-achievement': 'KPI Achievements',

            'w-today-revenue': 'Today\'s Revenue',
            'w-month-revenue': 'Month\'s Revenue',
            'w-pending-invoice': 'Pending Invoices',
            'w-outstanding': 'Outstanding Value',
            'w-finance-summary': 'Financial Summary',
            'w-overdue': 'Overdue Invoices',

            'w-available-fleet': 'Available Fleet',
            'w-on-trip': 'On Trip Vehicles',
            'w-maintenance': 'Under Maintenance',
            'w-active-trips': 'Active Bookings'
        } : {
            'widget-kpi-row': 'Kartu KPI Utama',
            'widget-quick-shortcuts': 'Shortcut Cepat',
            'widget-exec-summary': 'Executive Summary',
            'widget-fleet-league': 'Klasemen Fleet',
            'widget-revenue-chart': 'Grafik Pendapatan',
            'widget-sales-ranking': 'Sales Performance',
            'widget-recent-books': 'Booking Terbaru',
            'widget-approval-q': 'Antrean Persetujuan',
            'widget-charts-section': 'Grafik Analitik',

            'w-revenue-today': 'Pendapatan Hari Ini',
            'w-revenue-week': 'Pendapatan Minggu Ini',
            'w-revenue-month': 'Pendapatan Bulan Ini',
            'w-revenue-year': 'Pendapatan Tahun Ini',
            'w-active-bookings': 'Booking Aktif',
            'w-my-clients': 'Klien Saya',
            'w-new-booking': 'Tombol Booking Baru',
            'w-funnel-chart': 'Sales Pipeline',
            'w-revenue-chart': 'Tren Pendapatan',
            'w-recent-bookings': 'Tabel Booking Terbaru',

            'w-pipeline-tim': 'Nilai Pipeline Tim',
            'w-won-alltime': 'Deal Tim Berhasil',
            'w-approval-l1': 'Persetujuan Pending',
            'w-anggota-tim': 'Daftar Anggota Tim',
            'w-pipeline-breakdown': 'Breakdown Pipeline Tim',
            'w-approval-queue': 'Antrean Persetujuan',
            'w-recent-activities': 'Aktivitas Terbaru',
            'w-kpi-achievement': 'Pencapaian KPI',

            'w-today-revenue': 'Pendapatan Hari Ini',
            'w-month-revenue': 'Pendapatan Bulan Ini',
            'w-pending-invoice': 'Faktur Pending',
            'w-outstanding': 'Total Piutang',
            'w-finance-summary': 'Ringkasan Keuangan',
            'w-overdue': 'Faktur Jatuh Tempo',

            'w-available-fleet': 'Armada Tersedia',
            'w-on-trip': 'Armada Sedang Jalan',
            'w-maintenance': 'Armada Perbaikan',
            'w-active-trips': 'Daftar Booking Aktif'
        };

        return labels[widget.id] || widget.label;
    },

    _findWidgetEl(id) {
        return document.querySelector(`[gs-id="widget-${id}"]`) ||
               document.querySelector(`[gs-id="${id}"]`) ||
               document.getElementById('widget-' + id) ||
               document.getElementById(id);
    },

    init() {
        const saved = localStorage.getItem('crm-widgets');
        this.widgets = saved ? this._mergeSaved(JSON.parse(saved)) : [...this._defaults];
        this._applyVisibility();
    },

    _mergeSaved(saved) {
        const savedById = new Map(saved.map(w => [w.id, w]));
        return this._defaults.map(defaultWidget => ({
            ...defaultWidget,
            visible: savedById.has(defaultWidget.id) ? savedById.get(defaultWidget.id).visible : defaultWidget.visible,
        }));
    },

    open() {
        if (!this._drawer) this._buildDrawer();
        this._renderList();
        this._drawer.classList.add('open');
    },

    close() { this._drawer?.classList.remove('open'); },

    toggle(id) {
        const w = this.widgets.find(x => x.id === id);
        if (!w) return;

        w.visible = !w.visible;
        this._renderList();
        this._applyVisibility();
        this._saveLocal();
        this._autoSave();
    },

    _applyVisibility() {
        this.widgets.forEach(w => {
            const el = this._findWidgetEl(w.id);
            if (el) el.style.display = w.visible ? '' : 'none';
        });
    },

    _saveLocal() {
        localStorage.setItem('crm-widgets', JSON.stringify(this.widgets));
    },

    _autoSave() {
        clearTimeout(this._saveTimer);
        this._saveTimer = setTimeout(() => this.save({ quiet: true }), 350);
    },

    save(options = {}) {
        this._saveLocal();
        const csrf = document.querySelector('meta[name=csrf-token]')?.content;
        return fetch('/api/widgets/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ widgets: this.widgets }),
        }).then(() => {
            if (!options.quiet) CRM_Toast.show(this._t('saved'), 'success', 1800);
        }).catch(() => {
            if (!options.quiet) CRM_Toast.show(this._t('local'), 'success', 1800);
        });
    },

    reset() {
        this.widgets = [...this._defaults];
        localStorage.removeItem('crm-widgets');
        this._applyVisibility();
        this._renderList();
        fetch('/api/widgets/reset', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content,
            },
        }).catch(() => {});
        CRM_Toast.show(this._t('resetDone'), 'info', 2000);
    },

    _renderList() {
        const list = document.getElementById('widget-list');
        if (!list) return;
        const availableWidgets = this.widgets.filter(w => this._findWidgetEl(w.id) !== null);
        list.innerHTML = availableWidgets.map(w => `
            <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--cc-border);">
                <span style="color:var(--cc-text-muted);font-size:16px;cursor:default;">⠿</span>
                <span style="flex:1;font-size:13px;font-weight:500;color:var(--cc-text);">${this._label(w)}</span>
                <button type="button" onclick="event.stopPropagation(); CRM_Widget.toggle('${w.id}')" aria-pressed="${w.visible}" style="
                    width:36px;height:20px;border-radius:10px;position:relative;cursor:pointer;transition:background 0.2s;
                    background:${w.visible ? 'var(--cc-accent)' : 'rgba(16,40,72,0.14)'};flex-shrink:0;border:0;padding:0;">
                    <div style="position:absolute;top:2px;${w.visible ? 'right:2px' : 'left:2px'};width:16px;height:16px;border-radius:50%;background:#fff;transition:all 0.2s;"></div>
                </button>
            </div>`).join('');
    },

    _buildDrawer() {
        this._drawer = document.createElement('div');
        this._drawer.id = 'widget-drawer';
        this._drawer.style.cssText = `
            position:fixed;top:0;right:0;height:100vh;width:300px;z-index:9980;
            background:var(--cc-card);border-left:1px solid var(--cc-border-h);
            backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);
            transform:translateX(100%);transition:transform 0.25s cubic-bezier(0.16,1,0.3,1);
            display:flex;flex-direction:column;`;
        this._drawer.innerHTML = `
            <div style="padding:20px;border-bottom:1px solid var(--cc-border);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:15px;font-weight:700;color:var(--cc-text);">${this._t('customize')}</div>
                    <div style="font-size:11px;color:var(--cc-text-muted);margin-top:2px;">${this._t('helper')}</div>
                </div>
                <button onclick="CRM_Widget.close()" style="background:none;border:none;cursor:pointer;font-size:18px;color:var(--cc-text-muted);">✕</button>
            </div>
            <div id="widget-list" style="flex:1;overflow-y:auto;padding:0 20px;"></div>
            <div style="padding:16px 20px;border-top:1px solid var(--cc-border);display:flex;gap:8px;">
                <button onclick="CRM_Widget.reset()" style="flex:1;padding:9px;border-radius:8px;border:1px solid var(--cc-border);background:none;cursor:pointer;font-size:13px;font-weight:600;color:var(--cc-text-muted);">${this._t('reset')}</button>
                <button onclick="CRM_Widget.save()" style="flex:2;padding:9px;border-radius:8px;border:none;background:var(--cc-accent);cursor:pointer;font-size:13px;font-weight:700;color:#fff;">${this._t('save')}</button>
            </div>`;
        const s = document.createElement('style');
        s.textContent = `#widget-drawer.open{transform:translateX(0)!important;}`;
        document.head.appendChild(s);
        document.body.appendChild(this._drawer);
        document.addEventListener('click', e => {
            if (this._drawer.classList.contains('open') &&
                !this._drawer.contains(e.target) &&
                !e.target.closest('[data-widget-toggle]')) this.close();
        });
    },
};

/* ════════════════════════════════════════════════════════════
   12. RESIZABLE TABLE COLUMNS
   ════════════════════════════════════════════════════════════ */
window.CRM_TableResize = {
    init() {
        if (window.innerWidth <= 768) return; // Disable resizing on mobile

        const tables = document.querySelectorAll('table[data-resizable], table.resizable-table');
        tables.forEach((table, tableIndex) => {
            const tableId = table.getAttribute('data-table-id') || `table-${window.location.pathname}-${tableIndex}`;
            const cols = table.querySelectorAll('thead th');
            const savedWidths = JSON.parse(localStorage.getItem(`table-widths-${tableId}`) || '{}');

            // Apply 'table-layout: fixed' to ensure strict column widths are respected
            table.style.tableLayout = 'fixed';

            cols.forEach((col, colIndex) => {
                // Apply saved width if it exists
                if (savedWidths[colIndex]) {
                    col.style.width = savedWidths[colIndex];
                }

                // Add resize handle element to all columns except the last one
                if (colIndex < cols.length - 1) {
                    // Ensure the header cell is relative positioned
                    if (window.getComputedStyle(col).position === 'static') {
                        col.style.position = 'relative';
                    }

                    const handle = document.createElement('div');
                    handle.className = 'resize-handle';
                    handle.style.cssText = `
                        position: absolute;
                        top: 0;
                        right: 0;
                        bottom: 0;
                        width: 6px;
                        cursor: col-resize;
                        user-select: none;
                        z-index: 10;
                        transition: background-color 0.15s;
                    `;
                    
                    // Hover states
                    handle.addEventListener('mouseenter', () => handle.style.backgroundColor = 'rgba(59, 130, 246, 0.4)');
                    handle.addEventListener('mouseleave', () => {
                        if (!handle.classList.contains('resizing')) {
                            handle.style.backgroundColor = 'transparent';
                        }
                    });

                    col.appendChild(handle);

                    let startX, startWidth;

                    handle.addEventListener('mousedown', e => {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        startX = e.clientX;
                        startWidth = col.offsetWidth;
                        handle.classList.add('resizing');
                        handle.style.backgroundColor = 'rgba(59, 130, 246, 0.7)';

                        const onMouseMove = ev => {
                            const newWidth = Math.max(50, startWidth + (ev.clientX - startX));
                            col.style.width = `${newWidth}px`;
                        };

                        const onMouseUp = () => {
                            handle.classList.remove('resizing');
                            handle.style.backgroundColor = 'transparent';
                            document.removeEventListener('mousemove', onMouseMove);
                            document.removeEventListener('mouseup', onMouseUp);
                            
                            // Save all current column widths to localStorage
                            const currentWidths = {};
                            cols.forEach((th, idx) => {
                                if (th.style.width) {
                                    currentWidths[idx] = th.style.width;
                                }
                            });
                            localStorage.setItem(`table-widths-${tableId}`, JSON.stringify(currentWidths));
                        };

                        document.addEventListener('mousemove', onMouseMove);
                        document.addEventListener('mouseup', onMouseUp);
                    });
                }
            });
        });
    }
};

/* ════════════════════════════════════════════════════════════
   INIT — runs after Alpine stores are registered
   ════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', () => {
    CRM_Keys.init();
    Alpine.store('notif')._updateBadge();
    CRM_Widget.init();
    CRM_TableResize.init(); // Initialize resizable columns
});

Alpine.start();
