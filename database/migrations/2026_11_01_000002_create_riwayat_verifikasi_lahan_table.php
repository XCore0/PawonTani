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
        Schema::create('riwayat_verifikasi_lahan', function (Blueprint $table) {
            $table->bigIncrements('id_riwayat');
            $table->string('id_lahan', 30)->index();
            $table->string('id_pengguna', 20)->index();
            $table->string('aksi', 40);
            $table->string('status_sebelumnya', 30)->nullable();
            $table->string('status_sesudahnya', 30);
            $table->text('catatan')->nullable();
            $table->integer('nomor_revisi')->default(1);
            $table->jsonb('snapshot_data')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            // Foreign Key Constraints
            // Aturan khusus audit trail: ON DELETE NO ACTION untuk menjamin catatan hukum verifikasi tidak terhapus
            $table->foreign('id_lahan')
                ->references('id_lahan')
                ->on('lahan')
                ->onDelete('no action');

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('restrict');
        });

        // Enable Row Level Security (RLS) for PostgreSQL / Supabase
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE riwayat_verifikasi_lahan ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE riwayat_verifikasi_lahan DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('riwayat_verifikasi_lahan');
    }
};
