<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration ini di luar transaction agar ALTER TABLE tidak
     * membatalkan seluruh batch jika ada tabel yang belum ada.
     */
    public $withinTransaction = false;

    /**
     * Enable RLS hanya untuk PostgreSQL/Supabase, dan hanya pada tabel yang sudah ada.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'kelompok_tani',
            'pengguna',
            'migrations',
            'anggota',
            'artikel',
            'panduan',
            'tips',
            'komoditas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
            }
        }
    }

    /**
     * Disable RLS saat rollback, hanya pada tabel yang ada.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'kelompok_tani',
            'pengguna',
            'migrations',
            'anggota',
            'artikel',
            'panduan',
            'tips',
            'komoditas',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
            }
        }
    }
};
