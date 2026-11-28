<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            $table->string('status_penguasaan', 50)->default('Milik Sendiri')->after('luas')->comment('Milik Sendiri, Sewa, Garap, Bagi Hasil, Lainnya');
        });
    }

    public function down(): void
    {
        Schema::table('lahan', function (Blueprint $table) {
            $table->dropColumn('status_penguasaan');
        });
    }
};
