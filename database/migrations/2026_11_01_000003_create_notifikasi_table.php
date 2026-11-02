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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->string('id_notifikasi', 30)->primary();
            $table->string('id_pengirim', 20)->nullable()->index();
            $table->string('id_penerima', 20)->index();
            $table->string('id_lahan', 30)->nullable()->index();
            $table->unsignedBigInteger('id_riwayat')->nullable()->index();
            $table->string('jenis_notifikasi', 50);
            $table->string('judul', 150);
            $table->text('pesan');
            $table->jsonb('data_payload')->nullable();
            $table->boolean('sudah_dibaca')->default(false)->index();
            $table->timestamp('dibaca_pada')->nullable();
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('id_pengirim')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('set null');

            $table->foreign('id_penerima')
                ->references('id_pengguna')
                ->on('pengguna')
                ->onDelete('cascade');

            $table->foreign('id_lahan')
                ->references('id_lahan')
                ->on('lahan')
                ->onDelete('set null');

            $table->foreign('id_riwayat')
                ->references('id_riwayat')
                ->on('riwayat_verifikasi_lahan')
                ->onDelete('set null');
        });

        // Enable Row Level Security (RLS) for PostgreSQL / Supabase
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE notifikasi ENABLE ROW LEVEL SECURITY');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE notifikasi DISABLE ROW LEVEL SECURITY');
        }

        Schema::dropIfExists('notifikasi');
    }
};
