<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite does not support modifying column constraints directly.
            // We recreate the table to make phone and license_number nullable
            // and drop the unique index on license_number then re-add it as nullable unique.

            // 1. Create temp table with desired schema
            DB::statement('CREATE TABLE drivers_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NULL,
                license_number VARCHAR(255) NULL UNIQUE,
                status VARCHAR(255) NOT NULL DEFAULT \'available\',
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');

            // 2. Copy data
            DB::statement('INSERT INTO drivers_new (id, name, phone, license_number, status, notes, created_at, updated_at)
                SELECT id, name, phone, license_number, status, notes, created_at, updated_at FROM drivers');

            // 3. Drop old table and rename new
            DB::statement('DROP TABLE drivers');
            DB::statement('ALTER TABLE drivers_new RENAME TO drivers');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE drivers ALTER COLUMN phone DROP NOT NULL');
            DB::statement('ALTER TABLE drivers ALTER COLUMN license_number DROP NOT NULL');
        } else {
            // MySQL / MariaDB
            DB::statement('ALTER TABLE drivers MODIFY COLUMN phone VARCHAR(255) NULL');
            DB::statement('ALTER TABLE drivers MODIFY COLUMN license_number VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('CREATE TABLE drivers_old (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR(255) NOT NULL,
                phone VARCHAR(255) NOT NULL,
                license_number VARCHAR(255) NOT NULL UNIQUE,
                status VARCHAR(255) NOT NULL DEFAULT \'available\',
                notes TEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');

            DB::statement('INSERT INTO drivers_old (id, name, phone, license_number, status, notes, created_at, updated_at)
                SELECT id, name, COALESCE(phone, \'\'), COALESCE(license_number, \'\'), status, notes, created_at, updated_at FROM drivers');

            DB::statement('DROP TABLE drivers');
            DB::statement('ALTER TABLE drivers_old RENAME TO drivers');
        } elseif ($driver === 'pgsql') {
            DB::statement('ALTER TABLE drivers ALTER COLUMN phone SET NOT NULL');
            DB::statement('ALTER TABLE drivers ALTER COLUMN license_number SET NOT NULL');
        } else {
            DB::statement('ALTER TABLE drivers MODIFY COLUMN phone VARCHAR(255) NOT NULL');
            DB::statement('ALTER TABLE drivers MODIFY COLUMN license_number VARCHAR(255) NOT NULL');
        }
    }
};
