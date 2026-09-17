<?php

namespace Database\Seeders;

use App\Models\KelompokTani;
use Illuminate\Database\Seeder;

class KelompokTaniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama_kelompok' => 'Kelompok Tani Maju Bersama',
                'alamat' => 'Desa Sukamaju, Kec. Ciawi, Bogor',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Subur Makmur',
                'alamat' => 'Desa Margaluyu, Kec. Manonjaya, Tasikmalaya',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Sri Rejeki',
                'alamat' => 'Desa Karanganyar, Kec. Cikoneng, Ciamis',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Tani Mandiri',
                'alamat' => 'Desa Sukaratu, Kec. Sukaresik, Tasikmalaya',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Karya Bersama',
                'alamat' => 'Desa Cisayong, Kec. Cisayong, Tasikmalaya',
                'status' => 'Aktif',
            ],
            [
                'nama_kelompok' => 'Kelompok Tani Mekar Wangi',
                'alamat' => 'Desa Singaparna, Kec. Singaparna, Tasikmalaya',
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
