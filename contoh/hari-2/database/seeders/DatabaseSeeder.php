<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed pangkalan data aplikasi.
     */
    public function run(): void
    {
        $this->call([
            PembayarSeeder::class,
            JenisZakatSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
