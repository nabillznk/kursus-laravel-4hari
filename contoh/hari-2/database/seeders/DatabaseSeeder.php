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
        // Cipta pengguna admin untuk ujian
        \App\Models\User::factory()->create([
            'name' => 'Admin Zakat',
            'email' => 'admin@zakat.test',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            PembayarSeeder::class,
            JenisZakatSeeder::class,
            PembayaranSeeder::class,
        ]);
    }
}
