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
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('provinsi_id', 10)->nullable()->after('alamat');
            $table->string('kabupaten_id', 10)->nullable()->after('provinsi_id');
            $table->string('kecamatan_id', 10)->nullable()->after('kabupaten_id');
            $table->string('desa_id', 20)->nullable()->after('kecamatan_id');
            $table->string('lokasi_nama', 200)->nullable()->after('desa_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id', 'lokasi_nama']);
        });
    }
};
