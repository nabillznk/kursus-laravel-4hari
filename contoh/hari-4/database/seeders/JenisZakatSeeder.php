<?php

namespace Database\Seeders;

use App\Models\JenisZakat;
use Illuminate\Database\Seeder;

class JenisZakatSeeder extends Seeder
{
    public function run(): void
    {
        $jenis = [
            ['nama' => 'Zakat Fitrah',          'kadar' => 7.0000, 'penerangan' => 'Zakat wajib yang dikeluarkan pada bulan Ramadan sebelum solat Hari Raya Aidilfitri.',        'is_aktif' => true],
            ['nama' => 'Zakat Pendapatan',       'kadar' => 2.5000, 'penerangan' => 'Zakat yang dikenakan ke atas pendapatan yang diperoleh daripada pekerjaan atau perkhidmatan.', 'is_aktif' => true],
            ['nama' => 'Zakat Perniagaan',       'kadar' => 2.5000, 'penerangan' => 'Zakat yang dikenakan ke atas harta perniagaan sama ada barangan atau wang.',                  'is_aktif' => true],
            ['nama' => 'Zakat Wang Simpanan',    'kadar' => 2.5000, 'penerangan' => 'Zakat yang dikenakan ke atas wang simpanan yang telah mencapai nisab dan haul.',               'is_aktif' => true],
            ['nama' => 'Zakat Emas',             'kadar' => 2.5000, 'penerangan' => 'Zakat yang dikenakan ke atas emas yang disimpan dan tidak dipakai melebihi uruf.',             'is_aktif' => true],
        ];

        foreach ($jenis as $j) {
            JenisZakat::create($j);
        }
    }
}
