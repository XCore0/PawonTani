<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->timestamp('tanggal_keluar')->nullable();
            $table->text('alasan_keluar')->nullable();
            $table->string('dikeluarkan_oleh', 50)->nullable();
            $table->foreign('dikeluarkan_oleh')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->index('status_keanggotaan');
        });

        Schema::create('log_aktivitas_sistem', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('actor_id', 50)->nullable()->index();
            $table->string('actor_name', 120)->nullable();
            $table->string('actor_role', 30)->nullable();
            $table->string('id_kelompok', 20)->nullable()->index();
            $table->string('event', 80)->index();
            $table->string('subject_type', 120)->nullable();
            $table->string('subject_id', 80)->nullable()->index();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();

            $table->foreign('actor_id')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas_sistem');

        Schema::table('anggota', function (Blueprint $table) {
            $table->dropForeign(['dikeluarkan_oleh']);
            $table->dropIndex(['status_keanggotaan']);
            $table->dropColumn(['tanggal_keluar', 'alasan_keluar', 'dikeluarkan_oleh']);
        });
    }
};
