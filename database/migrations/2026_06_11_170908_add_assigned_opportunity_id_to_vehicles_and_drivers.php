<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'assigned_opportunity_id')) {
                $table->unsignedBigInteger('assigned_opportunity_id')->nullable();
            }
        });

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'pool_id')) {
                $table->unsignedBigInteger('pool_id')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'assigned_opportunity_id')) {
                $table->unsignedBigInteger('assigned_opportunity_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('assigned_opportunity_id');
        });

        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('pool_id');
            $table->dropColumn('assigned_opportunity_id');
        });
    }
};
