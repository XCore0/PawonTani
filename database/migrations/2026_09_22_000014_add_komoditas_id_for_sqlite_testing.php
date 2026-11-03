<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'panduan' => ['kategori', 'judul'],
            'artikel' => ['kategori', 'judul'],
            'tips' => ['kategori', 'judul'],
        ];

        foreach ($tables as $table => $afterColumn) {
            if (!Schema::hasTable($table) || Schema::hasColumn($table, 'komoditas_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) use ($afterColumn) {
                $t->string('komoditas_id', 20)->nullable()->after($afterColumn);
            });

            Schema::table($table, function (Blueprint $t) {
                $t->foreign('komoditas_id')
                    ->references('id_komoditas')
                    ->on('komoditas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['panduan', 'artikel', 'tips'];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'komoditas_id')) {
                continue;
            }

            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['komoditas_id']);
                $t->dropColumn('komoditas_id');
            });
        }
    }
};
