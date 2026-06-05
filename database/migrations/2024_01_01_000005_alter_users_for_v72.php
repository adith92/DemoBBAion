<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('manager_id')->nullable()->after('id');
            $table->string('sales_level')->nullable()->after('role')
                ->comment('junior|senior|key_account');

            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });

        // SQLite does not support MODIFY COLUMN for enum changes.
        // We use a CHECK constraint approach via DB::statement for SQLite,
        // or ALTER COLUMN for MySQL/PostgreSQL.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: enums stored as strings, no column modification needed.
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: drop old check constraint, then add new one with expanded roles
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('director','gm','manager','sales','operational','finance'))");
        } else {
            // MySQL / MariaDB
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('director','gm','manager','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['manager_id']);
            $table->dropColumn(['manager_id', 'sales_level']);
        });

        $driver = DB::getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('gm','sales','operational','finance') NOT NULL DEFAULT 'sales'");
        }
    }
};
