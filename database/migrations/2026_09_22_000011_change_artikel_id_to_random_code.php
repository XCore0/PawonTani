<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTable = 'artikel_integer_id';
        Schema::rename('artikel', $legacyTable);

        Schema::create('artikel', function (Blueprint $table) {
            $table->string('id_artikel', 8)->primary();
            $table->string('judul');
            $table->string('kategori');
            $table->string('komoditas')->nullable();
            $table->text('ringkasan');
            $table->text('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status')->default('Draft');
            $table->timestamps();
            $table->index('status', 'artikel_new_status_index');
            $table->index('kategori', 'artikel_new_kategori_index');
            $table->index('tanggal', 'artikel_new_tanggal_index');
        });

        foreach (DB::table($legacyTable)->orderBy('id_artikel')->get() as $artikel) {
            do {
                $id = 'ATL-' . random_int(1000, 9999);
            } while (DB::table('artikel')->where('id_artikel', $id)->exists());

            DB::table('artikel')->insert([
                'id_artikel' => $id,
                'judul' => $artikel->judul,
                'kategori' => $artikel->kategori,
                'komoditas' => $artikel->komoditas,
                'ringkasan' => $artikel->ringkasan,
                'isi' => $artikel->isi,
                'gambar' => $artikel->gambar,
                'tanggal' => $artikel->tanggal,
                'status' => $artikel->status,
                'created_at' => $artikel->created_at,
                'updated_at' => $artikel->updated_at,
            ]);
        }

        Schema::drop($legacyTable);
    }

    public function down(): void
    {
        Schema::rename('artikel', 'artikel_string_id');

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

        foreach (DB::table('artikel_string_id')->orderBy('created_at')->get() as $artikel) {
            DB::table('artikel')->insert([
                'judul' => $artikel->judul,
                'kategori' => $artikel->kategori,
                'komoditas' => $artikel->komoditas,
                'ringkasan' => $artikel->ringkasan,
                'isi' => $artikel->isi,
                'gambar' => $artikel->gambar,
                'tanggal' => $artikel->tanggal,
                'status' => $artikel->status,
                'created_at' => $artikel->created_at,
                'updated_at' => $artikel->updated_at,
            ]);
        }

        Schema::drop('artikel_string_id');
    }
};