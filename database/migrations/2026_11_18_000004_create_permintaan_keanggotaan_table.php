<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permintaan_keanggotaan', function (Blueprint $table) {
            $table->id();
            $table->string('id_pengguna', 50);
            $table->string('id_kelompok', 50)->nullable();
            $table->text('alasan')->nullable();
            $table->string('status', 20)->default('Menunggu');
            $table->string('ditinjau_oleh', 50)->nullable();
            $table->text('catatan_admin')->nullable();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();

            $table->index(['id_pengguna', 'status']);
            $table->index(['id_kelompok', 'status']);
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->nullOnDelete();
            $table->foreign('ditinjau_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permintaan_keanggotaan');
    }
};
