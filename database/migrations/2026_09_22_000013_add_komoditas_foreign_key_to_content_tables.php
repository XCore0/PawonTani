<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = ['tips', 'panduan', 'artikel'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                // Drop old plain-string komoditas column
                $t->dropColumn('komoditas');
            });

            Schema::table($table, function (Blueprint $t) use ($table) {
                // Add FK column referencing komoditas.id_komoditas
                $t->string('komoditas_id', 20)->nullable()->after('kategori');
                $t->foreign('komoditas_id')
                    ->references('id_komoditas')
                    ->on('komoditas')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = ['tips', 'panduan', 'artikel'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['komoditas_id']);
                $t->dropColumn('komoditas_id');
            });

            Schema::table($table, function (Blueprint $t) {
                $t->string('komoditas', 100)->nullable()->after('kategori');
            });
        }
    }
};
