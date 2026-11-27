<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan umur panen (dalam hari) pada master komoditas.
 *
 * Nilai ini dipakai untuk menurunkan estimasi panen secara otomatis
 * dari tanggal mulai tanam, sehingga anggota tidak perlu menebak tanggal.
 * Bersifat nullable: komoditas yang belum punya data umur panen tetap valid,
 * hanya estimasi otomatisnya tidak ditampilkan.
 */
return new class extends Migration
{
    /**
     * Perkiraan umur panen/siap produksi per komoditas (dalam hari).
     * Angka mengikuti rentang umum varietas lokal Indonesia.
     */
    private const UMUR_PANEN = [
        // Tanaman Pangan
        'Padi Sawah' => 115,
        'Padi Gogo' => 120,
        'Jagung' => 105,
        'Kedelai' => 85,
        'Kacang Tanah' => 100,
        'Kacang Hijau' => 65,
        'Singkong / Ubi Kayu' => 270,
        'Ubi Jalar' => 120,

        // Hortikultura & Sayuran
        'Cabai Rawit' => 90,
        'Cabai Merah' => 100,
        'Bawang Merah' => 60,
        'Bawang Putih' => 120,
        'Tomat' => 90,
        'Terong' => 90,
        'Bayam' => 30,
        'Kangkung' => 30,
        'Sawi & Pakcoy' => 35,
        'Kubis / Kol' => 90,
        'Mentimun' => 45,
        'Kacang Panjang' => 60,
        'Wortel' => 90,
        'Kentang' => 100,

        // Buah-buahan
        'Melon' => 75,
        'Semangka' => 75,
        'Pisang' => 300,
        'Pepaya' => 270,
        'Jeruk' => 365,
        'Mangga' => 365,
        'Alpukat' => 365,
        'Durian' => 1825,
        'Nanas' => 450,
        'Jambu Kristal' => 240,

        // Perkebunan & Rempah
        'Kopi' => 730,
        'Kakao / Cokelat' => 540,
        'Kelapa' => 1095,
        'Cengkeh' => 1825,
        'Lada' => 730,
        'Jahe' => 270,
        'Kunyit' => 270,
        'Lengkuas' => 300,
        'Serai' => 120,
    ];

    public function up(): void
    {
        Schema::table('komoditas', function (Blueprint $table) {
            $table->unsignedSmallInteger('umur_panen_hari')
                ->nullable()
                ->after('kategori')
                ->comment('Perkiraan umur panen/siap produksi dalam hari');
        });

        // Backfill data yang sudah ada agar estimasi otomatis langsung berfungsi.
        foreach (self::UMUR_PANEN as $nama => $umur) {
            DB::table('komoditas')
                ->where('nama_komoditas', $nama)
                ->update(['umur_panen_hari' => $umur]);
        }
    }

    public function down(): void
    {
        Schema::table('komoditas', function (Blueprint $table) {
            $table->dropColumn('umur_panen_hari');
        });
    }
};
