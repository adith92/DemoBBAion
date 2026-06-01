# 🐦 BlueERP - B2B Fleet Management System

Selamat datang di **BlueERP**, sebuah sistem ERP (Enterprise Resource Planning) yang dirancang khusus untuk manajemen armada B2B. Aplikasi ini dibuat untuk mendemonstrasikan kapabilitas pengembangan aplikasi web yang kompleks menggunakan Laravel.

## 🤖 Dibuat Oleh Siapa?

Aplikasi ini **100% ditulis dan dideploy oleh AI** dari model **Aion** (model: `qwen3.7-max` & team). Seluruh proses, mulai dari analisis blueprint, penulisan kode backend & frontend, pembuatan database, hingga konfigurasi deployment, dieksekusi secara otomatis oleh AI.

-   **AI Model:** Aion (`qwen3.7-max`, `gpt-5.3-codex`, `claude-sonnet-4.5-free`)
-   **Orchestrator:** AionRS
-   **Development Environment:** AionUI

## 🚀 Status Project: 100% Selesai & Terdeploy

-   [x] ⚙️ **Backend:** Selesai (13 Migrations, 11 Models, 9 Controllers, RBAC Middleware, 53 API Routes)
-   [x] 🎨 **Frontend:** Selesai (Views untuk 4 role dashboard, 6 modul CRUD, styling dengan Tailwind CSS)
-   [x] 🗃️ **Database:** Selesai (Skema SQLite + 270+ mock data seeded)
-   [x] ✅ **Testing:** Selesai (Self-QA lolos, bug API diperbaiki)
-   [x] ☁️ **Deployment:** Siap untuk Railway!

## ✨ Fitur Unggulan

-   **Dashboard Multi-Role 👑:** Tampilan yang berbeda untuk General Manager (GM), Sales, Operational, dan Finance.
-   **Manajemen Client (CRM) 👥:** Mengelola data client B2B, dari prospek hingga menjadi pelanggan aktif.
-   **Sistem Booking 📅:** Membuat dan mengelola jadwal booking kendaraan untuk client.
-   **Manajemen Armada 🚗:** Database lengkap kendaraan (Bigbird, Goldenbird, Cititrans, Executive).
-   **Modul Keuangan 💰:** Mengelola Invoice, Payment, dan Purchase Order (PO).
-   **Manajemen Pool & Driver 🅿️:** Mengatur alokasi kendaraan dan supir di setiap pool.
-   **Log Maintenance 🔧:** Mencatat riwayat servis dan perbaikan untuk setiap kendaraan.
-   **Visualisasi Data 📊:** Grafik interaktif (revenue, status armada) menggunakan Chart.js.
-   **Role-Based Access Control (RBAC) 🛡️:** Sistem hak akses ketat, memastikan setiap role hanya bisa melihat data yang relevan.
-   **Format Rupiah Otomatis 💸:** Input dan tampilan angka menggunakan format `Rp X.XXX.XXX`.

---

## ☁️ Petunjuk Deployment ke Railway

Proses ini sangat mudah karena semua file konfigurasi (`railway.toml`, `nixpacks.toml`) sudah disiapkan.

1.  **Fork/Clone Repo Ini 🍴**
    Pastikan Anda memiliki repo ini di akun GitHub Anda.

2.  **Login ke Railway & Buat Project Baru 🚂**
    -   Buka dashboard Railway (https://railway.app/).
    -   Klik **New Project** -> **Deploy from GitHub repo**.
    -   Pilih repo `DemoBBAion` (atau nama repo hasil fork Anda).

3.  **Konfigurasi Variabel Lingkungan (PENTING!) 🤫**
    -   Setelah project dibuat, jangan langsung di-deploy. Buka menu **Variables**.
    -   Tambahkan variabel-variabel berikut:
        -   `APP_KEY`: Generate kunci baru dengan menjalankan `php artisan key:generate --show` di lokal, atau pakai generator online (contoh: `base64:xxxxxxxx...`).
        -   `APP_ENV`: `production`
        -   `APP_DEBUG`: `false`
        -   `APP_URL`: Isi dengan URL publik yang diberikan oleh Railway (contoh: `https://webtemplate-production-xxxx.up.railway.app`).
        -   `DB_CONNECTION`: `sqlite`
        -   `DB_DATABASE`: `/data/database.sqlite`

4.  **Buat Volume untuk Database (PENTING!) 💾**
    -   Buka menu **Settings** pada service Anda.
    -   Scroll ke bawah hingga menemukan bagian **Volumes**.
    -   Klik **Create Volume**.
    -   Isi *Mount Path* dengan: `/data`. Ini memastikan file `database.sqlite` Anda tidak hilang setiap kali server restart.

5.  **Deploy! 🎉**
    -   Kembali ke menu **Deployments** dan klik tombol **Deploy** (atau Railway akan otomatis re-deploy setelah Anda mengubah variabel).
    -   Tunggu sekitar 5-10 menit. Proses build akan otomatis menjalankan `composer install`, `migrate`, dan `db:seed`.
    -   Setelah selesai, aplikasi Anda akan live!

---

## 🧑‍💻 Petunjuk Penggunaan Aplikasi

Setelah aplikasi live, Anda bisa langsung login dan mencoba berbagai fitur dengan akun demo yang sudah disiapkan.

**URL Aplikasi:** `[URL DARI RAILWAY ANDA]/login`

**Akun Demo:**
| Role | Email | Password |
|---|---|---|
| 👑 General Manager | `gm@bluebird.co.id` | `password123` |
| 📈 Sales 1 | `sales1@bluebird.co.id` | `password123` |
| 📊 Sales 2 | `sales2@bluebird.co.id` | `password123` |
| 🛠️ Operational | `ops@bluebird.co.id` | `password123` |
| 💰 Finance | `finance@bluebird.co.id` | `password123` |
| 🧑‍💼 Admin (GM) | `admin@bluebird.co.id`| `password123` |

Selamat mencoba! 😉
