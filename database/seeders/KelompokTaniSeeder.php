<?php

namespace Database\Seeders;

use App\Models\KelompokTani;
use Illuminate\Database\Seeder;

class KelompokTaniSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'nama_kelompok' => 'Kelompok Tani Maju Bersama',
                'alamat' => 'Desa Sumbersari, Kec. Sumbersari, Jember',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Subur Makmur',
                'alamat' => 'Desa Patrang, Kec. Patrang, Jember',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Sri Rejeki',
                'alamat' => 'Desa Mangli, Kec. Kaliwates, Jember',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Tani Mandiri',
                'alamat' => 'Desa Ajung, Kec. Ajung, Jember',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Karya Bersama',
                'alamat' => 'Desa Sukorambi, Kec. Sukorambi, Jember',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Mekar Wangi',
                'alamat' => 'Desa Kalisat, Kec. Kalisat, Jember',
                'status' => 'Tidak Aktif',
            ],
        ];

        foreach ($data as $item) {
            KelompokTani::firstOrCreate(
                ['nama_kelompok' => $item['nama_kelompok']],
                [
                    'id_kelompok' => KelompokTani::generateIdKelompok(),
                    'alamat' => $item['alamat'],
                    'status' => $item['status'],
                ]
            );
        }
    }
}
