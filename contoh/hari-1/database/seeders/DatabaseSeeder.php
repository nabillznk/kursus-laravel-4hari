<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Jalankan seeder pangkalan data.
     */
    public function run(): void
    {
        $this->call([
            PembayarSeeder::class,
        ]);
    }
}
