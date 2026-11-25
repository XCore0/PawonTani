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
        // 1. Tambah kolom pengakhiran dan verifikasi pada tabel siklus_tanam
        Schema::table('siklus_tanam', function (Blueprint $table) {
            $table->timestamp('tanggal_pengajuan')->nullable()->after('status');
            $table->timestamp('tanggal_verifikasi')->nullable()->after('diverifikasi_oleh');
            $table->string('jenis_pengakhiran', 30)->nullable()->after('status');
        });

        // 2. Buat tabel audit trail riwayat pengakhiran siklus tanam
        Schema::create('riwayat_pengakhiran_siklus', function (Blueprint $table) {
            $table->bigIncrements('id_riwayat');
            $table->string('id_siklus', 30)->index();
            $table->string('id_pengguna', 20)->index();
            $table->string('jenis_pengakhiran', 30)->index(); // selesai, gagal, dibatalkan
            $table->string('aksi', 40); // pengajuan_selesai, pengajuan_gagal, pembatalan, disetujui, ditolak
            $table->string('status_sebelumnya', 30)->nullable();
            $table->string('status_sesudahnya', 30);
            $table->text('alasan')->nullable();
            $table->string('foto_bukti', 255)->nullable();
            $table->string('verifikator_id', 20)->nullable()->index();
            $table->string('verifikator_nama', 150)->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamp('tanggal_keputusan')->nullable();
            $table->jsonb('snapshot_data')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            // Foreign Key Constraints (Aturan: no action / restrict untuk menjaga integritas data audit)
            $table->foreign('id_siklus')
                ->references('id_siklus')
                ->on('siklus_tanam')
                ->onDelete('no action');

            $table->foreign('id_pengguna')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('restrict');

            $table->foreign('verifikator_id')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('set null');
        });

        // Enable Row Level Security (RLS) for PostgreSQL / Supabase
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE riwayat_pengakhiran_siklus ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE riwayat_pengakhiran_siklus DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('riwayat_pengakhiran_siklus');

        Schema::table('siklus_tanam', function (Blueprint $table) {
            $table->dropColumn(['tanggal_pengajuan', 'tanggal_verifikasi', 'jenis_pengakhiran']);
        });
    }
};
