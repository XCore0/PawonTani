<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dokumen_lahan', function (Blueprint $table) {
            $table->dropUnique(['id_lahan']);
            $table->index(['id_lahan', 'created_at'], 'dokumen_lahan_lahan_created_idx');
        });
    }

    public function down(): void
    {
        $hasDuplicates = DB::table('dokumen_lahan')
            ->select('id_lahan')
            ->groupBy('id_lahan')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            throw new \RuntimeException('Tidak dapat rollback: terdapat lebih dari satu SPPT pada satu lahan. Pindahkan/arsipkan dokumen tambahan terlebih dahulu.');
        }

        Schema::table('dokumen_lahan', function (Blueprint $table) {
            $table->dropIndex('dokumen_lahan_lahan_created_idx');
            $table->unique('id_lahan');
        });
    }
};
