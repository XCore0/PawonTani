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
        Schema::table('panen', function (Blueprint $table) {
            $table->json('foto_panen_list')->nullable()->after('foto_panen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('panen', function (Blueprint $table) {
            $table->dropColumn('foto_panen_list');
        });
    }
};
