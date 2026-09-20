<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('artikel')) {
            return;
        }

        Schema::create('artikel', function (Blueprint $table) {
            $table->bigIncrements('id_artikel');
            $table->string('judul');
            $table->string('kategori');
            $table->string('komoditas')->nullable();
            $table->text('ringkasan');
            $table->text('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status')->default('Draft');
            $table->timestamps();

            $table->index('status');
            $table->index('kategori');
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel');
    }
};
