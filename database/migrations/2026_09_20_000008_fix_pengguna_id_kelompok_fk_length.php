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
        // Drop FK constraint first
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropForeign(['id_kelompok']);
        });

        // Adjust column length
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('id_kelompok', 20)->nullable()->change();
        });

        // Re-add FK constraint
        Schema::table('pengguna', function (Blueprint $table) {
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropForeign(['id_kelompok']);
        });

        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('id_kelompok', 50)->nullable()->change();
        });

        Schema::table('pengguna', function (Blueprint $table) {
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->nullOnDelete();
        });
    }
};
