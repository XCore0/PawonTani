<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('artikel') && Schema::hasColumn('artikel', 'target')) {
            Schema::table('artikel', function (Blueprint $table) {
                $table->dropColumn('target');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('artikel') && ! Schema::hasColumn('artikel', 'target')) {
            Schema::table('artikel', function (Blueprint $table) {
                $table->string('target')->nullable();
            });
        }
    }
};
