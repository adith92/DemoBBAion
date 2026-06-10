<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->timestamp('stage_changed_at')->nullable()->after('stage');
        });

        // Backfill: set stage_changed_at to updated_at for existing records
        DB::table('opportunities')->update([
            'stage_changed_at' => DB::raw('updated_at')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('stage_changed_at');
        });
    }
};
