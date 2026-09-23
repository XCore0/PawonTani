<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            KelompokTaniSeeder::class,
            PenggunaSeeder::class,
            KomoditasSeeder::class,
            TipsSeeder::class,
            PanduanSeeder::class,
            ArtikelSeeder::class,
        ]);
    }
}
