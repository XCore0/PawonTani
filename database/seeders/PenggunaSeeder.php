<?php

namespace Database\Seeders;

use App\Models\KelompokTani;
use App\Models\Pengguna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $poktans = KelompokTani::all();
        $poktan1 = $poktans->firstWhere('nama_kelompok', 'Kelompok Tani Maju Bersama') ?? $poktans->first();
        $poktan2 = $poktans->firstWhere('nama_kelompok', 'Kelompok Tani Subur Makmur') ?? $poktans->skip(1)->first() ?? $poktan1;
        $poktan3 = $poktans->firstWhere('nama_kelompok', 'Kelompok Tani Sri Rejeki') ?? $poktans->skip(2)->first() ?? $poktan1;

        $pengurusList = [
            // ===== ADMIN / PPL =====
            [
                'nama'        => 'Ahmad Fauzi, S.P.',
                'nik'         => '3509010101900001',
                'username'    => 'admin',
                'password'    => Hash::make('admin123'),
                'email'       => 'admin@pawontani.id',
                'no_telepon'  => '081111111111',
                'alamat'      => 'Jl. Mastrip No. 1, Kec. Sumbersari, Jember',
                'role'        => 'PPL',
                'jabatan'     => 'PPL',
                'id_kelompok' => null,
                'status'      => 'Aktif',
            ],

            // ===== PENGURUS =====
            [
                'nama'        => 'Bpk. Sutrisno, S.P.',
                'nik'         => '3509021205750001',
                'username'    => 'sutrisno',
                'password'    => Hash::make('password123'),
                'email'       => 'sutrisno@pawontani.id',
                'no_telepon'  => '081234567890',
                'alamat'      => 'Dusun Krajan RT 02/RW 04, Desa Sumbersari, Kec. Sumbersari, Jember',
                'role'        => 'Pengurus',
                'jabatan'     => 'Ketua',
                'id_kelompok' => $poktan1?->id_kelompok,
                'status'      => 'Aktif',
            ],
            [
                'nama'        => 'Ibu Sri Wahyuni, S.E.',
                'nik'         => '3509025508820003',
                'username'    => 'sriwahyuni',
                'password'    => Hash::make('password123'),
                'email'       => 'sriwahyuni@pawontani.id',
                'no_telepon'  => '082198765432',
                'alamat'      => 'Dusun Kebonsari RT 01/RW 02, Desa Sumbersari, Kec. Sumbersari, Jember',
                'role'        => 'Pengurus',
                'jabatan'     => 'Sekretaris',
                'id_kelompok' => $poktan1?->id_kelompok,
                'status'      => 'Aktif',
            ],
            [
                'nama'        => 'Bpk. Bambang Hartono',
                'nik'         => '3509022003780002',
                'username'    => 'bambang',
                'password'    => Hash::make('password123'),
                'email'       => 'bambang@pawontani.id',
                'no_telepon'  => '085712345678',
                'alamat'      => 'Jl. PB Sudirman No. 45, Kec. Patrang, Jember',
                'role'        => 'Pengurus',
                'jabatan'     => 'Ketua',
                'id_kelompok' => $poktan2?->id_kelompok,
                'status'      => 'Aktif',
            ],
            [
                'nama'        => 'Bpk. Slamet Riyadi',
                'nik'         => '3509021507700004',
                'username'    => 'slamet',
                'password'    => Hash::make('password123'),
                'email'       => 'slamet@pawontani.id',
                'no_telepon'  => '087812345678',
                'alamat'      => 'Dusun Curah Malang RT 03/RW 01, Desa Mangli, Kec. Kaliwates, Jember',
                'role'        => 'Pengurus',
                'jabatan'     => 'Bendahara',
                'id_kelompok' => $poktan3?->id_kelompok,
                'status'      => 'Aktif',
            ],
        ];

        foreach ($pengurusList as $p) {
            Pengguna::updateOrCreate(
                ['username' => $p['username']],
                array_merge($p, [
                    'id_pengguna' => Pengguna::where('username', $p['username'])->value('id_pengguna')
                        ?? Pengguna::generateIdPengguna(),
                ])
            );
        }
    }
}
