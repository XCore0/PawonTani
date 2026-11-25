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
        Schema::table('panen', function (Blueprint $table) {
            $table->string('id_siklus', 30)->nullable()->after('id_lahan');

            $table->foreign('id_siklus')
                ->references('id_siklus')
                ->on('siklus_tanam')
                ->onDelete('restrict');

            $table->index(['id_siklus', 'tanggal_panen'], 'idx_panen_siklus_tanggal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panen', function (Blueprint $table) {
            $table->dropForeign(['id_siklus']);
            $table->dropIndex('idx_panen_siklus_tanggal');
            $table->dropColumn('id_siklus');
        });
    }
};
