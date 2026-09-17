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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->string('id_pengguna', 50)->primary();
            $table->string('nama');
            $table->string('nik', 50)->nullable()->unique();
            $table->string('username', 100)->unique();
            $table->string('password');
            $table->string('email')->nullable()->unique();
            $table->string('no_telepon', 50)->nullable();
            $table->text('alamat')->nullable();
            $table->string('foto_profil')->nullable();
            $table->string('role', 50)->default('Pengurus'); // PPL, Pengurus, Anggota, Pembeli
            $table->string('status', 50)->default('Aktif');   // Aktif, Tidak Aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};
