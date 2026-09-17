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
            $table->string('jabatan', 50)->nullable()->after('role'); // Ketua, Sekretaris, Bendahara, dll.
            $table->string('id_kelompok', 50)->nullable()->after('jabatan');
            $table->foreign('id_kelompok')->references('id_kelompok')->on('kelompok_tani')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropForeign(['id_kelompok']);
            $table->dropColumn(['jabatan', 'id_kelompok']);
        });
    }
};
