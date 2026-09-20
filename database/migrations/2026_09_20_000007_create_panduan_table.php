<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('panduan', function (Blueprint $table) {
            $table->id('id_panduan');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target');
            $table->text('ringkasan');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->date('tanggal');
            $table->string('status', 20)->default('draft');
            $table->string('created_by', 50)->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal']);
            $table->index('kategori');
            $table->index('komoditas');

            $table->foreign('created_by')
                ->references('id_pengguna')
                ->on('pengguna')
                ->nullOnDelete();
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE panduan ENABLE ROW LEVEL SECURITY');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('panduan');
    }
};
