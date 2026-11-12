<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aktivitas_tanam', function (Blueprint $table) {
            $table->string('id_aktivitas', 30)->primary();
            $table->string('id_lahan', 30)->index();
            $table->string('id_pengguna', 20)->index();
            $table->string('id_kelompok', 20)->index();
            $table->string('kategori', 50)->index(); // pemupukan, penyiraman, pengairan, penyemprotan, penyiangan, pemangkasan
            $table->string('judul', 150);
            $table->string('kondisi_tanaman', 50)->default('sehat')->index(); // sehat, perluPerhatian, terserangHama
            $table->date('tanggal')->index();
            $table->string('waktu', 20)->nullable();
            $table->integer('hari_setelah_tanam')->nullable();
            $table->text('catatan')->nullable();
            $table->string('foto_aktivitas', 255)->nullable();
            $table->decimal('biaya', 14, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key Constraints
            $table->foreign('id_lahan')
                ->references('id_lahan')
                ->on('lahan')
                ->onDelete('cascade');

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');

            $table->foreign('id_kelompok')
                ->references('id_kelompok')
                ->on('kelompok_tani')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aktivitas_tanam');
    }
};

