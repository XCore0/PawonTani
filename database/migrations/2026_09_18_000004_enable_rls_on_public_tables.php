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

        DB::statement('ALTER TABLE kelompok_tani ENABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE pengguna ENABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE migrations ENABLE ROW LEVEL SECURITY');
    }

    /**
     * Disable RLS when rolling back this migration.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE kelompok_tani DISABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE pengguna DISABLE ROW LEVEL SECURITY');
        DB::statement('ALTER TABLE migrations DISABLE ROW LEVEL SECURITY');
    }
};
