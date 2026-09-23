<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE panduan ALTER COLUMN tanggal DROP NOT NULL');
        } else {
            Schema::table('panduan', function (Blueprint $table) {
                $table->date('tanggal')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("UPDATE panduan SET tanggal = CURRENT_DATE WHERE tanggal IS NULL");
            DB::statement('ALTER TABLE panduan ALTER COLUMN tanggal SET NOT NULL');
        } else {
            Schema::table('panduan', function (Blueprint $table) {
                $table->date('tanggal')->nullable(false)->change();
            });
        }
    }
};
