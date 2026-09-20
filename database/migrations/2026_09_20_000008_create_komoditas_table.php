<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('komoditas', function (Blueprint $table) {
            $table->string('id_komoditas', 20)->primary();
            $table->string('nama_komoditas', 100)->unique();
            $table->string('kategori', 100); // Tanaman Pangan, Hortikultura & Sayuran, Buah-buahan, Perkebunan & Rempah
            $table->timestamps();
        });

        // Enable RLS for PostgreSQL if applicable
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE komoditas ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE komoditas DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('komoditas');
    }
};
