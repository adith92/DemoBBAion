<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Set the default to the hashed '123456' PIN
            $table->string('billing_pin')->nullable()->after('password');
        });

        // Update existing users to have the default PIN '123456'
        \DB::table('users')->update([
            'billing_pin' => Hash::make('123456')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('billing_pin');
        });
    }
};
