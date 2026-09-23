<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enable RLS only for PostgreSQL/Supabase deployments.
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
            DB::statement("ALTER TABLE {$table} ENABLE ROW LEVEL SECURITY");
        }
    }

    /**
     * Disable RLS when rolling back this migration.
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
            DB::statement("ALTER TABLE {$table} DISABLE ROW LEVEL SECURITY");
        }
    }
};
