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
        Schema::table('aktivitas_tanam', function (Blueprint $table) {
            // Kolom id_siklus nullable untuk mempertahankan integritas data legacy
            $table->string('id_siklus', 30)->nullable()->after('id_lahan');

            // Foreign key referential integrity: restrict onDelete agar siklus yang memiliki aktivitas tidak terhapus sembarangan
            $table->foreign('id_siklus')
                  ->references('id_siklus')
                  ->on('siklus_tanam')
                  ->onDelete('restrict');

            // Index performa query riwayat aktivitas per siklus
            $table->index(['id_siklus', 'tanggal'], 'idx_aktivitas_siklus_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas_tanam', function (Blueprint $table) {
            $table->dropForeign(['id_siklus']);
            $table->dropIndex('idx_aktivitas_siklus_tanggal');
            $table->dropColumn('id_siklus');
        });
    }
};
