<?php

namespace Database\Seeders;

use App\Models\Pembayar;
use Illuminate\Database\Seeder;

/**
 * Seeder untuk jadual pembayars.
 * Mencipta 10 rekod contoh pembayar zakat.
 */
class PembayarSeeder extends Seeder
{
    /**
     * Jalankan seeder.
     */
    public function run(): void
    {
        $pembayars = [
            [
                'nama' => 'Ahmad bin Ibrahim',
                'no_ic' => '850315085123',
                'alamat' => 'No. 12, Jalan Putra, Taman Putra, 05150 Alor Setar, Kedah',
                'no_tel' => '0124567890',
                'email' => 'ahmad.ibrahim@gmail.com',
                'pekerjaan' => 'Guru',
                'pendapatan_bulanan' => 4500.00,
            ],
            [
                'nama' => 'Siti Aminah binti Abdullah',
                'no_ic' => '900722025678',
                'alamat' => 'Lot 45, Kampung Baru, 06000 Jitra, Kedah',
                'no_tel' => '0139876543',
                'email' => 'siti.aminah@yahoo.com',
                'pekerjaan' => 'Jururawat',
                'pendapatan_bulanan' => 3800.00,
            ],
            [
                'nama' => 'Mohd Faizal bin Hassan',
                'no_ic' => '880405145234',
                'alamat' => 'No. 78, Lorong Cempaka 3, 08000 Sungai Petani, Kedah',
                'no_tel' => '0171234567',
                'email' => 'faizal.hassan@hotmail.com',
                'pekerjaan' => 'Jurutera',
                'pendapatan_bulanan' => 6500.00,
            ],
            [
                'nama' => 'Norazlina binti Yusof',
                'no_ic' => '920118025890',
                'alamat' => 'Blok C-3-5, Pangsapuri Seri Kedah, 05400 Alor Setar, Kedah',
                'no_tel' => '0168765432',
                'email' => null,
                'pekerjaan' => 'Kerani',
                'pendapatan_bulanan' => 2800.00,
            ],
            [
                'nama' => 'Ismail bin Osman',
                'no_ic' => '780930025345',
                'alamat' => 'No. 5, Jalan Pekan, 06600 Kuala Kedah, Kedah',
                'no_tel' => '0192345678',
                'email' => 'ismail.osman@gmail.com',
                'pekerjaan' => 'Nelayan',
                'pendapatan_bulanan' => 2200.00,
            ],
            [
                'nama' => 'Fatimah binti Zakaria',
                'no_ic' => '950625085567',
                'alamat' => 'No. 33, Taman Harmoni, 09000 Kulim, Kedah',
                'no_tel' => '0143456789',
                'email' => 'fatimah.z@gmail.com',
                'pekerjaan' => 'Pegawai Bank',
                'pendapatan_bulanan' => 5200.00,
            ],
            [
                'nama' => 'Razak bin Mohd Nor',
                'no_ic' => '830212145789',
                'alamat' => 'Kampung Sungai Daun, 06300 Kuala Nerang, Kedah',
                'no_tel' => '0185678901',
                'email' => null,
                'pekerjaan' => 'Petani',
                'pendapatan_bulanan' => 1800.00,
            ],
            [
                'nama' => 'Nur Hidayah binti Ramli',
                'no_ic' => '970803025901',
                'alamat' => 'No. 67, Jalan Sultan Badlishah, 05000 Alor Setar, Kedah',
                'no_tel' => '0116789012',
                'email' => 'hidayah.ramli@outlook.com',
                'pekerjaan' => 'Pereka Grafik',
                'pendapatan_bulanan' => 3500.00,
            ],
            [
                'nama' => 'Kamaruddin bin Mat Zin',
                'no_ic' => '750519085234',
                'alamat' => 'No. 101, Pekan Pokok Sena, 06400 Pokok Sena, Kedah',
                'no_tel' => '0197890123',
                'email' => 'kamaruddin.mz@gmail.com',
                'pekerjaan' => 'Peniaga',
                'pendapatan_bulanan' => 7000.00,
            ],
            [
                'nama' => 'Zainab binti Ahmad',
                'no_ic' => '880916025456',
                'alamat' => 'No. 22, Taman Desa Permai, 09100 Baling, Kedah',
                'no_tel' => '0158901234',
                'email' => null,
                'pekerjaan' => 'Suri Rumah',
                'pendapatan_bulanan' => null,
            ],
        ];

        foreach ($pembayars as $data) {
            Pembayar::create($data);
        }
    }
}
