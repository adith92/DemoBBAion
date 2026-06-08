<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom kpi_key di products.
 * kpi_key memetakan produk ke salah satu dari 6 bucket KPI:
 *   mobil_short, bis_short, evoucher, mobil_long, bis_long, supir
 * Dipakai saat deal Won untuk memecah nilai per produk ke target KPI yang tepat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('kpi_key')->nullable()->after('name')
                ->comment('mobil_short|bis_short|evoucher|mobil_long|bis_long|supir');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('kpi_key');
        });
    }
};
