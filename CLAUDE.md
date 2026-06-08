# Bluebird CRM — CLAUDE.md
> Project context untuk Claude Code & Cowork sessions. Baca ini dulu sebelum mulai.

> 🔒 **WAJIB BACA DULU: [`UI_UX_LOCK.md`](./UI_UX_LOCK.md)** — Tampilan dashboard depan (Command Center) TERKUNCI. Dilarang mengubah UI/UX (warna, layout, font, struktur) tanpa izin tertulis pemilik. Hanya logika/data di belakang layar yang boleh diubah selama output visual identik.

---

## 🚌 Project Overview
**Bluebird CRM** adalah sistem B2B Fleet Management untuk Golden Bird Group.
Mengelola: corporate clients, fleet dispatch, sales pipeline, finance & billing, KPI tracking, approval workflows.

**Sub-brands:** Golden Bird · Big Bird · Cititrans · Executive Transport

---

## ⚙️ Tech Stack
| Layer | Tech |
|-------|------|
| Backend | Laravel 12 |
| PHP | 8.4 (lokal: 8.5, Railway: 8.4) |
| Database | PostgreSQL (Railway production), SQLite (local dev) |
| Frontend | Blade + Tailwind CDN + Alpine.js + Chart.js |
| Deploy | Railway (nixpacks, `php84` + `pdo_pgsql`) |
| Auth | Laravel built-in + role-based middleware |

---

## 🔑 6 User Roles & Access
| Role | Access |
|------|--------|
| `director` | Full access semua modul |
| `gm` | Full access minus system config |
| `manager` | Sales + Approval + Fleet view |
| `sales` | Pipeline + Clients + Activities |
| `operational` | Fleet + Dispatch/Booking |
| `finance` | Finance + Invoices + Subscriptions + Vouchers |

---

## 🌿 Branches
| Branch | Purpose | Status |
|--------|---------|--------|
| `main` | Production-ready stable | Live di Railway |
| `ui-modern-preview` | Dark command center UI v7.5 | **Active development** |

> ⚠️ JANGAN ubah `main` langsung. Semua UI work di `ui-modern-preview`.

---

## 🖥️ Key Commands
```bash
# Local dev
php artisan serve
php artisan migrate --seed
php artisan view:cache          # test blade syntax
php artisan view:clear          # clear compiled views
php artisan config:cache
php artisan route:list

# Git workflow
git checkout ui-modern-preview
git add resources/views/
git commit -m "feat(ui): description"
git push origin ui-modern-preview

# Railway
railway status
railway logs --lines 100
railway up --detach -m "description"
railway run php artisan migrate --force
```

---

## 📁 Key Files
```
resources/views/
├── layouts/app.blade.php       ← MAIN DARK LAYOUT (CSS classes, sidebar, topbar)
├── auth/login.blade.php        ← Login page
├── dashboard/
│   ├── gm.blade.php            ← ✅ Command center (KPI, chart, rankings)
│   ├── director.blade.php      ← ✅ Command center
│   ├── manager.blade.php       ← ⏳ Pending redesign
│   ├── sales.blade.php         ← ⏳ Pending redesign
│   ├── operational.blade.php   ← ⏳ Pending redesign
│   └── finance.blade.php       ← ⏳ Pending redesign
app/Http/Controllers/
├── DashboardController.php     ← dashboard data per role
routes/web.php                  ← all routes (do not modify)
nixpacks.toml                   ← Railway build (PHP 8.4)
DEPLOYMENT.md                   ← deploy guide Railway + Render
```

---

## 🎨 Design System (Dark Theme)
All CSS defined in `resources/views/layouts/app.blade.php`:

```
Colors: #09090f (bg) · #00e5ff (cyan) · #3b82f6 (blue) · #f59e0b (gold)
        #10b981 (emerald) · #ef4444 (red) · #8b5cf6 (purple)

Classes:
  .cc-card          - dark card
  .kpi-card.kpi-*   - KPI metric card with color variant
  .dark-table       - dark table
  .dark-input       - dark form input
  .dark-label       - form label
  .btn-primary      - blue gradient button
  .btn-secondary    - ghost button
  .status-badge.status-* - colored status pill
  .signal-up/down/warn   - performance signals
  .badge-live/demo/role  - status badges
```

---

## 🔗 Live URLs
| Environment | URL |
|-------------|-----|
| Production | https://gbcrmbycodex-production.up.railway.app |
| Login | https://gbcrmbycodex-production.up.railway.app/login |

---

## 👤 Demo Accounts
| Role | Email | Password |
|------|-------|----------|
| Director | director@goldenbird.co.id | password123 |
| GM | gm@goldenbird.co.id | password123 |
| Manager | manager@goldenbird.co.id | password123 |
| Sales | sales@goldenbird.co.id | password123 |
| Operational | operational@goldenbird.co.id | password123 |
| Finance | finance@goldenbird.co.id | password123 |

---

## ⛔ RULES — BACA SEBELUM MULAI

```
✅ BOLEH:
  - Ubah resources/views/ (Blade, CSS, layout)
  - Tambah @push('styles') dan @push('scripts') inline
  - Tambah CSS class baru di layouts/app.blade.php
  - Tambah file komponen baru di resources/views/components/

❌ DILARANG:
  - Ubah database/migrations/
  - Ubah routes/web.php (kecuali menambah route, bukan mengubah existing)
  - Ubah app/Models/ relationships
  - Ubah app/Http/Middleware/
  - Ubah config/ files (kecuali logging)
  - Merge ke main tanpa review user
  - Buat branch baru selain ui-modern-preview
```

---

## 🐛 Common Issues
| Error | Fix |
|-------|-----|
| Blade syntax error | `php artisan view:clear && php artisan view:cache` |
| Tailwind class missing | Gunakan full class string, bukan dynamic concat |
| Chart not rendering | Pastikan `canvas id` unik, wrap Chart init dalam `DOMContentLoaded` |
| Mobile layout broken | Tambah `overflow-x-auto` pada table wrapper |
| PostgreSQL error | Check `DB_*` env vars di Railway dashboard |
