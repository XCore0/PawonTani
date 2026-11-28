<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Musim tanam (MT-1 Rendeng, MT-2 Gadu, dst.) bukan lagi data yang wajib diisi
 * oleh anggota saat memulai siklus tanam: pada praktiknya petani mengisinya
 * asal-asalan karena tidak ada acuan resmi di aplikasi.
 *
 * Kolom tetap dipertahankan (nullable) agar data historis tidak hilang dan
 * pengurus masih bisa melihatnya bila ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE siklus_tanam ALTER COLUMN musim_tanam DROP NOT NULL');

            return;
        }

        Schema::table('siklus_tanam', function (Blueprint $table) {
            $table->string('musim_tanam', 50)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Isi nilai kosong terlebih dahulu agar kolom bisa dikembalikan menjadi NOT NULL.
        DB::table('siklus_tanam')->whereNull('musim_tanam')->update(['musim_tanam' => 'MT-1']);

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE siklus_tanam ALTER COLUMN musim_tanam SET NOT NULL');

            return;
        }

        Schema::table('siklus_tanam', function (Blueprint $table) {
            $table->string('musim_tanam', 50)->nullable(false)->change();
        });
    }
};
