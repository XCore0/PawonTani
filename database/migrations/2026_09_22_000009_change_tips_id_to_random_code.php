<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $legacyTable = 'tips_integer_id';
        Schema::rename('tips', $legacyTable);

        Schema::create('tips', function (Blueprint $table) {
            $table->string('id_tips', 8)->primary();
            $table->string('judul');
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target', 150)->nullable();
            $table->text('ringkasan');
            $table->text('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 50)->default('Publik');
            $table->timestamps();
        });

        foreach (DB::table($legacyTable)->orderBy('id_tips')->get() as $tip) {
            do {
                $id = 'TPS-' . random_int(1000, 9999);
            } while (DB::table('tips')->where('id_tips', $id)->exists());

            DB::table('tips')->insert([
                'id_tips' => $id,
                'judul' => $tip->judul,
                'kategori' => $tip->kategori,
                'komoditas' => $tip->komoditas,
                'target' => $tip->target,
                'ringkasan' => $tip->ringkasan,
                'isi' => $tip->isi,
                'gambar' => $tip->gambar,
                'tanggal' => $tip->tanggal,
                'status' => $tip->status,
                'created_at' => $tip->created_at,
                'updated_at' => $tip->updated_at,
            ]);
        }

        Schema::drop($legacyTable);
    }

    public function down(): void
    {
        Schema::rename('tips', 'tips_string_id');

        Schema::create('tips', function (Blueprint $table) {
            $table->id('id_tips');
            $table->string('judul');
            $table->string('kategori', 100);
            $table->string('komoditas', 100)->nullable();
            $table->string('target', 150)->nullable();
            $table->text('ringkasan');
            $table->text('isi')->nullable();
            $table->string('gambar')->nullable();
            $table->date('tanggal')->nullable();
            $table->string('status', 50)->default('Publik');
            $table->timestamps();
        });

        foreach (DB::table('tips_string_id')->orderBy('created_at')->get() as $tip) {
            DB::table('tips')->insert([
                'judul' => $tip->judul,
                'kategori' => $tip->kategori,
                'komoditas' => $tip->komoditas,
                'target' => $tip->target,
                'ringkasan' => $tip->ringkasan,
                'isi' => $tip->isi,
                'gambar' => $tip->gambar,
                'tanggal' => $tip->tanggal,
                'status' => $tip->status,
                'created_at' => $tip->created_at,
                'updated_at' => $tip->updated_at,
            ]);
        }

        Schema::drop('tips_string_id');
    }
};