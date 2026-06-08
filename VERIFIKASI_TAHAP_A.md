# Verifikasi Tahap A — Perintah untuk dijalankan di terminal

> PHP tidak tersedia di lingkungan Cowork, jadi migrate/seed/test harus dijalankan di komputer/repo lo sendiri.
> Jalankan dari folder repo: `golden-bird-crm`.

## 1. Migrate fresh + seed (lokal, SQLite)

```bash
cd golden-bird-crm
php artisan migrate:fresh --seed
```

**Yang harus muncul / dicek:**
- Migrasi `2026_06_09_000001_add_kpi_key_to_products_table` dan `2026_06_09_000002_remove_director_role` jalan tanpa error.
- Seeder selesai tanpa error.

## 2. Cek hierarki user (5 manager × 3 sales + GM)

```bash
php artisan tinker --execute="
use App\Models\User;
echo 'GM: '.User::where('role','gm')->count().PHP_EOL;
echo 'Manager: '.User::where('role','manager')->count().PHP_EOL;
echo 'Sales: '.User::where('role','sales')->count().PHP_EOL;
echo 'Director (harus 0): '.User::where('role','director')->count().PHP_EOL;
foreach (User::where('role','manager')->get() as \$m) {
  echo \$m->name.' → '.User::where('manager_id',\$m->id)->where('role','sales')->count().' sales'.PHP_EOL;
}
"
```

**Ekspektasi:** GM=1, Manager=5, Sales=15, Director=0, tiap manager = 3 sales.

## 3. Cek 6 produk fix + kpi_key

```bash
php artisan tinker --execute="
use App\Models\Product;
foreach (Product::whereNotNull('kpi_key')->get() as \$p) {
  echo \$p->kpi_key.' → '.\$p->name.PHP_EOL;
}
"
```

**Ekspektasi:** tepat 6 baris — mobil_short, bis_short, evoucher, mobil_long, bis_long, supir.

## 4. Jalankan test suite

```bash
php artisan test
```

**Catatan:**
- Test terkait director sudah diperbarui ke desain baru (level 2 = GM tertinggi).
- Jika ada test yang masih merah, kirim outputnya ke gw — kemungkinan ada assertion lama yang belum ke-cover.

## 5. Cek guard Client (manual / via test)

Login sebagai GM (`gm@goldenbird.co.id` / `password123`) lalu coba akses `/clients/create` → harus **403**.
Login sebagai Sales (`sales1@goldenbird.co.id` / `password123`) → boleh create/edit client.

---

## Ringkasan perubahan Tahap A

| Area | File |
|------|------|
| Hapus director | `app/Models/User.php`, `app/Http/Controllers/DashboardController.php`, `app/Services/ApprovalService.php`, `app/Http/Controllers/ProductController.php`, `routes/web.php`, `database/factories/UserFactory.php`, sidebar/fab/login blade |
| Migrasi DB | `2026_06_09_000001_add_kpi_key_to_products_table.php`, `2026_06_09_000002_remove_director_role.php` |
| Hierarki 5×3 | `database/seeders/DatabaseSeeder.php` |
| 6 produk + kpi_key | `database/seeders/DemoMassiveSeeder.php`, `app/Models/Product.php` |
| Guard Client | `routes/web.php` (resource clients dipecah view vs write) |
| Test diperbarui | `tests/Unit/ApprovalServiceComprehensiveTest.php`, `tests/Unit/ApprovalServiceTest.php`, `tests/Feature/ApprovalTest.php`, `tests/Feature/RoleAccessTest.php`, `tests/Feature/SmokeTest.php`, `tests/Feature/LocalizationWidgetTest.php` |

> ⚠️ Migrasi `2026_06_09_000002` juga me-remap user `director` lama → `gm` dan memperketat constraint di Postgres/MySQL production. Aman untuk SQLite lokal.
