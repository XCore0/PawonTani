<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_lahan', function (Blueprint $table) {
            $table->id();
            $table->string('id_lahan', 30)->unique();
            $table->string('jenis', 10)->default('SPPT');
            $table->string('file_path', 500);
            $table->string('original_name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->string('uploaded_by', 50)->nullable();
            $table->timestamps();

            $table->foreign('id_lahan')->references('id_lahan')->on('lahan')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_lahan');
    }
};
