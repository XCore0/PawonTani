<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE panduan ALTER COLUMN target DROP NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("UPDATE panduan SET target = '' WHERE target IS NULL");
            DB::statement('ALTER TABLE panduan ALTER COLUMN target SET NOT NULL');
        }
    }
};
