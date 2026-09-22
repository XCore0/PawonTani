<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTable = 'panduan_integer_id';
        Schema::rename('panduan', $legacyTable);

        Schema::create('panduan', function (Blueprint $table) {
            $table->string('id_panduan', 8)->primary();
            $table->string('judul');
            $table->string('slug');
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target')->nullable();
            $table->text('ringkasan');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->date('tanggal');
            $table->string('status', 20)->default('draft');
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
            $table->index(['status', 'tanggal'], 'panduan_new_status_tanggal_index');
            $table->index('kategori', 'panduan_new_kategori_index');
            $table->index('komoditas', 'panduan_new_komoditas_index');
            $table->foreign('created_by')->references('id_pengguna')->on('pengguna')->nullOnDelete();
            $table->unique('slug', 'panduan_new_slug_unique');
        });

        foreach (DB::table($legacyTable)->orderBy('id_panduan')->get() as $panduan) {
            do {
                $id = 'PDU-' . random_int(1000, 9999);
            } while (DB::table('panduan')->where('id_panduan', $id)->exists());

            DB::table('panduan')->insert([
                'id_panduan' => $id,
                'judul' => $panduan->judul,
                'slug' => $panduan->slug,
                'kategori' => $panduan->kategori,
                'komoditas' => $panduan->komoditas,
                'target' => $panduan->target,
                'ringkasan' => $panduan->ringkasan,
                'isi' => $panduan->isi,
                'gambar' => $panduan->gambar,
                'tanggal' => $panduan->tanggal,
                'status' => $panduan->status,
                'created_by' => $panduan->created_by,
                'created_at' => $panduan->created_at,
                'updated_at' => $panduan->updated_at,
            ]);
        }

        Schema::drop($legacyTable);
    }

    public function down(): void
    {
        Schema::rename('panduan', 'panduan_string_id');

        Schema::create('panduan', function (Blueprint $table) {
            $table->id('id_panduan');
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target')->nullable();
            $table->text('ringkasan');
            $table->text('isi');
            $table->string('gambar')->nullable();
            $table->date('tanggal');
            $table->string('status', 20)->default('draft');
            $table->string('created_by', 50)->nullable();
            $table->timestamps();
            $table->foreign('created_by')->references('id_pengguna')->on('pengguna')->nullOnDelete();
        });

        foreach (DB::table('panduan_string_id')->orderBy('created_at')->get() as $panduan) {
            DB::table('panduan')->insert([
                'judul' => $panduan->judul,
                'slug' => $panduan->slug,
                'kategori' => $panduan->kategori,
                'komoditas' => $panduan->komoditas,
                'target' => $panduan->target,
                'ringkasan' => $panduan->ringkasan,
                'isi' => $panduan->isi,
                'gambar' => $panduan->gambar,
                'tanggal' => $panduan->tanggal,
                'status' => $panduan->status,
                'created_by' => $panduan->created_by,
                'created_at' => $panduan->created_at,
                'updated_at' => $panduan->updated_at,
            ]);
        }

        Schema::drop('panduan_string_id');
    }
};