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
        Schema::create('panen', function (Blueprint $table) {
            $table->string('id_panen', 30)->primary(); // PN-YYYYMMDD-XXXX
            $table->string('kode_panen', 50)->unique(); // PN-202611-001
            $table->string('id_lahan', 30)->index();
            $table->string('id_pengguna', 20)->index();
            $table->string('id_kelompok', 20)->index();
            $table->string('id_komoditas', 20)->nullable()->index();
            
            // Informasi Tanaman & Komoditas
            $table->string('komoditas_nama', 100); // Padi, Jagung, dll
            $table->string('varietas', 100); // Ciherang, IR-64, Hibrida, dll
            $table->string('jenis_panen', 100)->nullable(); // GKP, Tongkol Segar, dll
            
            // Waktu & Musim
            $table->date('tanggal_panen')->index();
            $table->string('waktu_panen', 20)->nullable();
            $table->string('musim_tanam', 50)->default('Musim Rendeng');
            
            // Timbangan & Nilai Finansial
            $table->decimal('berat_kg', 12, 2)->default(0);
            $table->string('kualitas_grade', 50)->default('Grade A'); // Grade A, Grade B, Grade C
            $table->decimal('kadar_air', 5, 2)->nullable(); // %
            $table->decimal('persen_butir_utuh', 5, 2)->nullable(); // %
            $table->decimal('persen_hampa_kotoran', 5, 2)->nullable(); // %
            
            // Operasional Panen
            $table->string('metode_panen', 100)->default('Mesin Combine Harvester');
            $table->decimal('biaya_panen', 14, 2)->default(0);
            $table->string('keterangan_tenaga_kerja', 150)->nullable();
            $table->decimal('harga_per_kg', 12, 2)->default(0);
            $table->text('catatan')->nullable();
            $table->string('foto_panen', 255)->nullable();
            
            // Status Verifikasi & Approval
            $table->string('status', 30)->default('menunggu')->index(); // menunggu, disetujui, ditolak
            $table->string('diverifikasi_oleh', 150)->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            
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
        Schema::dropIfExists('panen');
    }
};
