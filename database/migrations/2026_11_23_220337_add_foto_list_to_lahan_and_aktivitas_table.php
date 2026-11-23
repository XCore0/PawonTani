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
        Schema::table('lahan', function (Blueprint $table) {
            $table->json('foto_lahan_list')->nullable()->after('foto_lahan');
        });

        Schema::table('aktivitas_tanam', function (Blueprint $table) {
            $table->json('foto_aktivitas_list')->nullable()->after('foto_aktivitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            $table->dropColumn('foto_lahan_list');
        });

        Schema::table('aktivitas_tanam', function (Blueprint $table) {
            $table->dropColumn('foto_aktivitas_list');
        });
    }
};
