# 🔒 UI/UX LOCK — Golden Bird CRM (Dashboard Depan / Command Center)

> **STATUS: TERKUNCI. JANGAN DIUBAH.**
> Dokumen ini adalah kontrak desain yang mengikat. Halaman **Dashboard Depan (GM / Command Center)** sudah final. AI atau developer manapun yang mengerjakan repo ini **DILARANG mengubah tampilan, warna, layout, tipografi, spacing, atau struktur visual** halaman ini tanpa instruksi eksplisit & tertulis dari pemilik (adithya).

---

## ⛔ ATURAN UNTUK SETIAP AI / DEVELOPER

**Baca ini sebelum menyentuh kode apa pun.**

1. **DILARANG** mengubah file UI di daftar "File Terkunci" di bawah untuk alasan estetika, "perbaikan", "modernisasi", atau "konsistensi" — kecuali diminta langsung oleh pemilik.
2. **BOLEH** mengubah **data/logika di belakang layar** (controller, model, query, route, service) **selama output visual identik**. Contoh: mengganti angka hardcoded jadi data dinamis dari DB **boleh**, asalkan posisi, warna, ukuran, dan label tetap sama persis.
3. Jika sebuah requirement baru **memaksa** perubahan visual, **JANGAN langsung ubah**. Berhenti, tulis usulan, minta persetujuan pemilik dulu.
4. Setiap perubahan ke file terkunci **wajib** dicatat di bagian "Change Log" paling bawah, dengan tanggal + alasan + siapa yang menyetujui.
5. Token warna, font, dan komponen di bawah adalah **sumber kebenaran tunggal**. Jangan memperkenalkan warna baru, font baru, atau library UI baru.

---

## 📌 Baseline Commit (titik kunci)

- **Repo:** `golden-bird-crm`
- **Commit terkunci:** `f23867b` — _"fix: pipeline rupiah update saat drag — server-side summary"_ (2026-06-07)
- Tampilan dashboard depan pada commit ini = **versi resmi**. Bandingkan ke sini jika ragu.

---

## 📁 File Terkunci (Dashboard Depan)

| File | Peran | Boleh diubah? |
|------|-------|----------------|
| `resources/views/dashboard/gm.blade.php` | Struktur & layout halaman Command Center | ❌ Visual terkunci. Hanya boleh ganti sumber data (hardcoded → dinamis) tanpa ubah tampilan. |
| `resources/views/dashboard/charts.blade.php` | Bagian charts di bawah dashboard | ❌ Visual terkunci. |
| `resources/views/layouts/app.blade.php` | Shell aplikasi (wrapper) | ❌ Struktur terkunci. |
| `resources/views/components/sidebar.blade.php` | Sidebar navigasi | ❌ Terkunci. |
| `resources/views/components/topbar.blade.php` | Topbar | ❌ Terkunci. |
| `resources/css/app.css` (token & komponen) | Design tokens + class komponen | ❌ Token & class terkunci. |

---

## 🎨 DESIGN TOKENS — TERKUNCI (dari `app.css`)

Aplikasi punya **Light (default)** & **Dark** mode. Kedua set token di bawah **tidak boleh diubah nilainya**.

### Accent (sama di kedua mode)
| Token | Nilai |
|-------|-------|
| `--cc-accent` | `#1468a8` |
| `--cc-accent-dim` | `rgba(20,104,168,0.12)` |
| `--theme-speed` | `200ms` |

### Light Mode (default)
| Token | Nilai | | Token | Nilai |
|-------|-------|---|-------|-------|
| `--cc-bg` | `#f4f7fb` | | `--cc-text` | `#101828` |
| `--cc-surface` | `#ffffff` | | `--cc-text-muted` | `#667085` |
| `--cc-card` | `#ffffff` | | `--cc-text-faint` | `#98a2b3` |
| `--cc-card-hover` | `#f8fafc` | | `--cc-border` | `rgba(16,40,72,0.10)` |
| `--cc-sidebar` | `#082b5f` | | `--cc-border-h` | `rgba(20,104,168,0.24)` |
| `--cc-topbar` | `#ffffff` | | `--cc-row-hover` | `rgba(20,104,168,0.06)` |

### Dark Mode
| Token | Nilai | | Token | Nilai |
|-------|-------|---|-------|-------|
| `--cc-bg` | `#0b1120` | | `--cc-text` | `#e2e8f0` |
| `--cc-surface` | `#111827` | | `--cc-text-muted` | `#94a3b8` |
| `--cc-card` | `#1a2332` | | `--cc-text-faint` | `#64748b` |
| `--cc-card-hover` | `#1e2a3d` | | `--cc-border` | `rgba(255,255,255,0.07)` |
| `--cc-sidebar` | `#06172e` | | `--cc-border-h` | `rgba(99,179,237,0.28)` |
| `--cc-topbar` | `#111827` | | `--cc-row-hover` | `rgba(255,255,255,0.04)` |

### Tipografi
- Font: **`Inter`, system-ui, sans-serif** (terkunci). Tidak ada font lain.
- Skala judul KPI: `text-lg font-black`; label KPI: `text-[10px] font-semibold uppercase tracking-wide`.
- Heading utama: `text-xl font-black tracking-tight`.

### Warna aksen KPI cards (per-card, terkunci)
| Card | Warna ikon | Background ikon |
|------|-----------|------------------|
| Revenue (cyan/biru) | `#1468a8` | `rgba(20,104,168,0.10)` |
| Bookings (blue) | `#60a5fa` | `rgba(59,130,246,0.1)` |
| Fleet (emerald) | `#34d399` | `rgba(16,185,129,0.1)` |
| Clients (purple) | `#a78bfa` | `rgba(139,92,246,0.1)` |
| Outstanding (gold) | `#fbbf24` | `rgba(245,158,11,0.1)` |
| Approval (red) | `#f87171` | `rgba(239,68,68,0.1)` |

### Sinyal / status
- `.signal-up` = emerald-500, `.signal-down` = red-500, `.signal-warn` = amber-500.
- `.pulse-dot` = `#10b981` (hijau, animasi pulse 2s).

---

## 🧱 STRUKTUR LAYOUT DASHBOARD DEPAN — TERKUNCI

Urutan blok dari atas ke bawah (jangan diubah urutannya, jangan ditambah/dikurangi tanpa izin):

1. **Command Center Header** — judul `Bluebird CRM Command Center`, sub-teks, deret badge (Live Demo, June 2026, Director HQ, API Ready, Render Deploy), tombol kanan (Approve Queue, Reports).
2. **KPI Cards Row** (`#widget-kpi-row`) — grid 6 kartu: Revenue, Bookings, Fleet, Clients, Outstanding, Approval. Grid: `grid-cols-2 md:grid-cols-3 lg:grid-cols-6`.
3. **Quick Shortcuts** (`#widget-quick-shortcuts`) — grid ikon menu (`lg:grid-cols-8`). 16 shortcut.
4. **Main Grid** (`lg:grid-cols-3`): **Executive Summary** (2/3, `#widget-exec-summary`) + **Fleet League** (1/3, `#widget-fleet-league`).
5. **Bottom Grid** (`lg:grid-cols-3`): **Revenue Chart** (2/3, `#widget-revenue-chart`) + **Sales Ranking** (1/3, `#widget-sales-ranking`).
6. **Bottom Row** (`lg:grid-cols-2`): **Recent Bookings** (`#widget-recent-books`) + **Approval Queue** (`#widget-approval-q`).
7. **Charts Section** (`#widget-charts-section`) — `@include('dashboard.charts')`.

> ID widget (`#widget-*`) adalah **anchor terkunci**. Jangan rename. Jika kode lain bergantung padanya, biarkan.

### Komponen class terkunci (dari `app.css`)
`.cc-card`, `.kpi-card`, `.kpi-cyan/blue/emerald/purple/gold/red`, `.btn-primary`, `.btn-secondary`, `.badge-demo`, `.badge-live`, `.status-badge`, `.signal-up/down/warn`, `.pulse-dot`, `.nav-item`, `.sidebar`. **Definisi class ini tidak boleh diubah.**

---

## ✅ Yang BOLEH dikerjakan tanpa melanggar lock

- Mengganti angka **hardcoded** di `gm.blade.php` (mis. `Rp 2,84 M`, `128`, leaderboard nama `Andi Pratama`) menjadi **data dinamis** dari controller — **selama format, posisi, warna, dan ukuran identik**.
- Memperbaiki bug logika di controller/model/service.
- Menambah data ke variabel Blade yang sudah ada.
- Menerjemahkan teks via file `lang/` (key `ui.*`) — selama panjang teks tidak merusak layout.

## ❌ Yang DILARANG

- Mengubah warna token, font, radius, shadow, spacing kartu.
- Menambah/menghapus/menggeser blok widget.
- Mengganti library chart, menambah CSS framework, atau menulis ulang markup "biar lebih rapi".
- Mengubah sidebar/topbar/layout shell.
- "Redesign", "refresh", "modernize" tanpa izin tertulis.

---

## 📝 Change Log (wajib diisi setiap ada perubahan ke file terkunci)

| Tanggal | File | Perubahan | Alasan | Disetujui oleh |
|---------|------|-----------|--------|----------------|
| 2026-06-09 | — | Dokumen lock dibuat. Baseline = commit `f23867b`. | Mengunci UI/UX dashboard depan. | adithya |

---

_Dokumen ini harus disertakan/di-link di `CLAUDE.md` repo agar setiap AI membacanya sebelum bekerja._
