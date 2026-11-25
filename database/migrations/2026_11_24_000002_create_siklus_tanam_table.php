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
        Schema::create('siklus_tanam', function (Blueprint $table) {
            $table->string('id_siklus', 30)->primary();
            $table->string('id_lahan', 30)->index();
            $table->string('id_pengguna', 20)->index();
            $table->string('id_kelompok', 20)->index();
            $table->string('id_komoditas', 20)->index();
            $table->string('komoditas_nama', 100);
            $table->string('varietas', 100)->nullable();
            $table->string('musim_tanam', 50);
            $table->date('tanggal_mulai')->index();
            $table->date('estimasi_panen')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('status', 30)->default('aktif')->index();
            $table->text('alasan_pengakhiran')->nullable();
            $table->string('foto_bukti_gagal', 255)->nullable();
            $table->decimal('target_panen_ton', 10, 2)->nullable();
            $table->string('diverifikasi_oleh', 150)->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign Key Constraints (Aturan: Restrict untuk melindungi integritas data)
            $table->foreign('id_lahan')
                ->references('id_lahan')
                ->on('lahan')
                ->onDelete('restrict');

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
                ->onDelete('restrict');
        });

        // Partial unique index untuk menjamin maksimal 1 siklus berjalan per lahan pada PostgreSQL
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX idx_unique_running_cycle ON siklus_tanam (id_lahan) WHERE status IN ('aktif', 'menunggu_selesai', 'menunggu_gagal')");
            DB::statement('ALTER TABLE siklus_tanam ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_unique_running_cycle');
            DB::statement('ALTER TABLE siklus_tanam DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('siklus_tanam');
    }
};
