<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('panduan', 'created_by')) {
            Schema::table('panduan', function (Blueprint $table) {
                $table->string('created_by', 50)->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::table('panduan', function (Blueprint $table) {
            $table->dropColumn('created_by');
        });
    }
};