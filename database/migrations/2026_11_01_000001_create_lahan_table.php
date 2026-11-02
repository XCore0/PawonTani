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
        Schema::create('lahan', function (Blueprint $table) {
            $table->string('id_lahan', 30)->primary();
            $table->string('id_pengguna', 20)->index();
            $table->string('id_kelompok', 20)->index();
            $table->string('nama_lahan', 150);
            $table->decimal('luas', 10, 4); // Satuan baku: Hektare (ha)
            $table->string('provinsi_id', 10)->nullable();
            $table->string('kabupaten_id', 10)->nullable();
            $table->string('kecamatan_id', 10)->nullable();
            $table->string('desa_id', 20)->nullable();
            $table->text('alamat_lahan');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('id_komoditas', 20)->nullable()->index();
            $table->string('musim_tanam', 50)->nullable();
            $table->string('foto_lahan', 255)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status_operasional', 30)->default('persiapan');
            $table->string('status_verifikasi', 30)->default('menunggu_verifikasi')->index();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->string('verifikator_id', 20)->nullable()->index();
            $table->integer('nomor_revisi')->default(1);
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key Constraints
            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('restrict');

            $table->foreign('id_kelompok')
                ->references('id_kelompok')
                ->on('kelompok_tani')
                ->onDelete('restrict');

            $table->foreign('id_komoditas')
                ->references('id_komoditas')
                ->on('komoditas')
                ->onDelete('set null');

            $table->foreign('verifikator_id')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('set null');
        });

        // Enable Row Level Security (RLS) for PostgreSQL / Supabase
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE lahan ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE lahan DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('lahan');
    }
};
