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
        Schema::create('tips', function (Blueprint $table) {
            $table->id('id_tips');
            $table->string('judul');
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target', 150)->nullable();
            $table->text('ringkasan');
            $table->text('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 50)->default('Publik'); // Publik, Draft
            $table->timestamps();
        });

        // Enable RLS for PostgreSQL if applicable
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tips ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE tips DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('tips');
    }
};
