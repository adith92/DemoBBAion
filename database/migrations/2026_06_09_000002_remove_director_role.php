<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus role 'director'. Wewenangnya digabung ke GM.
 * 1) Remap semua user role 'director' menjadi 'gm'.
 * 2) Perbarui CHECK constraint (Postgres) / ENUM (MySQL) agar tidak lagi mengizinkan 'director'.
 * Aman dijalankan di SQLite (lokal, role disimpan sebagai string biasa).
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1) Remap data lama: director -> gm
        DB::table('users')->where('role', 'director')->update(['role' => 'gm']);

        // 2) Sesuaikan constraint per driver
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('gm','manager','sales','operational','finance'))");
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('gm','manager','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
        // sqlite: tidak ada constraint enum, cukup remap data di atas.
    }

    public function down(): void
    {
        // Kembalikan kemungkinan role 'director' (data tidak di-restore otomatis).
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('director','gm','manager','sales','operational','finance'))");
        } elseif ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('director','gm','manager','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
    }
};
