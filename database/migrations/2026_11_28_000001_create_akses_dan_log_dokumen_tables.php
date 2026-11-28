<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('akses_dokumen', function (Blueprint $table) {
            $table->id();
            $table->string('id_pengguna', 50)->comment('Pemilik dokumen yang memberikan izin');
            $table->string('tipe_dokumen', 30)->comment('identitas (KTP/KK) atau sppt');
            $table->unsignedBigInteger('id_dokumen')->comment('ID baris dokumen_anggota atau dokumen_lahan');
            $table->string('id_kelompok', 50)->comment('Kelompok tani yang diberi izin akses');
            $table->string('tujuan_penggunaan', 255);
            $table->timestamp('waktu_persetujuan')->useCurrent();
            $table->timestamp('waktu_berakhir')->nullable();
            $table->string('status_akses', 20)->default('aktif')->comment('aktif, dicabut, kadaluarsa');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['id_pengguna', 'status_akses']);
            $table->index(['id_kelompok', 'status_akses']);
            $table->index(['tipe_dokumen', 'id_dokumen']);
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->cascadeOnDelete();
        });

        Schema::create('log_akses_dokumen', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_akses_dokumen')->nullable();
            $table->string('id_pengguna', 50)->comment('Pengurus atau aktor yang mengakses');
            $table->string('tipe_dokumen', 30);
            $table->unsignedBigInteger('id_dokumen');
            $table->string('jenis_aktivitas', 20)->comment('preview atau download');
            $table->timestamp('waktu_akses')->useCurrent();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['id_dokumen', 'tipe_dokumen']);
            $table->index('waktu_akses');
            $table->foreign('id_akses_dokumen')->references('id')->on('akses_dokumen')->nullOnDelete();
            $table->foreign('id_pengguna')->references('id_pengguna')->on('pengguna')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_akses_dokumen');
        Schema::dropIfExists('akses_dokumen');
    }
};
