<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Anggota;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->string('id_anggota', 8)->change();
        });

        Anggota::query()->each(function (Anggota $anggota) {
            $anggota->id_anggota = Anggota::generateIdAnggota();
            $anggota->saveQuietly();
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->unsignedBigInteger('id_anggota')->change();
        });
    }
};