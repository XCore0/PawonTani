<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->string('id_penjualan', 30)->primary();
            $table->string('id_kelompok', 20)->index();
            $table->string('id_panen', 30)->index();
            $table->string('id_pengurus', 20)->nullable()->index();
            $table->date('tanggal_penjualan')->index();
            $table->string('nama_pembeli', 120)->nullable();
            $table->string('komoditas_nama', 100);
            $table->decimal('berat_kg', 12, 2);
            $table->decimal('harga_per_kg', 12, 2);
            $table->decimal('total_harga', 14, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_kelompok')
                ->references('id_kelompok')
                ->on('kelompok_tani')
                ->onDelete('restrict');

            $table->foreign('id_panen')
                ->references('id_panen')
                ->on('panen')
                ->onDelete('restrict');

            $table->foreign('id_pengurus')
                ->references('id_pengguna')
                ->on('pengguna')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};
