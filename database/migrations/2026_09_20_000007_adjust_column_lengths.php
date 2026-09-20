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
        // --- kelompok_tani ---
        Schema::table('kelompok_tani', function (Blueprint $table) {
            $table->string('id_kelompok', 20)->change();       // PokTan-XXXX = 12 char
            $table->string('nama_kelompok', 150)->change();     // nama kelompok
            $table->string('status', 20)->change();             // Aktif / Tidak Aktif
        });

        // --- pengguna ---
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('id_pengguna', 20)->change();        // PGR-XXXX = 8 char
            $table->string('nama', 100)->change();              // nama orang
            $table->string('nik', 16)->nullable()->change();    // NIK = 16 digit
            $table->string('username', 50)->change();           // username
            $table->string('no_telepon', 20)->nullable()->change(); // no telp
            $table->string('role', 20)->change();               // PPL, Pengurus, Anggota, Pembeli
            $table->string('status', 20)->change();             // Aktif / Tidak Aktif
            $table->string('jabatan', 30)->nullable()->change();// Ketua, Sekretaris, dll
            $table->string('foto_profil', 255)->nullable()->change(); // path file
        });

        // --- anggota ---
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('id_pengguna', 20)->change();        // sesuaikan dengan pengguna
            $table->string('status_keanggotaan', 20)->change(); // Aktif / Tidak Aktif
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kelompok_tani', function (Blueprint $table) {
            $table->string('id_kelompok', 50)->change();
            $table->string('nama_kelompok')->change();
            $table->string('status', 50)->change();
        });

        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('id_pengguna', 50)->change();
            $table->string('nama')->change();
            $table->string('nik', 50)->change();
            $table->string('username', 100)->change();
            $table->string('no_telepon', 50)->change();
            $table->string('role', 50)->change();
            $table->string('status', 50)->change();
            $table->string('jabatan', 50)->change();
            $table->string('foto_profil')->change();
        });

        Schema::table('anggota', function (Blueprint $table) {
            $table->string('id_pengguna', 50)->change();
            $table->string('status_keanggotaan', 50)->change();
        });
    }
};
